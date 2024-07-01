@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Appointments</h1>

            <hr>
            <a href="{{ route('admin.appointments.create', request()->all()) }}" class="btn btn-primary me-3">Добавить</a>


            <form style="display: inline-block;" action="">
                <input type="date" name="date_from" value="{{ request('date_from') }}">
                <input type="date" name="date_to" value="{{ request('date_to') }}">
                <input class="btn btn-sm btn-primary" type="submit" value="Применить">
            </form>
            <span style="margin: 0px 20px;"></span>

            @php

            @endphp

            <div style="display: inline-block;">
                <div style="display: flex; gap: 20px;">
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->subDays(7)->startOfWeek()->format('Y-m-d'), 'date_to' => now()->subDays(7)->endOfWeek()->format('Y-m-d')]) }}">прошлая неделя</a>
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->startOfWeek()->format('Y-m-d'), 'date_to' => now()->endOfWeek()->format('Y-m-d')]) }}">текущая неделя</a>
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->startOfDay()->format('Y-m-d'), 'date_to' => now()->addDays(7)->endOfDay()->format('Y-m-d')]) }}">сегодня</a>
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->addDays(7)->startOfWeek()->format('Y-m-d'), 'date_to' => now()->addDays(7)->endOfWeek()->format('Y-m-d')]) }}">следующая неделя</a>
                </div>
            </div>

            <hr>

            <div class="mb-3 text-right">
                @if(request('place_id'))
                    <a class="text-danger me-3" href="{{ route('admin.appointments.index') }}">{{ \App\Models\Place::find(request('place_id'))?->name }} (X)</a>
                @endif

                @if(request('master_id'))
                    <a class="text-danger me-3" href="{{ route('admin.appointments.index') }}">{{ \App\Models\Master::find(request('master_id'))?->full_name }} (X)</a>
                @endif

                @if(request('date'))
                    <a class="text-danger me-3" href="{{ route('admin.appointments.index') }}">{{ request('date') }} (X)</a>
                @endif
            </div>

            <!-- ----------------------------------------->

            @for($i = 0, $nextDate = $dateFrom; $i <= $dateFrom->diffInDays($dateTo); $i++, $nextDate = $dateFrom->clone()->addDays($i))


                    <table id="appointmentsList" class="table table-bordered mb-5">
                        <tr>
                            <th colspan="7">
                                <a style="font-size: 20px; text-decoration: none;" href="{{ request()->fullUrlWithQuery(['date' => \Carbon\Carbon::parse($nextDate)->format('Y-m-d')]) }}">{{ $nextDate->format('d/m Y') }}</a>

                                <span style="font-size: 20px; color: #ccc;">[{{ Carbon\Carbon::parse($nextDate)->isoFormat('dddd') }}]</span>

                                @if(now()->subDay()->startOfDay()->equalTo(\Carbon\Carbon::parse($nextDate)->startOfDay()))
                                    <b style="font-size: 20px; color: #ccc;">Вчера</b>
                                @endif

                                @if(now()->startOfDay()->equalTo(\Carbon\Carbon::parse($nextDate)->startOfDay()))
                                    <b style="font-size: 20px; color: #ccc;">Сегодня</b>
                                @endif

                                @if(now()->addDay()->startOfDay()->equalTo($nextDate))
                                    <b style="font-size: 20px; color: #ccc;">Завтра</b>
                                @endif

                                <span style="float: right; font-size: 20px;">
                                    <a style="color: #333;" href="{{ url('https://graceplace.by?date=' . \Carbon\Carbon::parse($nextDate)->format('Y-m-d')) }}" target="_blank">График</a>
                                </span>
                            </th>
                        </tr>

                        @php
                            $appointmentsToDay = $appointments->filter(function ($appointment) use ($nextDate) { return $appointment->date->format('Y-m-d') == $nextDate->format('Y-m-d'); });
                        @endphp

                        @forelse($appointmentsToDay as $appointment)

                            <tr class="{{ !is_null($appointment->canceled_at) ? 'canceled' : '' }}">
                                {{--                            <td style="width: 50px;">--}}
                                {{--                                {{ $appointment->id }}--}}
                                {{--                            </td>--}}

                                {{--                            <td>{{ $appointment->date?->format('d.m.Y') }}</td>--}}

                                <td style="width: 150px;">
                                    @if(isset($appointment->date))
                                        {{ $appointment->date?->format('H:i') }} -
                                        {{ $appointment->date->addMinutes($appointment->duration)?->format('H:i') }}
                                    @endif
                                </td>

                                <td style="width: 250px;">
                                    @if(isset($appointment->master))

                                        <div class="flex-fill" style="display:flex; justify-content: space-between;">
                                            <a href="{{ request()->fullUrlWithQuery(['master_id' => $appointment->master_id]) }}">{{ $appointment->master->full_name }}</a>
                                            <a target="_blank" href="{{ route('admin.masters.show', $appointment->master) }}">?</a>
                                        </div>

                                        {{--                                <a href="{{ route('admin.masters.show', $appointment->master) }}">{{ $appointment->master->full_name }}</a>--}}
                                    @endif

                                    {{--                            @if($appointment->client)--}}
                                    {{--                                <br>--}}
                                    {{--                                {{ $appointment->client->person->first_name }}  {{ $appointment->master->person->last_name }}--}}
                                    {{--                            @endif--}}
                                </td>


                                <td style="width: 140px;">
                                    @if(isset($appointment->master))
                                        {{ $appointment->master->phone }}
                                    @endif
                                </td>


                                <td style="width: 250px;">
                                    @if(isset($appointment->place))
                                        <a href="{{ request()->fullUrlWithQuery(['place_id' => $appointment->place_id]) }}">{{ $appointment->place->name }}</a>
                                        {{--                                <a href="{{ route('admin.places.show', $appointment->place) }}?date_from={{ now()->format('Y-m-d') }}&date_to={{ now()->addWeek()->format('Y-m-d') }}">{{ $appointment->place->name }}</a>--}}
                                    @endif
                                </td>

                                <td style="">
                                    {{ $appointment->description }}
                                </td>

                                <td style="width: 100px; white-space: nowrap; text-align: right;">

                                    @if(is_null($appointment->price))
                                        <span style="color: #e1e1e1;">{{ $appointment->place->price_hour * $appointment->duration / 60 }} BYN</span>
                                    @else
                                        <b style="color: {{ is_null($appointment->price) ? 'red' : '#000' }}">{{ $appointment->price ?? '-' }} BYN</b>
                                    @endif

                                </td>

                                <td style="width: 80px;">
                                    <a href="{{ route('admin.appointments.edit', $appointment) }}">edit</a>
                                </td>
                            </tr>

                            @empty
                                <tr>
                                    <td colspan="7">Нет записей</td>
                                </tr>
                        @endforelse

                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="text-align: right;">Сумма</th>

                            <th style="width: 100px; white-space: nowrap; text-align: right;">
                                <b>{{ number_format($appointmentsToDay->sum('price'), 2, '.') }} BYN</b>
                            </th>

                            <th></th>
                        </tr>
                    </table>
            @endfor

            <!-- ----------------------------------------->




        </div>
    </div>
@endsection

