@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">

    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Edit Kepaniteraan</h5>
        </div>
        <div class="card-body px-4 pt-2 pb-2">

            <form method="POST" action="{{ route('kepaniteraan.update', $rotation->id) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label>Rumah Sakit</label>
                        <input type="text" name="hospital" class="form-control" list="hospital_list"
                            placeholder="Pilih Rumah Sakit" required
                            value="{{ old('hospital', $rotation->hospital->name) }}">

                        <datalist id="hospital_list">
                            @foreach ($hospitals as $hospital )
                            <option value="{{ $hospital->name }}">
                            </option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Stase</label>
                        <input type="text" name="stase" class="form-control" list="stase_list" placeholder="Pilih Stase"
                            required value="{{ old('stase', $rotation->clinicalRotation->name) }}">

                        <datalist id="stase_list">
                            @foreach ($stases as $stase )
                            <option value="{{ $stase->name }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Semester</label>
                        <select name="semester_id" id="semester_id" class="form-select">
                            @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ old('semester_id', $rotation->semester_id) ==
                                $semester->id ? 'selected' : '' }}>
                                {{ $semester->semester_name }} - {{ $semester->academicYear->year_name }}
                                @if ($activeSemester && $semester->id == $activeSemester->id)
                                (Aktif)
                                @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" required
                            value="{{ old('start_date', optional($rotation->start_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" required
                            value="{{ old('end_date', optional($rotation->end_date)->format('Y-m-d')) }}">
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
@endsection