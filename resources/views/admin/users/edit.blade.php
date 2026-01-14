@extends('layouts.user_type.auth')

@section('content')
<div class="row">
  <div class="col-12 mb-4">
    <div class="card">
      <div class="card-header pb-0">
        <h5 class="mb-0">Edit {{ ucfirst($type) }}</h5>
      </div>
      <div class="card-body px-4 pt-2 pb-2">
        <form method="POST" action="{{ route(session('context').'.admin.users.update', [$type, $user->id]) }}">
          @csrf
          @include('admin.users.partials.user-form', ['isCreate' => false])

          <div class="row mt-4">
            <div class="col-md-2">
              <button type="submit" class="btn bg-gradient-primary w-100">Update</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection