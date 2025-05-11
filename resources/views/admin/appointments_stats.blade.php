@extends('admin.layouts.app')


@section('content')
    @php
        // Преобразуем $stats в массив [place_id][hour] = count
        $data = [];
        $maxCount = 0; // Нужно для определения максимума (чтобы нормализовать цвета)

        foreach ($stats as $stat) {
            $data[$stat->place_id][$stat->hour_of_day] = $stat->total_appointments;
            if ($stat->total_appointments > $maxCount) {
                $maxCount = $stat->total_appointments;
            }
        }

        $places = array_keys($data);
        $hours = range(0, 23);
    @endphp

    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; text-align: center;">
        <thead>
        <tr>
            <th>Place ID \ Час</th>
            @foreach ($hours as $hour)
                <th>{{ $hour }}:00</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($places as $place_id)
            <tr>
                <td><strong>{{ \App\Models\Place::find($place_id)?->name }}</strong></td>
                @foreach ($hours as $hour)
                    @php
                        $count = $data[$place_id][$hour] ?? 0;

                        // Вычисляем интенсивность цвета (от белого до красного)
                        $intensity = $maxCount > 0 ? intval(($count / $maxCount) * 255) : 0;
                        $color = "rgb(255, " . (255 - $intensity) . ", " . (255 - $intensity) . ")";
                    @endphp
                    <td style="background-color: {{ $color }};">
                        {{ $count }}
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>


@endsection
