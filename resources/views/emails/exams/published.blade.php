<x-mail::message>
  Hasil **{{ $exam->title }} {{ $course->name }}** telah dipublish oleh dosen.

  Silakan login untuk melihat nilai dan feedback dari ujian Anda.

  <x-mail::button :url="route('student.results.show', $exam->exam_code)">
    Lihat Hasil Ujian
  </x-mail::button>

  Terima kasih, Admin PSSK FK Untar
</x-mail::message>