@extends('layouts.user_type.auth')

@section('content')
<div class="col-12 mb-4">

    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0">Edit Rumah Sakit</h5>
        </div>
        <div class="card-body px-4 pt-2 pb-2">

            <form method="POST" action="{{ route('rumah-sakit.update', $hospital->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="mb-3 col-md-12">
                        <label>Kode Rumah Sakit</label>
                        <input type="text" name="kode" class="form-control" required
                            value="{{ old('kode', $hospital->code) }}">
                    </div>
                    <div class="mb-3 col-md-12">
                        <label>Nama Rumah Sakit</label>
                        <input type="text" name="name" class="form-control" required
                            value="{{ old('name', $hospital->name) }}">
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