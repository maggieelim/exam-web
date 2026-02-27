@extends('layouts.user_type.guest')

@section('content')
<main class="main-content  mt-0">
    <section>
        <div class="page-header min-vh-75">
            <div class="container">
                <div class="row">
                    <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                        <div class="card card-plain mt-8">
                            <div class="card-body">
                                <form method="POST" action="{{ route('student.start', $credential->exam_id) }}">
                                    @csrf
                                    <input type="hidden" class="form-control" name="credential" placeholder="Credential"
                                        value="{{ $credential->id }}">
                                    <label>NIM </label>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="nim" value="{{ old('nim') }}"
                                            placeholder="NIM" autocomplete="off">

                                        @error('nim')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <label>Name</label>
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="name" placeholder="Name">
                                        @error('name')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <label>Exam Password</label>
                                    <div class="mb-3">
                                        <input type="password" class="form-control" name="password"
                                            placeholder="Password">

                                        @error('password')
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">
                                            Start
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection