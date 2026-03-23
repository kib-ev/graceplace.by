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
                    <td>Локер</td>
                    <td>{{ number_format($totalLockerRevenue ?? 0, 2) }} BYN</td>
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
                    <td>{{ $appointmentsStats->visited ? number_format($appointmentsStats->total_price / $appointmentsStats->visited, 2) : 0 }} BYN</td>
                </tr>
                <tr>
                    <td>Средняя стоимость часа</td>
                    <td>{{ $appointmentsStats->total_duration ? number_format($appointmentsStats->total_price / ($appointmentsStats->total_duration / 60), 2) : 0 }} BYN</td>
                </tr>
                <tr>
                    <td>Штрафы</td>
                    <td>{{ number_format($canceledWithPaymentCount, 2, '.', ',') }} BYN</td>
                </tr>
            </table>

            @foreach($years as $year)
                {{ $year }}
                <table class="table table-bordered">
                    <tr>
                        <td style="width: 110px;"></td>
                        @for($i = 1; $i <=12; $i++)
                            <td style="width: 90px;">{{ \Carbon\Carbon::parse('01-'. $i . '-' . $year)->format('M-Y') }}</td>
                        @endfor
                        <td><b>ВСЕГО</b></td>
                    </tr>
                    <tr>
                        <td>Выручка</td>
                        @php $totalYear = 0; @endphp
                        @for($i = 1; $i <=12; $i++)
                            @php
                                $revenue = $monthlyStats[$year][$i]->revenue ?? 0;
                                $lockerRevenue = $lockerStats[$year][$i]->locker_revenue ?? 0;
                                $totalMonth = $revenue + $lockerRevenue;
                                $totalYear += $totalMonth;
                            @endphp
                            <td style="text-align: right;">{{ number_format($totalMonth, 2) }}</td>
                        @endfor
                        <td style="text-align: right;"><b>{{ number_format($totalYear, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td>Мастера</td>
                        @for($i = 1; $i <=12; $i++)
                            <td style="text-align: right;">
                                @php $newCount = $newMasters[$year][$i]->count ?? 0; @endphp
                                @if($newCount > 0)
                                    +{{ $newCount }} /
                                @endif
                                {{ $uniqueMasters[$year][$i]->count ?? 0 }}
                            </td>
                        @endfor
                        <td></td>
                    </tr>
                </table>
            @endforeach
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
                        <td>{{ number_format($week['hours'] / 60, 0) }} / {{ $placesCount > 0 ? number_format($week['hours'] / (60 * 7 * 8 * $placesCount) * 100, 0) : 0 }} % / {{ 7 * 8 * $placesCount }}</td>
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
                        <td>{{ number_format($week['hours'] / 60, 0) }} / {{ $placesCount > 0 ? number_format($week['hours'] / (60 * 7 * 8 * $placesCount) * 100, 0) : 0 }} % / {{ 7 * 8 * $placesCount }}</td>
                        <td style="text-align: right;">{{ number_format($week['revenue'], 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
