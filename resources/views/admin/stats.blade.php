@extends('admin.layouts.app')

@section('style')
    <style>
        .current-week td {
            background: #d3ffde;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <h1>Статистика</h1>

            <a href="/admin/appointments/cancel-stats">Статистика отмен</a>
            <a href="/admin/appointments-chart">График загрузки по часам</a>
            <a href="/admin/appointments-stats">Тепловая карта загрузки</a>
            <a href="/admin/revenue/hours">Выручка по часам</a>

            <table class="table table-bordered">
                <tr>
                    <td style="width: 50%;">Количество рабочих мест</td>
                    <td>{{ $placesCount }}</td>
                </tr>
                <tr>
                    <td>Мастеров в базе</td>
                    <td>{{ $mastersCount }}</td>
                </tr>
                <tr>
                    <td>Записей / Посещений / Отмен</td>
                    <td>
                        {{ $appointmentsStats->total }} /
                        {{ $appointmentsStats->visited }} /
                        {{ $appointmentsStats->canceled }}
                    </td>
                </tr>
                <tr>
                    <td>Записей через личный кабинет</td>
                    <td>
                        {{ $appointmentsStats->self_added }}
                        @if($appointmentsStats->total > 0)
                            ({{ round($appointmentsStats->self_added / $appointmentsStats->total * 100, 1) }} %)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Часов аренды</td>
                    <td>{{ $appointmentsStats->total_duration / 60 }}</td>
                </tr>
                <tr>
                    <td>Записей более 8 часов</td>
                    <td>
                        {{ $appointmentsStats->over_8_hours }}
                        @if($appointmentsStats->visited > 0)
                            ({{ round($appointmentsStats->over_8_hours / $appointmentsStats->visited * 100, 1) }} %)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Средний чек</td>
                    <td>{{ $appointmentsStats->visited ? number_format($appointmentsStats->total_price / $appointmentsStats->visited, 2) : 0 }}</td>
                </tr>
                <tr>
                    <td>Средняя стоимость часа</td>
                    <td>{{ $appointmentsStats->total_duration ? number_format($appointmentsStats->total_price / ($appointmentsStats->total_duration / 60), 2) : 0 }}</td>
                </tr>
            </table>

            2024
            <table class="table table-bordered">
                <tr>
                    <td style="width: 110px;"></td>
                    @for($i = 1; $i <=12; $i++)
                        <td style="width: 90px;">{{ \Carbon\Carbon::parse('01-'. $i . '-2024')->format('M-Y') }}</td>
                    @endfor
                    <td><b>ВСЕГО</b></td>
                </tr>
                <tr>
                    <td>Выручка</td>
                    @php $total2024 = 0; @endphp
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ $monthlyStats2024[$i]->revenue ?? 0 }}</td>
                        @php $total2024 += $monthlyStats2024[$i]->revenue ?? 0; @endphp
                    @endfor
                    <td>{{ $total2024 }}</td>
                </tr>
                <tr>
                    <td>Часы аренды</td>
                    @php $hours2024 = 0; @endphp
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ ($monthlyStats2024[$i]->hours ?? 0) / 60 }}</td>
                        @php $hours2024 += $monthlyStats2024[$i]->hours ?? 0; @endphp
                    @endfor
                    <td>{{ $hours2024 / 60 }}</td>
                </tr>
                <tr>
                    <td>Мастера</td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            @if(($newMasters2024[$i]->count ?? 0) > 0)
                                +{{ $newMasters2024[$i]->count }} /
                            @endif
                            {{ $uniqueMasters2024[$i]->count ?? 0 }}
                        </td>
                    @endfor
                    <td></td>
                </tr>
            </table>

            2025
            <table class="table table-bordered">
                <tr>
                    <td style="width: 110px;"></td>
                    @for($i = 1; $i <=12; $i++)
                        <td style="width: 90px;">{{ \Carbon\Carbon::parse('01-'. $i . '-2025')->format('M-Y') }}</td>
                    @endfor
                    <td><b>ВСЕГО</b></td>
                </tr>
                <tr>
                    <td>Выручка</td>
                    @php $total2025 = 0; @endphp
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ $monthlyStats2025[$i]->revenue ?? 0 }}</td>
                        @php $total2025 += $monthlyStats2025[$i]->revenue ?? 0; @endphp
                    @endfor
                    <td>{{ $total2025 }}</td>
                </tr>
                <tr>
                    <td>Часы аренды</td>
                    @php $hours2025 = 0; @endphp
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ ($monthlyStats2025[$i]->hours ?? 0) / 60 }}</td>
                        @php $hours2025 += $monthlyStats2025[$i]->hours ?? 0; @endphp
                    @endfor
                    <td>{{ $hours2025 / 60 }}</td>
                </tr>
                <tr>
                    <td>Мастера</td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            @if(($newMasters2025[$i]->count ?? 0) > 0)
                                +{{ $newMasters2025[$i]->count }} /
                            @endif
                            {{ $uniqueMasters2025[$i]->count ?? 0 }}
                        </td>
                    @endfor
                    <td></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <table class="table table-bordered">
                <tr>
                    <th></th>
                    <th>Неделя</th>
                    <th>Часов аренды</th>
                    <th>Выручка</th>
                </tr>
                @foreach($weeklyStatsPrev as $index => $week)
                    <tr>
                        <td>{{ $index+1 }}</td>
                        <td>
                            <a href="https://graceplace.by/admin/appointments?date_from={{ $week['week_start'] }}&date_to={{ \Carbon\Carbon::parse($week['week_start'])->addDays(6)->format('Y-m-d') }}">
                                {{ $week['week_start'] }} - {{ \Carbon\Carbon::parse($week['week_start'])->addDays(6)->format('Y-m-d') }}
                            </a>
                        </td>
                        <td>{{ number_format($week['hours'] / 60, 0) }} / {{ number_format($week['hours'] / (60 * 7 * 8 * 9) * 100, 0) }} % / {{ 7 * 8 * 9 }}</td>
                        <td style="text-align: right;">{{ number_format($week['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="col-6">
            <table class="table table-bordered">
                <tr>
                    <th></th>
                    <th>Неделя</th>
                    <th>Часов аренды</th>
                    <th>Выручка</th>
                </tr>
                @foreach($weeklyStatsCurr as $index => $week)
                    @php
                        $isCurrentWeek = (\Carbon\Carbon::now()->format('oW') == $week['yearweek']);
                    @endphp
                    <tr class="{{ $isCurrentWeek ? 'current-week' : '' }}">
                        <td>{{ $index+1 }}</td>
                        <td>
                            <a href="https://graceplace.by/admin/appointments?date_from={{ $week['week_start'] }}&date_to={{ \Carbon\Carbon::parse($week['week_start'])->addDays(6)->format('Y-m-d') }}">
                                {{ $week['week_start'] }} - {{ \Carbon\Carbon::parse($week['week_start'])->addDays(6)->format('Y-m-d') }}
                            </a>
                        </td>
                        <td>{{ number_format($week['hours'] / 60, 0) }} / {{ number_format($week['hours'] / (60 * 7 * 8 * 9) * 100, 0) }} % / {{ 7 * 8 * 9 }}</td>
                        <td style="text-align: right;">{{ number_format($week['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
