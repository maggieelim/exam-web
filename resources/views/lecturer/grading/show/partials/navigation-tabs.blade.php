@php
$tabs = [
[
'key' => 'results',
'label' => 'Exam Results',
'route' => route('lecturer.results.show.' . $status, $exam->exam_code) . '?tab=results',
'active' => $activeTab === 'results',
],
[
'key' => 'answers',
'label' => 'Question Analytics',
'route' => route('lecturer.results.show.' . $status, $exam->exam_code) . '?tab=answers',
'active' => $activeTab === 'answers',
],
];
@endphp

<div class="nav-wrapper position-relative end-0 my-4">
    <ul class="nav nav-pills nav-fill p-1" id="examTabs" role="tablist">
        @foreach ($tabs as $tab)
        <li class="nav-item w-100 w-md-50" role="presentation">
            <a class="nav-link {{ $tab['active'] ? 'active' : '' }}" href="{{ $tab['route'] }}"
                aria-selected="{{ $tab['active'] ? 'true' : 'false' }}">
                {{ $tab['label'] }}
            </a>
        </li>
        @endforeach
    </ul>
</div>