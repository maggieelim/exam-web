@extends('layouts.user_type.auth')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h6 class="mb-0">Upload Soal & Buat Jadwal Ujian</h6>
        </div>
        <div class="card-body pt-4 p-3">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('soal.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Informasi Jadwal Ujian Baru -->
                <h6>Data Jadwal Ujian</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="judul">Judul Ujian</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="jam">Jam</label>
                        <input type="time" name="jam" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="durasi">Durasi (menit)</label>
                        <input type="number" name="durasi" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="ruangan">Ruangan</label>
                        <input type="text" name="ruangan" class="form-control" required>
                    </div>
                </div>

                <!-- Pilih nama blok -->
                <div class="form-group mb-3">
                    <label for="nama_blok">Pilih Nama Blok</label>
                    <select class="form-control" id="nama_blok" name="nama_blok" required>
                        <option value="">-- Pilih Blok --</option>
                        <option>BELAJAR SEPANJANG HAYAT</option>
                        <option>BIOMEDIK I</option>
                        <option>BIOMEDIK II</option>
                        <option>BIOMEDIK III</option>
                        <option>DOCTORPRENEUR</option>
                        <option>ETIKA KEDOKTERAN, HUKUM KED. DAN KEDOKTERAN FORENSIK</option>
                        <option>HUMANIORA</option>
                        <option>ILMU KESEHATAN MASYARAKAT</option>
                        <option>KEGAWATDARURATAN MEDIK</option>
                        <option>LIFESTYLE MEDICINE</option>
                        <option>SIKLUS HIDUP</option>
                        <option>SISTIM ENDOKRIN & METABOLISME</option>
                        <option>SISTIM GASTRO INTESTINAL</option>
                        <option>SISTIM HEMATOLOGI</option>
                        <option>SISTIM HEPATO-BILIER</option>
                        <option>SISTIM IMUN & INFEKSI</option>
                        <option>SISTIM KARDIOVASKULER</option>
                        <option>SISTIM MUSKULO-SKELETAL</option>
                        <option>SISTIM PENGINDERAAN</option>
                        <option>SISTIM REPRODUKSI</option>
                        <option>SISTIM RESPIRASI</option>
                        <option>SISTIM SARAF & KEJIWAAN</option>
                        <option>SISTIM UROGENITAL</option>
                    </select>
                </div>

                <!-- Upload file Excel -->
                <div class="form-group mb-3">
                    <label for="file">Pilih File Excel</label>
                    <input type="file" name="file" class="form-control" required>
                </div>

                <button type="submit" class="btn bg-gradient-dark">Upload Soal</button>
            </form>
        </div>
    </div>
</div>
@endsection
