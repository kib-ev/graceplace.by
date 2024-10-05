@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Записи</h1>

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
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->startOfDay()->format('Y-m-d'), 'date_to' => now()->addDays(30)->endOfDay()->format('Y-m-d')]) }}">сегодня +30</a>
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
                            <th colspan="9">

                                <a style="font-size: 20px; color: #333;" href="{{ url('https://graceplace.by?date=' . \Carbon\Carbon::parse($nextDate)->format('Y-m-d')) }}" target="_blank">

                                    {{ $nextDate->format('d/m Y') }}

{{--                                <a style="font-size: 20px; text-decoration: none;" href="{{ request()->fullUrlWithQuery(['date' => \Carbon\Carbon::parse($nextDate)->format('Y-m-d')]) }}"></a>--}}

                                    [{{ Carbon\Carbon::parse($nextDate)->isoFormat('dddd') }}]
                                </a>


                                @if(now()->subDay()->startOfDay()->equalTo(\Carbon\Carbon::parse($nextDate)->startOfDay()))
                                    <span style="font-size: 20px; color: #ccc;">Вчера</span>
                                @endif

                                @if(now()->startOfDay()->equalTo(\Carbon\Carbon::parse($nextDate)->startOfDay()))
                                    <b style="font-size: 20px; color: #ccc;">Сегодня</b>
                                @endif

                                @if(now()->addDay()->startOfDay()->equalTo($nextDate))
                                    <b style="font-size: 20px; color: #ccc;">Завтра</b>
                                @endif


                            </th>
                        </tr>

                        @php
                            $appointmentsToDay = $appointments->filter(function ($appointment) use ($nextDate) { return $appointment->start_at->format('Y-m-d') == $nextDate->format('Y-m-d'); });
                        @endphp

                        @forelse($appointmentsToDay as $appointment)

                            <tr class="{{ !is_null($appointment->canceled_at) ? 'canceled' : '' }}">
                                {{--                            <td style="width: 50px;">--}}
                                {{--                                {{ $appointment->id }}--}}
                                {{--                            </td>--}}

                                {{--                            <td>{{ $appointment->start_at?->format('d.m.Y') }}</td>--}}

                                <td style="width: 1%; white-space: nowrap;" title="{{ 'id: '.$appointment->id }}">

                                    @if($appointment->is_full_day)
                                        Полный день
                                    @else
                                        @if(isset($appointment->start_at))
                                            {{ $appointment->start_at?->format('H:i') }} -
                                            {{ $appointment->start_at->addMinutes($appointment->duration)?->format('H:i') }}
                                        @endif
                                    @endif

                                </td>

                                <td style="width: 1%; min-width: 30px;">
                                    @if($appointment->isSelfAdded())
                                        <span class="self-added"><i class="fa fa-user"></i></span>
                                    @else

                                    @endif
                                </td>

                                <td style="width: 180px;">
                                    @if(isset($appointment->master))

                                        <div class="flex-fill" style="display:flex; justify-content: space-between;">
                                            <a href="{{ route('admin.masters.show', $appointment->master) }}">{{ $appointment->master->full_name }}</a>

                                        </div>

                                        {{--                                <a href="{{ route('admin.masters.show', $appointment->master) }}">{{ $appointment->master->full_name }}</a>--}}
                                    @endif


                                    <div class="comments">
                                        @foreach($appointment->comments as $comment)
                                            <div class="comment" style="border: 1px solid #ccc; padding: 5px 10px;background: #fbffc5;">
                                                {{ $comment->text }}
                                            </div>
                                        @endforeach
                                    </div>

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

                                <td style="width: 40px;">
                                    @if(isset($appointment->master) && $appointment->master->direct)
                                        <a target="_blank" href="{{ $appointment->master->direct }}">direct</a>
                                    @endif
                                </td>

                                <td style="width: 180px; white-space: nowrap;">
                                    @if(isset($appointment->place))
                                        <a href="{{ request()->fullUrlWithQuery(['place_id' => $appointment->place_id]) }}">{{ $appointment->place->name }}</a>
                                        {{--                                <a href="{{ route('admin.places.show', $appointment->place) }}?date_from={{ now()->format('Y-m-d') }}&date_to={{ now()->addWeek()->format('Y-m-d') }}">{{ $appointment->place->name }}</a>--}}
                                    @endif
                                </td>

                                <td style="">
                                    {{ $appointment->description }}
                                </td>

                                <td style="width: 100px; white-space: nowrap; text-align: right;">

                                    @if(is_null($appointment->price) && isset($appointment->place))
                                        <span style="color: #c1bebe;">{{ $appointment->getExpectedPrice() }} BYN</span>
                                    @else
                                        <b style="color: {{ is_null($appointment->price) ? 'red' : '#000' }}">{{ $appointment->price ?? '-' }} BYN</b>
                                    @endif

                                </td>

                                <td style="width: 1%;">
                                    <a href="{{ route('admin.appointments.edit', $appointment) }}"><i class="fa fa-edit"></i></a>
                                </td>
                            </tr>

                            @empty
                                <tr>
                                    <td colspan="9">Нет записей</td>
                                </tr>
                        @endforelse

                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="text-align: right;">ИТОГО</th>

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

