@extends('admin.layouts.app')


@section('content')

    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
    </style>

    <h2>Статистика отмен за период {{ $startDate }} — {{ $endDate }}</h2>

    <form method="GET" action="/admin/appointments/cancel-stats">
        <label for="start_date">Начало периода:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">

        <label for="end_date">Конец периода:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">

        <button type="submit">Показать</button>
    </form>

    <p>Потенциальные прибыль при наличии штрафа 50% от стоимости аренды за отмену менее чем за 24 часа.</p>

    <table class="table table-bordered" style="margin-top:20px;">
        <thead>
        <tr>
            <th>Рабочее место</th>
            <th>Количество отмен</th>
            <th>Потенциальные потери (BYN)</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($losses as $loss)
            <tr>
                <td>{{ $loss->place_name }}</td>
                <td>{{ $loss->cancellations_count }}</td>
                <td style="text-align: right;">{{ number_format($loss->potential_loss, 2, ',', ' ') }} BYN</td>
            </tr>
        @endforeach
        <tr>
            <th colspan="2">Итого</th>
            <th style="text-align: right;">{{ number_format($losses->sum('potential_loss'), 2, ',', ' ') }} BYN</th>
        </tr>
        </tbody>
    </table>

@endsection
