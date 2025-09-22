<!-- data pertanyaan, student, dan informasi lainnya dari kelas yang sudah dipilih
 (halaman details dari kelas tertentu) --><!-- tampilan untuk menampilkan seluruh kelas yang sudah dibuat -->

<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Exam') }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          {{ __("Manage Exam") }}
        </div>
      </div>
    </div>
  </div>
</x-app-layout>