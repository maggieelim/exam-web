<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 3;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 15px;
            margin: 3;
            padding: 3;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <table>
        @foreach(collect($credentials)->chunk(4) as $row)
        <tr>
            @foreach($row as $credential)
            <td>
                <div>Username: {{ $credential->username }}</div>

                <div style="margin-top:4px;">Password: {{ $credential->plain_password }}</div>
            </td>
            @endforeach
        </tr>
        @endforeach
    </table>

</body>

</html>