@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Статистика</h1>

            <hr>
            <a href="{{ route('admin.places.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            @php
                $appointments = \App\Models\Appointment::all() // ALL!!
            @endphp

            <table class="table table-bordered">

                <tr>
                    <td style="width: 50%;">Количество рабочих мест</td>
                    <td>{{ \App\Models\Place::count() }}</td>
                </tr>

                <tr>
                    <td>Мастеров в базе</td>
                    <td>{{ \App\Models\Master::count() }}</td>
                </tr>

                <tr>
                    <td>Записей / Посещений / Отмен</td>
                    <td>
                        {{ $appointments->count() }} /
                        {{ $appointments->whereNull('canceled_at')->count() }} /
                        {{ $appointments->whereNotNull('canceled_at')->count() }}
                    </td>
                </tr>

                <tr>
                    <td>Записей через ЛК</td>
                    <td>
                        {{ $selfAddedCount = $appointments->sum(function ($item) { return $item->isCreatedByUser() ? 1 : 0; }) }}

                        @if($appointments->count() > 0)
                            ({{ round($selfAddedCount / $appointments->count() * 100, 1) }} %)
                        @endif
                    </td>
                </tr>

                <tr>
                    <td>Часов аренды</td>
                    <td>{{ \App\Models\Appointment::whereNull('canceled_at')->sum('duration') / 60 }}</td>
                </tr>

                <tr>
                    <td>Средний чек</td>
                    <td>{{ number_format(\App\Models\Appointment::whereNull('canceled_at')->sum('price') / (\App\Models\Appointment::whereNull('canceled_at')->count()), 2) }}</td>
                </tr>

                <tr>
                    <td>Средняя стоимость часа</td>
                    <td>{{ number_format(\App\Models\Appointment::whereNull('canceled_at')->sum('price') / (\App\Models\Appointment::whereNull('canceled_at')->sum('duration') / 60), 2) }}</td>
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
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ \App\Models\Appointment::whereNull('canceled_at')->whereMonth('start_at', $i)->whereYear('start_at','2024')->sum('price') }}
                        </td>
                    @endfor
                    <td>
                        {{ \App\Models\Appointment::whereNull('canceled_at')->whereYear('start_at', '2024')->whereYear('start_at','2024')->sum('price') }}
                    </td>
                </tr>

                <tr>
                    <td>Часы аренды</td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ \App\Models\Appointment::whereNull('canceled_at')->whereMonth('start_at', $i)->whereYear('start_at','2024')->sum('duration') / 60 }}
                        </td>
                    @endfor
                    <td>
                        {{ \App\Models\Appointment::whereNull('canceled_at')->whereYear('start_at', '2024')->whereYear('start_at','2024')->sum('duration') / 60 }}
                    </td>
                </tr>

                <tr>
                    <td>Мастера</td>
                    @for($i = 1; $i <=12; $i++)
                        @php
                            $dateStart = \Carbon\Carbon::parse('2024-' . $i . '-01');
                            $dateEnd = $dateStart->clone()->endOfMonth();

                            $newMasters = \App\Models\Master::whereBetween('created_at', [$dateStart, $dateEnd])->count();
                            $periodMasters = \App\Models\Appointment::whereNull('canceled_at')->whereBetween('start_at', [$dateStart, $dateEnd])->distinct('user_id')->count();
                        @endphp
                        <td>
                            @if($newMasters)
                            {{ '+' . $newMasters }} /
                            @endif

                            {{ \App\Models\Appointment::whereNull('canceled_at')->whereBetween('start_at', [$dateStart, $dateEnd])->distinct('user_id')->count() }}
                        </td>
                    @endfor
                    <td>
{{--                        {{ \App\Models\Appointment::whereNull('canceled_at')->whereYear('start_at', '2024')->whereYear('start_at','2024')->sum('price') }}--}}
                    </td>
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
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ \App\Models\Appointment::whereNull('canceled_at')->whereMonth('start_at', $i)->whereYear('start_at','2025')->sum('price') }}
                        </td>
                    @endfor
                    <td>
                        {{ \App\Models\Appointment::whereNull('canceled_at')->whereYear('start_at', '2025')->sum('price') }}
                    </td>
                </tr>

                <tr>
                    <td>Часы аренды</td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ \App\Models\Appointment::whereNull('canceled_at')->whereMonth('start_at', $i)->whereYear('start_at','2025')->sum('duration') / 60 }}
                        </td>
                    @endfor
                    <td>
                        {{ \App\Models\Appointment::whereNull('canceled_at')->whereYear('start_at','2025')->sum('duration') / 60 }}
                    </td>
                </tr>

                <tr>
                    <td>Мастера</td>
                    @for($i = 1; $i <=12; $i++)
                        @php
                            $dateStart = \Carbon\Carbon::parse('2025-' . $i . '-01');
                            $dateEnd = $dateStart->clone()->endOfMonth();

                            $newMasters = \App\Models\Master::whereBetween('created_at', [$dateStart, $dateEnd])->count();
                            $periodMasters = \App\Models\Appointment::whereNull('canceled_at')->whereBetween('start_at', [$dateStart, $dateEnd])->distinct('user_id')->count();
                        @endphp
                        <td>
                            @if($newMasters)
                                {{ '+' . $newMasters }} /
                            @endif

                            {{ \App\Models\Appointment::whereNull('canceled_at')->whereBetween('start_at', [$dateStart, $dateEnd])->distinct('user_id')->count() }}
                        </td>
                    @endfor
                    <td>
                        {{--                        {{ \App\Models\Appointment::whereNull('canceled_at')->whereYear('start_at', '2024')->whereYear('start_at','2024')->sum('price') }}--}}
                    </td>
                </tr>

            </table>
        </div>
    </div>


    <div class="row">
        <div class="col-6">
            @php
                $startDate = \Carbon\Carbon::now()->startOfYear()->startOfWeek();
                $endDate = \Carbon\Carbon::now()->endOfYear()->endOfWeek();
            @endphp
            <table class="table table-bordered">
                <tr>
                    <th></th>
                    <th>
                        Неделя
                    </th>
                    <th>
                        Часов аренды
                    </th>
                    <th>
                        Выручка
                    </th>
                </tr>
                @for($date = $startDate->clone(), $index = 1; $date->lessThan($endDate); $date->addDays(7), $index++)
                    <tr>
                        <td style="background: {{ now()->gt($date) && now()->lt($date->clone()->addWeek()) ? '#a1ff9b' : 'none' }}; width: 1%;">
                            {{ $index}}
                        </td>
                        <td>
                            <a href="https://graceplace.by/admin/appointments?date_from={{ $date->format('Y-m-d') }}&date_to={{ $date->clone()->addDays(6)->format('Y-m-d') }}">
                                {{ $date->format('Y-m-d') }} - {{ $date->clone()->addDays(6)->format('Y-m-d') }}
                            </a>
                        </td>
                        <td>
                            {{ number_format(\App\Models\Appointment::whereNull('canceled_at')->whereBetween('start_at', [$date, $date->clone()->addDays(7)])->sum('duration') / 60, 0) }}
                        </td>

                        <td style="text-align: right;" title="{{ number_format(\App\Models\Appointment::whereNull('canceled_at')->whereBetween('start_at', [$date, $date->clone()->addDays(7)])->get()->sum(function ($a) {
                                return $a->place->price_hour * $a->duration / 60;
                            }) / 1.2, 0) }}">
                            {{ number_format(\App\Models\Appointment::whereNull('canceled_at')->whereBetween('start_at', [$date, $date->clone()->addDays(7)])->sum('price'), 2) }}
                        </td>
                    </tr>
                @endfor
            </table>
        </div>
    </div>
@endsection
