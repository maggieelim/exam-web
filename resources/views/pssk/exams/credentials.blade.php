<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0.5cm;
        }

        table {
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
    </style>
</head>

<body>
    <table>
        @foreach($credentials as $credential)
        <tr>
            <th>Username</th>
            <td>{{ $credential->username }}</td>
        </tr>
        <tr>
            <th>Password</th>
            <td>{{ $credential->plain_password }}</td>
        </tr>
        @endforeach
    </table>
</body>

</html>