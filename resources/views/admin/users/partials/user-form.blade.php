@php
$user = $user ?? null;
$isCreate = $isCreate ?? false;

// Get gender value based on user type
$genderValue = '';
if ($user) {
if ($type === 'student' && $user->student) {
$genderValue = $user->gender;
} elseif ($type === 'lecturer' && $user->lecturer) {
$genderValue = $user->gender;
} elseif ($type === 'admin') {
$genderValue = $user->gender;
}
}
@endphp

<!-- Common User Fields -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}"
            autocomplete="off" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required
            autocomplete="{{ $isCreate ? 'off' : 'email' }}">
    </div>

    <!-- Gender Field (Common for both student and lecturer) -->
    <div class="col-md-4">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-control" required>
            <option value="">Pilih Gender</option>
            <option value="Laki-Laki" {{ old('gender', $genderValue)==='Laki-Laki' ? 'selected' : '' }}>Laki-Laki
            </option>
            <option value="Perempuan" {{ old('gender', $genderValue)==='Perempuan' ? 'selected' : '' }}>Perempuan
            </option>
        </select>
    </div>

    @if (!$isCreate)
    <div class="col-md-4">
        <label class="form-label">Role</label>
        <select name="roles[]" class="form-select choices" multiple required>
            @foreach ($roles as $id => $name)
            <option value="{{ $name }}" {{ $user && $user->hasRole($name) ? 'selected' : '' }}>
                {{ ucfirst($name) }}
            </option>
            @endforeach
        </select>
    </div>
    @endif


    @if ($type === 'student')
    @include('admin.users.partials.student-fields', [
    'student' => $user->student ?? null,
    'isCreate' => $isCreate,
    ])
    @elseif($type === 'lecturer')
    @include('admin.users.partials.lecturer-fields', [
    'lecturer' => $user->lecturer ?? null,
    'isCreate' => $isCreate,
    ])
    @endif
</div>

<!-- Type Specific Fields -->