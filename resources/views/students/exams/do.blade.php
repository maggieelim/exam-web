@extends('layouts.user_type.auth')

@section('hideSidebar') @endsection
@section('hideNavbar') @endsection
@section('hideFooter') @endsection

@section('content')
<div class="py-4 px-0">
    <div class="row justify-content-center g-3">
        <div class="col-12 col-md-7">
            <div class="card p-3">
                {{-- Question Display --}}
                @include('students.exams.partials.question_display')

                {{-- Form with debounced save --}}
                <form id="answerForm"
                    data-save-url="{{ route('student.exams.answer', [$examData->exam_code, $currentQuestion->kode_soal]) }}">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $currentQuestion->id }}">

                    @foreach ($currentQuestion->options->shuffle() as $option)
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="radio" name="answer" id="option{{ $option->id }}"
                            value="{{ $option->id }}" {{ $savedAnswer && $savedAnswer->answer == $option->id ? 'checked'
                        : '' }}>
                        <label class="form-check-label" for="option{{ $option->id }}">
                            {{ $option->text }}
                        </label>
                        @if ($option->image)
                        <div class="mt-0">
                            <img src="{{ asset('storage/' . $option->image) }}" alt="Gambar Soal"
                                class="img-fluid rounded shadow-sm zoomable-image"
                                style="max-width: 150px; max-height: 130px; cursor: zoom-in;" loading="lazy"
                                data-bs-toggle="modal" data-bs-target="#imageZoomModal"
                                data-image="{{ asset('storage/' . $option->image) }}">
                        </div>
                        @endif
                    </div>
                    @endforeach

                    {{-- Navigation --}}
                    @include('students.exams.partials.navigation_buttons')
                </form>
            </div>
        </div>

        {{-- Sidebar with timer and navigation --}}
        @include('students.exams.partials.sidebar')
    </div>

    {{-- Modals --}}
    @include('students.exams.partials.modals')
</div>

