@extends('layouts.user_type.auth')

@section('hideSidebar')
@endsection
@section('hideNavbar')
@endsection
@section('hideFooter')
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-9">
            <div class="card p-3">
                <div class="mt-2">
                    <p><strong>{{ $currentQuestion->badan_soal }}</strong></p>
                    <p>{{ $currentQuestion->kalimat_tanya }}</p>
                    @if ($currentQuestion->image)
                    <div class="my-3">
                        <img src="{{ asset('storage/' . $currentQuestion->image) }}" alt="Gambar Soal"
                            class="img-fluid " style="max-width: 600px;">
                    </div>
                    @endif
                </div>

                {{-- Form AJAX --}}
                <form id="answerForm">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $currentQuestion->id }}">
                    @foreach ($currentQuestion->options->shuffle() as $option)
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="radio" name="answer" id="option{{ $option->id }}"
                            value="{{ $option->id }}" {{ isset($savedAnswer) && $savedAnswer->answer == $option->id ?
                        'checked' : '' }}>
                        <label class="form-check-label" for="option{{ $option->id }}">
                            {{ $option->text }}
                        </label>
                    </div>
                    @endforeach

                    <div class="mt-4 d-flex justify-content-center align-items-center">
                        @if ($prevQuestion)
                        <button type="button" id="prevQuestion" class="btn bg-gradient-primary"> <i
                                class="fas fa-chevron-left"> </i>
                        </button>
                        @else
                        <button type="button" class="btn btn-secondary" disabled> <i class="fas fa-chevron-left"></i>
                        </button>
                        @endif

                        {{-- Checkbox Ragu-Ragu --}}
                        <div class="form-check mx-3">
                            <input class="form-check-input" type="checkbox" name="mark_doubt" id="markDoubtCheckbox" {{
                                isset($savedAnswer) && $savedAnswer->marked_doubt ? 'checked' : '' }}>
                            <label class="form-check-label mb-0" for="markDoubtCheckbox">
                                Tandai sebagai ragu-ragu
                            </label>
                        </div>

                        @if ($nextQuestion)
                        <button type="button" id="nextQuestion" class="btn bg-gradient-primary"><i
                                class="fas fa-chevron-right"></i> </button>
                        @else
                        <button disabled type="button" id="nextQuestion" class="btn bg-gradient-primary"><i
                                class="fas fa-chevron-right"></i></button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <strong>SISA WAKTU</strong>
                    <span id="timer" class="badge bg-danger fs-6">00:00:00</span>
                </div>
                <hr>
                <div class="grid-container" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 5px;">
                    @foreach ($questions as $index => $q)
                    <button type="button" class="btn btn-xs d-flex align-items-center justify-content-center question-nav
            @if ($q->id == $currentQuestion->id) border border-2 border-primary
            @elseif($q->isDoubtBy(auth()->id())) btn-warning
            @elseif(isset($userAnswers[$q->id]) && $userAnswers[$q->id] !== null) btn-success
            @else btn-secondary @endif" style="min-width: 28px; height: 35px; font-size: 0.9rem; padding: 0;"
                        data-kode-soal="{{ $q->kode_soal }}"
                        data-is-current="{{ $q->id == $currentQuestion->id ? 'true' : 'false' }}">
                        {{ $loop->iteration }}
                    </button>
                    @endforeach
                </div>
                <hr>
                <div id="finishExamContainer" style="display: {{ $allAnswered ? 'block' : 'none' }};">
                    <form action="{{ route('student.exams.finish', $exam->exam_code) }}" method="POST" id="finishForm">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            Selesaikan Ujian
                        </button>
                    </form>
                </div>
                <form id="autoFinishForm" action="{{ route('student.exams.finish', $exam->exam_code) }}" method="POST"
                    style="display: none;">
                    @csrf
                    <input type="hidden" name="auto_finish" value="1">
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Konfirmasi</h5>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    <!-- Konten modal akan diisi secara dinamis -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-primary" id="confirmModalAction">Ya</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Alert --}}
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Pemberitahuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="alertModalBody">
                    <!-- Konten modal akan diisi secara dinamis -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.go(1);
            alert("Navigasi kembali dinonaktifkan selama ujian.");
        };
        document.addEventListener('DOMContentLoaded', function() {
            @if (optional($exam->attempt)->status === 'completed')
                document.getElementById('autoFinishForm').submit();
            @endif

            let answeredQuestions = new Set({!! json_encode(
                collect($userAnswers)->filter(function ($answer) {
                        return $answer !== null;
                    })->keys()->toArray(),
            ) !!});
            const totalQuestions = {{ $totalQuestions }};


            function checkAllQuestionsAnswered() {
                return answeredQuestions.size >= totalQuestions;
            }

            function updateFinishButton() {
                const allAnswered = checkAllQuestionsAnswered();
                const finishContainer = document.getElementById('finishExamContainer');
                const answeredCountSpan = document.getElementById('answeredCount');
                const unansweredCountSpan = document.getElementById('unansweredCount');

                if (allAnswered) {
                    finishContainer.style.display = 'block';
                } else {
                    finishContainer.style.display = 'none';
                }

                // Update counters
                if (answeredCountSpan) {
                    answeredCountSpan.textContent = answeredQuestions.size;
                }
                if (unansweredCountSpan) {
                    unansweredCountSpan.textContent = totalQuestions - answeredQuestions.size;
                }

                console.log('Answered questions:', answeredQuestions.size, '/', totalQuestions);
            }

            updateFinishButton();

            function checkExamStatus() {
                return fetch("{{ route('student.exams.check-status', $exam->exam_code) }}", {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Exam status check:', data);
                        return data;
                    })
                    .catch(error => {
                        console.error('Error checking exam status:', error);
                        return {
                            status: 'error'
                        };
                    });
            }

            // Polling untuk mengecek status exam setiap 10 detik
            let statusCheckInterval = setInterval(() => {
                checkExamStatus().then(data => {
                    if (data.status === 'completed') {
                        clearInterval(statusCheckInterval);
                        clearInterval(timerInterval); // Hentikan timer juga

                        showAlert(
                            'Ujian telah diakhiri oleh admin. Anda akan diarahkan ke halaman previous exam.'
                        );

                        setTimeout(() => {
                            document.getElementById('autoFinishForm').submit();
                        }, 3000);
                    }
                });
            }, 10000);

            // Debug info
            console.log('Exam ID:', '{{ $exam->id }}');
            console.log('Server End Time:', '{{ $endTime }}');

            // Gunakan endTime dari server
            const serverEndTime = new Date("{{ $endTime }}").getTime();
            const now = new Date().getTime();

            console.log('Server End Time (ms):', serverEndTime);
            console.log('Now (ms):', now);

            // Cek jika waktu sudah habis berdasarkan server time
            if (now >= serverEndTime) {
                console.log('Time already expired based on server time');
                document.getElementById("timer").innerHTML = "WAKTU HABIS";
                showAlert('Waktu ujian telah habis!');

                // Beri waktu 2 detik sebelum auto submit
                setTimeout(() => {
                    document.getElementById('autoFinishForm').submit();
                }, 2000);
                return;
            }

            // Untuk attempt berjalan, gunakan localStorage jika ada, jika tidak gunakan server time
            let savedEndTime = localStorage.getItem('examEndTime_{{ $exam->id }}');

            if (!savedEndTime) {
                // Attempt pertama, simpan waktu dari server ke localStorage
                console.log('First attempt, saving server time to localStorage');
                localStorage.setItem('examEndTime_{{ $exam->id }}', serverEndTime.toString());
                savedEndTime = serverEndTime;
            } else {
                savedEndTime = parseInt(savedEndTime);
                console.log('Using saved time from localStorage:', savedEndTime);
            }

            // Validasi savedEndTime tidak lebih awal dari serverEndTime
            if (savedEndTime < serverEndTime) {
                console.log('Saved time is earlier than server time, using server time');
                savedEndTime = serverEndTime;
                localStorage.setItem('examEndTime_{{ $exam->id }}', serverEndTime.toString());
            }

            let timerInterval;

            function updateTimer() {
                const now = new Date().getTime();
                const distance = savedEndTime - now;

                // Jika waktu habis
                if (distance <= 0) {
                    clearInterval(timerInterval);
                    clearInterval(statusCheckInterval);
                    document.getElementById("timer").innerHTML = "WAKTU HABIS";
                    localStorage.removeItem('examEndTime_{{ $exam->id }}');

                    console.log('Time expired during timer update');
                    showAlert('Waktu ujian telah habis!');

                    setTimeout(() => {
                        document.getElementById('autoFinishForm').submit();
                    }, 2000);
                    return;
                }

                // Hitung jam, menit, detik
                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Format tampilan
                document.getElementById("timer").innerHTML =
                    ("0" + hours).slice(-2) + ":" +
                    ("0" + minutes).slice(-2) + ":" +
                    ("0" + seconds).slice(-2);
            }

            // Mulai timer
            timerInterval = setInterval(updateTimer, 1000);
            updateTimer(); // Panggil sekali di awal

            function showAlert(message) {
                document.getElementById('alertModalBody').textContent = message;
                const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
                alertModal.show();
            }

            function showConfirm(message, callback) {
                document.getElementById('confirmModalBody').textContent = message;
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

                // Hapus event listener sebelumnya dan tambahkan yang baru
                const actionBtn = document.getElementById('confirmModalAction');
                const newActionBtn = actionBtn.cloneNode(true);
                actionBtn.parentNode.replaceChild(newActionBtn, actionBtn);

                newActionBtn.onclick = function() {
                    confirmModal.hide();
                    callback(true);
                };

                confirmModal.show();
            }

            // Fungsi untuk menyimpan jawaban
            function saveAnswer() {
                const selectedAnswer = document.querySelector('input[name="answer"]:checked');
                const answerValue = selectedAnswer ? selectedAnswer.value : null;
                const markDoubtCheckbox = document.getElementById('markDoubtCheckbox');
                const markDoubtValue = markDoubtCheckbox.checked ? 1 : 0;
                const currentQuestionId = {{ $currentQuestion->id }};

                console.log('Saving answer:', {
                    answer: answerValue,
                    mark_doubt: markDoubtValue,
                    question_id: currentQuestionId
                });

                return fetch(
                        "{{ route('student.exams.answer', [$exam->exam_code, $currentQuestion->kode_soal]) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                answer: answerValue,
                                mark_doubt: markDoubtValue
                            })
                        })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Server response:', data);
                        if (data.success) {
                            console.log('Answer saved successfully. marked_doubt:', data.marked_doubt);

                            if (answerValue) {
                                answeredQuestions.add(currentQuestionId);
                            } else {
                                answeredQuestions.delete(currentQuestionId);
                            }
                            // Update tampilan tombol navigasi jika perlu
                            updateNavigationButtons();
                            updateFinishButton();

                            return true;
                        } else {
                            showAlert('Gagal menyimpan jawaban: ' + (data.message || 'Terjadi kesalahan'));
                            return false;
                        }
                    })
                    .catch(error => {
                        console.error('Error saving answer:', error);
                        showAlert('Terjadi kesalahan saat menyimpan jawaban. Periksa koneksi internet Anda.');
                        return false;
                    });
            }

            // Fungsi untuk update tampilan tombol navigasi
            function updateNavigationButtons() {
                const currentButton = document.querySelector(
                    `.question-nav[data-kode-soal="{{ $currentQuestion->kode_soal }}"]`);
                if (currentButton) {
                    const selectedAnswer = document.querySelector('input[name="answer"]:checked');
                    const markDoubtCheckbox = document.getElementById('markDoubtCheckbox');

                    // Update class tombol berdasarkan status jawaban
                    currentButton.classList.remove('btn-secondary', 'btn-success', 'btn-warning');

                    if (markDoubtCheckbox.checked) {
                        currentButton.classList.add('btn-warning');
                    } else if (selectedAnswer) {
                        currentButton.classList.add('btn-success');
                    } else {
                        currentButton.classList.add('btn-secondary');
                    }
                }
            }

            // Event listener untuk checkbox ragu-ragu
            document.getElementById('markDoubtCheckbox').addEventListener('change', function() {
                const checkbox = this;
                const originalState = checkbox.checked;
                checkbox.disabled = true;

                console.log('Doubt checkbox changed:', originalState);

                saveAnswer().then(success => {
                    checkbox.disabled = false;

                    if (!success) {
                        // Jika gagal, kembalikan ke state semula
                        checkbox.checked = !originalState;
                    } else {
                        // Jika berhasil, update tampilan
                        updateNavigationButtons();
                    }
                });
            });

            // Event listener untuk semua tombol navigasi soal
            document.querySelectorAll('.question-nav').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const kodeSoal = this.getAttribute('data-kode-soal');
                    const isCurrent = this.getAttribute('data-is-current') === 'true';

                    console.log('Navigation button clicked:', kodeSoal, 'Current:', isCurrent);

                    if (isCurrent) {
                        return;
                    }

                    // Simpan jawaban terlebih dahulu sebelum navigasi
                    saveAnswer().then(success => {
                        if (success) {
                            console.log('Navigating to question:', kodeSoal);
                            window.location.href =
                                "{{ route('student.exams.do', [$exam->exam_code, '']) }}/" +
                                kodeSoal;
                        } else {
                            console.log('Failed to save answer, navigation cancelled');
                        }
                    }).catch(error => {
                        console.error('Navigation error:', error);
                        showAlert('Gagal berpindah soal. Silakan coba lagi.');
                    });
                });
            });

            // Event listener untuk tombol sebelumnya
            if (document.getElementById('prevQuestion')) {
                document.getElementById('prevQuestion').addEventListener('click', () => {
                    saveAnswer().then(success => {
                        if (success) {
                            window.location.href =
                                "{{ route('student.exams.do', [$exam->exam_code]) }}/{{ $prevQuestion ? $prevQuestion->kode_soal : '' }}";
                        }
                    });
                });
            }

            // Event listener untuk tombol selanjutnya
            if (document.getElementById('nextQuestion')) {
                document.getElementById('nextQuestion').addEventListener('click', () => {
                    saveAnswer().then(success => {
                        if (success) {
                            window.location.href =
                                "{{ route('student.exams.do', [$exam->exam_code]) }}/{{ $nextQuestion ? $nextQuestion->kode_soal : '' }}";
                        }
                    });
                });
            }

            // Event listener untuk form selesaikan ujian
            document.getElementById('finishForm').addEventListener('submit', function(e) {
                e.preventDefault();

                showConfirm('Apakah Anda yakin ingin menyelesaikan ujian?', function(confirmed) {
                    if (confirmed) {
                        // Simpan jawaban terakhir sebelum submit
                        saveAnswer().then(success => {
                            if (success) {
                                e.target.submit();
                            } else {
                                showAlert(
                                    'Gagal menyimpan jawaban terakhir. Silakan coba lagi.'
                                );
                            }
                        });
                    }
                });
            });

            // Simpan jawaban otomatis saat memilih opsi
            const radioInputs = document.querySelectorAll('input[name="answer"]');
            radioInputs.forEach(input => {
                input.addEventListener('change', () => {
                    saveAnswer().then(success => {
                        if (success) {
                            updateNavigationButtons();
                        }
                    });
                });
            });

            // Prevent form submission on enter key
            document.getElementById('answerForm').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });

            // Handle page refresh/unload - simpan jawaban terakhir
            window.addEventListener('beforeunload', function(e) {
                if (!e.defaultPrevented) {
                    saveAnswer();
                }
            });

            // Auto-save setiap 30 detik untuk backup
            setInterval(() => {
                const selectedAnswer = document.querySelector('input[name="answer"]:checked');
                if (selectedAnswer) {
                    console.log('Auto-saving answer...');
                    saveAnswer().then(success => {
                        if (success) {
                            console.log('Auto-save successful');
                        }
                    });
                }
            }, 30000); // 30 detik

            // Initial update untuk tombol navigasi
            updateNavigationButtons();
            window.addEventListener('beforeunload', function() {
                clearInterval(statusCheckInterval);
            })
        });
</script>
@endsection