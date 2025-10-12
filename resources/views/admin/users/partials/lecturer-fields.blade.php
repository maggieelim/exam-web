@php
$lecturer = $lecturer ?? null;
$isCreate = $isCreate ?? false;

$strataOptions = ['S1', 'S2', 'S3', 'Sp1', 'Sp2'];
$tipeDosenOptions = ['Asdos', 'CDT', 'DT', 'DTT'];
@endphp

<div class="col-md-4">
  <label class="form-label">NIDN</label>
  <input type="text" name="nidn" class="form-control"
    value="{{ old('nidn', $lecturer->nidn ?? '') }}" required>
</div>

<div class="col-md-4">
  <label class="form-label">Strata</label>
  <select name="strata" class="form-select">
    @foreach($strataOptions as $option)
    <option value="{{ $option }}" {{ (old('strata', $lecturer->strata ?? '') == $option ? 'selected' : '') }}>
      {{ $option }}
    </option>
    @endforeach
  </select>
</div>

<div class="col-md-4">
  <label class="form-label">Gelar</label>
  <input type="text" name="gelar" class="form-control"
    value="{{ old('gelar', $lecturer->gelar ?? '') }}">
</div>

<div class="col-md-4">
  <label class="form-label">Tipe Dosen</label>
  <select name="tipe_dosen" class="form-select">
    @foreach($tipeDosenOptions as $option)
    <option value="{{ $option }}" {{ (old('tipe_dosen', $lecturer->tipe_dosen ?? '') == $option ? 'selected' : '') }}>
      {{ $option }}
    </option>
    @endforeach
  </select>
</div>

<div class="col-md-2">
  <label class="form-label">Min SKS</label>
  <input type="number" name="min_sks" class="form-control"
    value="{{ old('min_sks', $lecturer->min_sks ?? '') }}">
</div>

<div class="col-md-2">
  <label class="form-label">Max SKS</label>
  <input type="number" name="max_sks" class="form-control"
    value="{{ old('max_sks', $lecturer->max_sks ?? '') }}">
</div>

@if(!$isCreate)
<div class="col-md-4">
  <label class="form-label">Role</label>
  <select name="role" class="form-select" required>
    @foreach($roles as $id => $name)
    <option value="{{ $name }}" {{ ($user->hasRole($name) ? 'selected' : '') }}>
      {{ ucfirst($name) }}
    </option>
    @endforeach
  </select>
</div>
@endif