{{-- Optimized JavaScript --}}
<script>
    (function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        examId: '{{ $examData->id }}',
        examCode: '{{ $examData->exam_code }}',
        currentKodeSoal: '{{ $currentQuestion->kode_soal }}',
        totalQuestions: {{ $totalQuestions }},
        csrfToken: '{{ csrf_token() }}',
        checkStatusUrl: '{{ route("student.exams.check-status", $examData->exam_code) }}',
        finishUrl: '{{ route("student.exams.finish", $examData->exam_code) }}',
        prevUrl: '{{ $prevQuestion ? route("student.exams.do", [$examData->exam_code, $prevQuestion->kode_soal]) : "" }}',
        nextUrl: '{{ $nextQuestion ? route("student.exams.do", [$examData->exam_code, $nextQuestion->kode_soal]) : "" }}'
    };

    // State management
    const state = {
        answeredQuestions: new Set({!! json_encode(collect($userAnswers)->filter(fn($a) => $a !== null)->keys()->toArray()) !!}),
        saveTimeout: null,
        isSaving: false,
        pendingSave: false
    };

    // Debounced save function
    function debouncedSave() {
        if (state.isSaving) {
            state.pendingSave = true;
            return;
        }
        
        clearTimeout(state.saveTimeout);
        state.saveTimeout = setTimeout(saveAnswer, 800);
    }

    // Save answer with abort controller
    let abortController = null;
    
    async function saveAnswer() {
        if (state.isSaving) return;
        
        // Abort previous request if exists
        if (abortController) {
            abortController.abort();
        }
        
        abortController = new AbortController();
        state.isSaving = true;
        
        const selectedAnswer = document.querySelector('input[name="answer"]:checked');
        const markDoubt = document.getElementById('markDoubtCheckbox').checked ? 1 : 0;
        
        try {
            const response = await fetch(CONFIG.saveUrl || document.getElementById('answerForm').dataset.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CONFIG.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    answer: selectedAnswer ? selectedAnswer.value : null,
                    mark_doubt: markDoubt
                }),
                signal: abortController.signal
            });

            const data = await response.json();
            
            if (data.success) {
                const questionId = {{ $currentQuestion->id }};
                if (selectedAnswer) {
                    state.answeredQuestions.add(questionId);
                } else {
                    state.answeredQuestions.delete(questionId);
                }
                updateNavigationButtons();
                updateFinishButton();
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Save error:', error);
            }
        } finally {
            state.isSaving = false;
            if (state.pendingSave) {
                state.pendingSave = false;
                debouncedSave();
            }
        }
    }

    // Timer with requestAnimationFrame for better performance
    function initTimer() {
        const endTime = new Date("{{ $endTime->toISOString() }}").getTime();
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            const now = Date.now();
            const distance = endTime - now;
            
            if (distance <= 0) {
                timerElement.innerHTML = "WAKTU HABIS";
                clearInterval(timerInterval);
                showAlert('Waktu ujian telah habis!', true);
                return;
            }
            
            const hours = Math.floor(distance / 3600000);
            const minutes = Math.floor((distance % 3600000) / 60000);
            const seconds = Math.floor((distance % 60000) / 1000);
            
            timerElement.innerHTML = 
                hours.toString().padStart(2, '0') + ':' +
                minutes.toString().padStart(2, '0') + ':' +
                seconds.toString().padStart(2, '0');
        }
        
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
        
        // Clean up interval on page unload
        window.addEventListener('beforeunload', () => clearInterval(timerInterval));
    }

    // Status check with longer interval (30 seconds instead of 10)
    function initStatusCheck() {
        let checkInterval = setInterval(async () => {
            try {
                const response = await fetch(CONFIG.checkStatusUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                
                if (data.status === 'completed') {
                    clearInterval(checkInterval);
                    showAlert('Ujian telah diakhiri oleh admin.', true);
                }
            } catch (error) {
                console.error('Status check error:', error);
            }
        }, 30000); // 30 detik
        
        return checkInterval;
    }

    // Utility functions
    function showAlert(message, autoSubmit = false) {
        document.getElementById('alertModalBody').textContent = message;
        const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
        alertModal.show();
        
        if (autoSubmit) {
            setTimeout(() => document.getElementById('autoFinishForm').submit(), 2000);
        }
    }

    function updateNavigationButtons() {
        const currentButton = document.querySelector(`.question-nav[data-kode-soal="${CONFIG.currentKodeSoal}"]`);
        if (!currentButton) return;
        
        const selectedAnswer = document.querySelector('input[name="answer"]:checked');
        const markDoubt = document.getElementById('markDoubtCheckbox').checked;
        
        currentButton.classList.remove('btn-secondary', 'btn-success', 'btn-warning');
        
        if (markDoubt) {
            currentButton.classList.add('btn-warning');
        } else if (selectedAnswer) {
            currentButton.classList.add('btn-success');
        } else {
            currentButton.classList.add('btn-secondary');
        }
    }

    function updateFinishButton() {
        const finishContainer = document.getElementById('finishExamContainer');
        if (finishContainer) {
            finishContainer.style.display = state.answeredQuestions.size >= CONFIG.totalQuestions ? 'block' : 'none';
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Image zoom
        document.querySelectorAll('.zoomable-image').forEach(img => {
            img.addEventListener('click', function() {
                document.getElementById('zoomedImage').src = this.dataset.image;
            });
        });

        // Prevent back navigation
        history.pushState(null, null, location.href);
        window.onpopstate = () => history.go(1);

        // Initialize
        initTimer();
        const statusCheckInterval = initStatusCheck();
        
        // Radio change with debounce
        document.querySelectorAll('input[name="answer"]').forEach(input => {
            input.addEventListener('change', debouncedSave);
        });

        // Doubt checkbox
        document.getElementById('markDoubtCheckbox').addEventListener('change', function() {
            this.disabled = true;
            saveAnswer().finally(() => { this.disabled = false; });
        });

        // Navigation
        document.querySelectorAll('.question-nav').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.dataset.isCurrent === 'true') return;
                
                this.disabled = true;
                saveAnswer().then(() => {
                    window.location.href = `{{ route("student.exams.do", $examData->exam_code) }}/${this.dataset.kodeSoal}`;
                }).finally(() => {
                    this.disabled = false;
                });
            });
        });

        // Prev/Next buttons
        if (document.getElementById('prevQuestion')) {
            document.getElementById('prevQuestion').addEventListener('click', function() {
                this.disabled = true;
                saveAnswer().then(() => {
                    if (CONFIG.prevUrl) window.location.href = CONFIG.prevUrl;
                }).finally(() => { this.disabled = false; });
            });
        }

        if (document.getElementById('nextQuestion')) {
            document.getElementById('nextQuestion').addEventListener('click', function() {
                this.disabled = true;
                saveAnswer().then(() => {
                    if (CONFIG.nextUrl) window.location.href = CONFIG.nextUrl;
                }).finally(() => { this.disabled = false; });
            });
        }

        // Finish form
        document.getElementById('finishForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('Apakah Anda yakin ingin menyelesaikan ujian?')) {
                saveAnswer().then(() => this.submit());
            }
        });

        // Clean up
        window.addEventListener('beforeunload', () => {
            clearInterval(statusCheckInterval);
            if (state.saveTimeout) clearTimeout(state.saveTimeout);
            if (abortController) abortController.abort();
        });
    });
})();
</script>
@endsection