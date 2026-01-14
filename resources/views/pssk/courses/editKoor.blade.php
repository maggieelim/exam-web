@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">
    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">
                Update Koordinator Blok {{$course->name}}
                {{ $semester->semester_name }}
                {{$semester->academicYear->year_name}}
            </h5>
        </div>

        <div class="card-body px-4 pt-2 pb-2">
            <form method="POST" action="{{ route('courses.updateKoor', $course->slug) }}">
                @csrf

                {{-- Data hidden --}}
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="semester_id" value="{{ $semester->id }}">

                <div class="row">

                    {{-- KOORDINATOR --}}
                    <div class="mb-3 col-md-6">
                        <label>Koordinator</label>

                        <input type="text" class="form-control input-bg lecturer-name-input"
                            list="lecturer-list-koordinator" value="{{ $koordinator->lecturer->user->name ?? '' }}"
                            data-target="#koordinator_id">

                        <input type="hidden" id="koordinator_id" name="koordinator_id"
                            value="{{ $koordinator->lecturer_id ?? '' }}">

                        <datalist id="lecturer-list-koordinator">
                            @foreach ($lecturers as $lec)
                            <option value="{{ $lec->user->name }}" data-id="{{ $lec->id }}">
                                @endforeach
                        </datalist>
                    </div>

                    {{-- SEKRETARIS --}}
                    <div class="mb-3 col-md-6">
                        <label>Sekretaris</label>

                        <input type="text" class="form-control input-bg lecturer-name-input"
                            list="lecturer-list-sekretaris" value="{{ $sekretaris->lecturer->user->name ?? '' }}"
                            data-target="#sekretaris_id">

                        <input type="hidden" id="sekretaris_id" name="sekretaris_id"
                            value="{{ $sekretaris->lecturer_id ?? '' }}">

                        <datalist id="lecturer-list-sekretaris">
                            @foreach ($lecturers as $lec)
                            <option value="{{ $lec->user->name }}" data-id="{{ $lec->id }}">
                                @endforeach
                        </datalist>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-2">
                        <button type="submit" class="btn bg-gradient-primary">Save</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.lecturer-name-input').forEach(input => {
        input.addEventListener('input', function () {
            const list = this.getAttribute('list');
            const target = this.getAttribute('data-target');
            const options = document.querySelectorAll(`#${list} option`);

            let selectedId = '';

            options.forEach(opt => {
                if (opt.value === this.value) {
                    selectedId = opt.dataset.id;
                }
            });

            document.querySelector(target).value = selectedId;
        });
    });
</script>

@endsection