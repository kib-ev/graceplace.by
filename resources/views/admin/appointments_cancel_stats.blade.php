@extends('admin.layouts.app')


@section('content')

    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
    <h2>Статистика отмен за период {{ $startDate }} — {{ $endDate }}</h2>

    <form method="GET" action="/admin/appointments/cancel-stats">
        <label for="start_date">Начало периода:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">
        <label for="end_date">Конец периода:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">
        <button type="submit">Показать</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>Place ID</th>
            <th>Отмены &lt; 24ч</th>
            <th>Отмены &lt; 2ч</th>
            <th>Потенциальный штраф 50%</th>
            <th>Потенциальный штраф 100%</th>
            <th>Итого потеряно</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalLost = 0;
        @endphp
        @foreach ($stats as $row)
            @php
                $lost = $row->potential_fee_50 + $row->potential_fee_100;
                $totalLost += $lost;
            @endphp
            <tr>
                <td>{{ \App\Models\Place::find($row->place_id)?->name }}</td>
                <td>{{ $row->canceled_under_24h }}</td>
                <td>{{ $row->canceled_under_2h }}</td>
                <td>{{ number_format($row->potential_fee_50, 2, '.', ' ') }} BYN</td>
                <td>{{ number_format($row->potential_fee_100, 2, '.', ' ') }} BYN</td>
                <td><strong>{{ number_format($lost, 2, '.', ' ') }} BYN</strong></td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th colspan="5">Итого по всем местам</th>
            <th><strong>{{ number_format($totalLost, 2, '.', ' ') }} BYN</strong></th>
        </tr>
        </tfoot>
    </table>

@endsection
