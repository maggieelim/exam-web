<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 2.54cm;
            margin-top: 1.8cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }

        .info-table td {
            border: none;
            padding: 2px 4px;
        }

        .footer {
            margin-top: 60px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div style="margin-bottom:20px;">

        @php
        $path = public_path('assets/img/Logo-kedokteran-untar.png');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
        @endphp

        <img src="{{ $logo }}" width="120">

    </div>
    <div class="title">
        PERSENTASE PENGUASAAN MATERI {{ $exam->title }}
    </div>

    <div class="title">
        Blok {{ $exam->course->name }} <br>
        Semester {{ $exam->semester->semester_name }} {{ $exam->semester->academicYear->year_name ?? '' }}
    </div>

    <hr>
    <table style="border:none; margin-top:10px;">
        <tr>
            <td style="border:none; width:80px;"><strong>NIM</strong></td>
            <td style="border:none; width:10px;">:</td>
            <td style="border:none;">{{ $student->student->nim }}</td>
        </tr>
        <tr>
            <td style="border:none;"><strong>NAMA</strong></td>
            <td style="border:none;">:</td>
            <td style="border:none;">{{ $student->name }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="60%">Materi</th>
                <th width="30%">Persentase Penguasaan</th>
            </tr>
        </thead>

        <tbody>

            @foreach($scores as $index => $score)

            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td>{{ $score['category_name'] }}</td>
                <td align="center">{{ $score['percentage'] }}%</td>
            </tr>

            @endforeach

        </tbody>
    </table>

    <div class="footer">

        <p>Koord. {{ $exam->course->name }}</p>
        <p>{{ $coordinator->lecturer->user->name }}, {{ $coordinator->lecturer->gelar }}</p>
        <br><br><br>
    </div>

</body>

</html>