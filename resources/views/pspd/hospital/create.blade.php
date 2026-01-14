@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">

    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Create Rumah Sakit Baru</h5>
        </div>
        <div class="card-body px-4 pt-2 pb-2">

            <form method="POST" action="{{ route('rumah-sakit.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="mb-3 col-md-12">
                        <label>Kode Rumah Sakit</label>
                        <input type="text" name="kode" class="form-control" required>
                        <!-- Dalam form create -->
                    </div>
                    <div class="mb-3 col-md-12">
                        <label>Nama Rumah Sakit</label>
                        <input type="text" name="name" class="form-control" required>
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