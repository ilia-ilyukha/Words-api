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

        .sentences {
            font-size: 8px;
            text-align: left;
        }

        .table-pdf {
            font-size: {{ $fontSize }}px;
        },
        .sentences p,
        hr {
            margin: 0;
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
               @if(in_array("DE", $selectedColums)) <td>{{ $item->DE }}</td> @endif
               @if(in_array("RU", $selectedColums)) <td>{{ $item->RU }}</td> @endif
               @if(in_array("sentences", $selectedColums)) 
                <td class="sentences">
                    <!-- @foreach ($item->sentences as $sentence)
                        <p>{{ $sentence->description_DE }}</p>
                        <p>{{ $sentence->description_RU }}</p>
                        <hr>
                    @endforeach -->

                    @if($sentence = $item->sentences->first())
                    <p>{{ $sentence->description_DE }}</p>
                    <p>{{ $sentence->description_RU }}</p>
                    @endif
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>

    </table>
</body>

</html>