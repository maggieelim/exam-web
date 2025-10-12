@php
$student = $student ?? null;
$isCreate = $isCreate ?? false;
@endphp


<div class="col-md-4">
  <label class="form-label">NIM</label>
  <input type="text" name="nim" class="form-control"
    value="{{ old('nim', $student->nim ?? '') }}" required>
</div>

@if(!$isCreate)
<div class="col-md-4">
  <label class="form-label">Angkatan</label>
  <input type="text" name="angkatan" class="form-control"
    value="{{ old('angkatan', $student->angkatan ?? '') }}" required>
</div>
@endif