@extends('layouts.user_type.auth')

@section('content')
<div class="card">
    <div class="card-header pb-0">
        <h5 class="mb-0">Penugasan Kepaniteraan</h5>
    </div>
    <div class="card-body row px-4 pt-2 pb-2">
        <div class="col-md-4">
            <p><strong>Rumah Sakit:</strong> {{ $rotation->hospital->name }}</p>
        </div>
        <div class="col-md-4">
            <p><strong>Stase:</strong> {{ $rotation->clinicalRotation->name }}</p>
        </div>
        <div class="col-md-4">
            <p><strong>Semester:</strong> {{ $rotation->semester->semester_name }} {{
                $rotation->semester->academicYear->year_name }}</p>
        </div>
        <div class="col-md-4">
            <p><strong>Periode:</strong> {{ $rotation->start_date->format('d M Y') }} - {{
                $rotation->end_date->format('d M Y') }}</p>
        </div>

        <div class="col-md-8">
            <p class="pb-0 mb-0"><strong>Lecturers:</strong></p>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($lecturers as $lecturer)
                <span class="badge bg-info" style="text-transform: none;">
                    {{ $lecturer->lecturer->user->name }}, {{ $lecturer->lecturer->gelar }}
                </span>

                @endforeach
            </div>
        </div>
        <div class="card-header pb-0">
            <h5 class="mb-0">Peserta Kepaniteraan</h5>
        </div>
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                            Nama</th>
                        <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                            NIM</th>
                        <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                            Gender</th>
                        <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                            Ankatan</th>
                        <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                            Status</th>
                        <th class="text-center text-uppercase text-dark text-sm font-weight-bolder">
                            Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student )
                    <tr>
                        <td class="align-middle text-center text-sm font-weight-bold">
                            {{ $student->student->user->name }}
                        </td>
                        <td class="align-middle text-center text-sm font-weight-bold">
                            {{ $student->student->nim }}
                        </td>
                        <td class="align-middle text-center text-sm font-weight-bold">
                            {{ $student->student->user->gender }}
                        </td>
                        <td class="align-middle text-center text-sm font-weight-bold">
                            {{ $student->student->angkatan }}
                        </td>
                        <td class="align-middle text-center text-sm font-weight-bold">
                            {{ ucfirst($student->status) }}
                        </td>
                        <td class="align-middle text-center">
                            <a href="{{ route(session('context').'.admin.users.show', ['student', $student->student->user->id]) }}"
                                class="btn bg-gradient-info m-1 p-2 px-3" title="Info">
                                <i class="fas fa-info-circle"></i>
                            </a>
                            <form action="{{ route('mahasiswa-koas.destroy', [$student->id, $rotation->id]) }}"
                                method="POST" class="d-inline"
                                onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini dari kepaniteraan?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn bg-gradient-danger m-1 p-2 px-3"
                                    title="Hapus Mahasiswa Koas">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            <x-pagination :paginator="$students" />
        </div>
    </div>
</div>
@endsection