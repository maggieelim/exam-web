@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">

    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Create Kepaniteraan Baru</h5>
        </div>
        <div class="card-body px-4 pt-2 pb-2">
            <form method="POST" action="{{ route('kepaniteraan.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label>Rumah Sakit</label>
                        <input type="text" name="hospital" class="form-control" list="hospital_list"
                            placeholder="Pilih Rumah Sakit" required>

                        <datalist id="hospital_list">
                            @foreach ($hospitals as $hospital )
                            <option value="{{ $hospital->name }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3 col-md-4">
                        <label>Stase</label>
                        <input type="text" name="stase" class="form-control" list="stase_list" placeholder="Pilih Stase"
                            required>

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
                            <option value="{{ $semester->id }}" {{ $semesterId==$semester->id ? 'selected' : ''
                                }}>
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
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
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