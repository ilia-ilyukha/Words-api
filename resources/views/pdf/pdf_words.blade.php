<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        /* Поддержка кириллицы */
        .header {
            text-align: center;
        }

        .table-pdf {
            width: 100%;
        }

        .table-pdf td {
            border: 1px black solid;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Kapitel №{{ $capital }}</h1>
    </div>
    <table class='table-pdf'>
        <th> DE </th>
        <th> RU </th>

        <tbody>
            @foreach ($items as $item)
            <tr>
                <td>{{ $item->DE }}</td>
                <td>{{ $item->RU }}</td>
            </tr>
            @endforeach
        </tbody>

    </table>
</body>

</html>