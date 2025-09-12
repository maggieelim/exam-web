@if($errors->any())
<div class="alert alert-danger fw-bold" id="errorAlert"
  style="position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;">
  <ul class="mb-0">
    @foreach ($errors->all() as $item)
    <li>{{ $item }}</li>
    @endforeach
  </ul>
</div>
<script>
  setTimeout(function() {
    document.getElementById('errorAlert').style.display = 'none';
  }, 5000);
</script>
@endif

@if (session('error'))
<div class="alert alert-danger fw-bold" id="errorAlert"
  style="position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;">
  {{ session('error') }}
</div>
<script>
  setTimeout(function() {
    document.getElementById('errorAlert').style.display = 'none';
  }, 10000);
</script>
@endif

@if (Session::get('success'))
<div class="alert alert-success text-dark fw-bold" id="successAlert"
  style="position: fixed; top: 10%; right: 10px; max-width: fit-content; z-index: 9999; color: #ffffffff;">
  {{ Session::get('success') }}
</div>
<script>
  setTimeout(function() {
    document.getElementById('successAlert').style.display = 'none';
  }, 5000);
</script>
@endif