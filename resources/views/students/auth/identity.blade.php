@extends(auth()->check() ? 'layouts.user_type.auth' : 'layouts.user_type.guest')

@section('hideSidebar') @endsection
@section('hideNavbar') @endsection
@section('hideFooter') @endsection
@section('content')
<main class="main-content">
    <section class="min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-7 col-md-8">
                    <div class="card shadow-lg">
                        <div class="card-header position-relative text-center mb-0 pb-0">

                            <!-- Back Button -->
                            <a class="w-10 position-absolute start-0 top-30 translate-middle-y "
                                href="{{ url('/logout') }}">
                                <i class="fa-solid fa-arrow-left fa-lg"></i>
                            </a>
                        </div>

                        <div class="card-body mt-0">
                            <form method="POST" action="{{ route('student.start', $credential->exam_id) }}">
                                @csrf

                                <input type="hidden" name="credential" value="{{ $credential->id }}">

                                <!-- Student Info -->
                                <div class="text-center">
                                    <h5 class="mb-1">{{ $student->user->name ?? '-' }}</h5>
                                    <span class="text-muted">NIM: {{ $student->nim ?? '-' }}</span>
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <label class="form-label">Exam Password</label>
                                    <input type="password" class="form-control" name="password" autocomplete="off"
                                        placeholder="Enter exam password">

                                    @error('password')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Button -->
                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn bg-gradient-info">
                                        <i class="fas fa-play me-1"></i> Start Exam
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection