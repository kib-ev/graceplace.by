@extends('admin.layouts.app')


@section('content')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <h2>График загрузки по часам</h2>

    <form method="GET" action="/admin/appointments-chart">
        <label for="start_date">Начало периода:</label>
        <input type="date" id="start_date" name="start_date" value="{{ request('start_date') ?? now()->startOfMonth()->toDateString() }}">

        <label for="end_date">Конец периода:</label>
        <input type="date" id="end_date" name="end_date" value="{{ request('end_date') ?? now()->toDateString() }}">

        <button type="submit">Показать</button>
    </form>


    <canvas id="appointmentsChart" width="1000" height="400"></canvas>

    <script>
        const ctx = document.getElementById('appointmentsChart').getContext('2d');

        const labels = [...Array(24).keys()].map(h => h + ":00");

        const datasets = [
                @foreach ($chartData as $placeId => $hoursData)
            {
                label: '{{ \App\Models\Place::find($placeId)?->name ?? '0' }}',
                data: [
                    @for ($h = 0; $h < 24; $h++)
                        {{ $hoursData[$h] ?? 0 }},
                    @endfor
                ],
                fill: false,
                borderColor: '{{ string_to_color(\App\Models\Place::find($placeId)?->name ?? '0') }}',
                tension: 0.1
            },
            @endforeach
        ];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Количество записей по часам'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1
                    }
                }
            }
        });
    </script>

@endsection
