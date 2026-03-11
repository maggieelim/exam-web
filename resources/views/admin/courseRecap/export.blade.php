<table border="1" style="border-collapse: collapse; width:100%; font-family: Arial, sans-serif;">
    @php
    $groupedCourses = $courses->groupBy('sesi');
    @endphp

    <thead>
        <tr>
            <th rowspan="3" style="text-align:center; vertical-align:middle; padding:6px; border:1px solid #000; 
                       background-color:#D9E1F2; width:40; white-space:normal;">
                Nama Dosen
            </th>

            @foreach ($groupedCourses as $sesi => $courseGroup)
            <th colspan="{{ $courseGroup->count() * $activities->count() }}" style="text-align:center; vertical-align:middle; padding:6px; border:1px solid #000; 
                       background-color:#D9E1F2;">
                Sesi {{ $sesi ?? '-' }}
            </th>
            @endforeach
        </tr>

        <tr>
            @foreach ($groupedCourses as $courseGroup)
            @foreach ($courseGroup as $course)
            <th colspan="{{ $activities->count() }}" style="text-align:center; vertical-align:middle; padding:6px; border:1px solid #000; 
                           background-color:#E7EEF9;">
                {{ $course->name }}
            </th>
            @endforeach
            @endforeach
        </tr>

        <tr>
            @foreach ($courses as $course)
            @foreach ($activities as $activity)
            <th style="text-align:center; vertical-align:middle; padding:6px; border:1px solid #000; 
                           background-color:#F2F2F2;">
                {{ $activity }}
            </th>
            @endforeach
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach ($lecturers as $lecturer)
        <tr>
            <td style="padding:6px; border:1px solid #000; vertical-align:middle; width:40; white-space:normal;">
                {{ $lecturer->user->name }}, {{ $lecturer->gelar }}
            </td>

            @foreach ($courses as $course)
            @foreach ($activities as $activity)
            <td style="text-align:center; vertical-align:middle; padding:6px; border:1px solid #000;">
                {{ $summary[$lecturer->id][$course->id][$activity] ?? 0 }}
            </td>
            @endforeach
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>