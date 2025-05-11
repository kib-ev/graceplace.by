@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Записи мастеров</h1>

            <hr>
            <a href="{{ route('admin.appointments.create', request()->all()) }}" class="btn btn-primary me-3">Добавить</a>

            <form style="display: inline-block;" action="">
                <input type="date" name="date_from" value="{{ request('date_from') }}">
                <input type="date" name="date_to" value="{{ request('date_to') }}">
                <input class="btn btn-sm btn-primary" type="submit" value="Применить">
            </form>
            <span style="margin: 0px 20px;"></span>

            <div style="display: inline-block;">
                <div style="display: flex; gap: 20px;">
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->subDays(7)->startOfWeek()->format('Y-m-d'), 'date_to' => now()->subDays(7)->endOfWeek()->format('Y-m-d')]) }}">прошлая неделя</a>
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->startOfWeek()->format('Y-m-d'), 'date_to' => now()->endOfWeek()->format('Y-m-d')]) }}">текущая неделя</a>
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->subDay()->format('Y-m-d'), 'date_to' => now()->subDay()->format('Y-m-d')]) }}">вчера</a>
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}">сегодня</a>
                    <a href="{{ route('admin.appointments.index', ['date_from' => now()->addDay()->format('Y-m-d'), 'date_to' => now()->addDay()->format('Y-m-d')]) }}">завтра</a>
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


                <div class="date" style="font-weight: bold;">
                    <a style="font-size: 20px; color: #333;" href="{{ url('https://graceplace.by?date=' . \Carbon\Carbon::parse($nextDate)->format('Y-m-d')) }}" target="_blank">

                        {{ $nextDate->format('d/m Y') }}

                        {{-- <a style="font-size: 20px; text-decoration: none;" href="{{ request()->fullUrlWithQuery(['date' => \Carbon\Carbon::parse($nextDate)->format('Y-m-d')]) }}"></a>--}}

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
                </div>

            <div class="float-end">
                <a href="{{ route('admin.appointments.merge-closest') }}?date={{ $nextDate->format('Y-m-d') }}">[объединить соседние записи]</a>
            </div>

                @php
                    $appointmentsToDay = $appointments->filter(function ($appointment) use ($nextDate) { return $appointment->start_at->format('Y-m-d') == $nextDate->format('Y-m-d'); });
                @endphp


                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#actual{{ $i }}" type="button" role="tab" aria-controls="profile" aria-selected="true">Актуальные ({{ $appointmentsToDay->whereNull('canceled_at')->count() }})</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#cancel{{ $i }}" type="button" role="tab" aria-controls="home" aria-selected="false">Отмененные ({{ $appointmentsToDay->whereNotNull('canceled_at')->count() }})</button>
                    </li>

                </ul>


                {{--  TAB 1  --}}
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="actual{{ $i }}" role="tabpanel" aria-labelledby="profile-tab">
                        <table id="appointmentsList1" class="table table-bordered table-responsive mb-5">

                            @forelse($appointmentsToDay->whereNull('canceled_at') as $appointment)

                                <tr class="{{ !is_null($appointment->canceled_at) ? 'canceled' : '' }}">
                                    <td style="width: 1%; white-space: nowrap;" title="{{ 'id: '.$appointment->id }}">

                                        @if($appointment->is_full_day)
                                            Полный день
                                        @else
                                            @if(isset($appointment->start_at))
                                                {{ $appointment->start_at?->format('H:i') }} -
                                                {{ $appointment->start_at->addMinutes($appointment->duration)?->format('H:i') }}
                                            @endif
                                        @endif

                                        <br>

                                        <span style="color: #ccc; font-size: 0.7em;">{{ $appointment->created_at->format('d.m.Y H:i') }}</span>

                                    </td>

                                    <td style="width: 1%; min-width: 30px;">
                                        @if($appointment->isCreatedByUser())
                                            <span class="self-added"><i class="fa fa-user"></i></span>
                                        @else

                                        @endif
                                    </td>

                                    <td title="ЕРИП" style="width: 5px; padding: 2px; background: {{ $appointment->user->getSetting('payment_link.place') && $appointment->user->getSetting('payment_link.storage') ? '#4ab728' : '#ff2318' }}">
                                    </td>

                                    <td style="width: 190px;">
                                        @if($appointment->user->master)
                                            <div class="flex-fill" style="display:flex; justify-content: space-between;">
                                                <a href="{{ route('admin.masters.show', $appointment->user->master) }}">
                                                    {{ $appointment->user->name }}
                                                </a>

                                                @if($appointment->user->appointments->first()->start_at->format('Y-m-d') == $nextDate->format('Y-m-d'))
                                                    <span class="text-bg-warning p-2">1</span>
                                                @endif

                                            </div>
                                        @else
                                            <div class="flex-fill" style="display:flex; justify-content: space-between;">
                                                {{ $appointment->user->name }}
                                            </div>
                                        @endif
                                    </td>

                                    @php

                                    @endphp

                                    <td style="width: 140px;">
                                        {{ $appointment->user->phone }}
                                    </td>

                                    <td style="width: 40px;">
                                        @if($appointment->user->master && $appointment->user->master->direct)
                                            <a target="_blank" href="{{ $appointment->user->master->direct }}">direct</a>
                                        @endif
                                    </td>

                                    <td style="width: 180px; white-space: nowrap;">
                                        @if(isset($appointment->place))
                                            <a href="{{ request()->fullUrlWithQuery(['place_id' => $appointment->place_id]) }}">{{ $appointment->place->name }}</a>
                                            {{--                                <a href="{{ route('admin.places.show', $appointment->place) }}?date_from={{ now()->format('Y-m-d') }}&date_to={{ now()->addWeek()->format('Y-m-d') }}">{{ $appointment->place->name }}</a>--}}
                                        @endif
                                    </td>

                                    <td style="">
                                        {!! $appointment->description !!}

                                        <div class="comments">
                                            @foreach($appointment->comments as $comment)
                                                <div class="comment {{ $comment->type }} {{ $comment->user->hasRole('admin') ? 'admin' : 'master' }} mb-1">
                                                    <div class="label" style="font-size: 0.8em; color: #ccc;">
                                                        {{ $comment->created_at->format('d.m.Y H:i') }} - {{ $comment->user->name }}
                                                    </div>
                                                    <div class="text" style="border: 1px solid #ccc; padding: 5px 10px;">
                                                        {!! $comment->text !!}
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if($appointment->canceled_at)
                                                <hr>
                                                @if($appointment->canceled_at < $appointment->start_at)
                                                    <span style="font-size: 0.9em; color: #ccc;">От отмены до начала записи: <br> {{ \Carbon\Carbon::parse($appointment->canceled_at)->diffAsCarbonInterval($appointment->start_at)->forHumans() }}</span>
                                                @else
                                                    <span style="font-size: 0.9em; color: #ccc;">Отмена после начала записи</span>
                                                @endif
                                            @endif
                                        </div>
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

{{--                                    <td style="width: 100px; white-space: nowrap; text-align: right; font-size: 0.9em;">--}}

{{--                                        <a style="text-decoration: none;" href="{{ route('admin.appointments.payments.show', $appointment) }}">--}}
{{--                                            @if($appointment->paymentRequirements()->sum('amount_due'))--}}
{{--                                                {{ $appointment->paymentRequirements()->sum('amount_due') }} BYN--}}
{{--                                            @else--}}
{{--                                                <span style="color: #c1bebe;">{{ number_format($appointment->getExpectedPrice(), 2, '.') }} BYN</span>--}}
{{--                                            @endif--}}


{{--                                            <br>--}}


{{--                                            @if($appointment->isPaid())--}}
{{--                                                <i style="color: #5fdb64;" class="fa fa-check" aria-hidden="true"></i>--}}
{{--                                            @else--}}
{{--                                                <i style="color: #e7e7e7;" class="fa fa-check" aria-hidden="true"></i>--}}
{{--                                            @endif--}}
{{--                                        </a>--}}
{{--                                    </td>--}}


                                </tr>

                            @empty
                                <tr>
                                    <td colspan="9">Нет записей</td>
                                </tr>
                            @endforelse

                            <tr>
                                <th></th>
                                <th></th>
                                <th style="padding: 0px; "></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="text-align: right;">ИТОГО</th>

                                <th style="width: 100px; white-space: nowrap; text-align: right;">
                                    <b title="{{ number_format($appointmentsToDay->whereNull('canceled_at')->sum(function ($a) { return $a->getExpectedPrice(); }), 2, '.') }} BYN">{{ number_format($appointmentsToDay->sum('price'), 2, '.') }} BYN</b>
                                </th>

                                <th></th>

                            </tr>
                        </table>
                    </div>
                </div>


                {{--  TAB 2  --}}
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade" id="cancel{{ $i }}" role="tabpanel" aria-labelledby="profile-tab">
                        <table id="appointmentsList2" class="table table-bordered mb-5">

                            @forelse($appointmentsToDay->whereNotNull('canceled_at') as $appointment)

                                <tr class="{{ !is_null($appointment->canceled_at) ? 'canceled' : '' }}">
                                    <td style="width: 1%; white-space: nowrap;" title="{{ 'id: '.$appointment->id }}">

                                        @if($appointment->is_full_day)
                                            Полный день
                                        @else
                                            @if(isset($appointment->start_at))
                                                {{ $appointment->start_at?->format('H:i') }} -
                                                {{ $appointment->start_at->addMinutes($appointment->duration)?->format('H:i') }}
                                            @endif
                                        @endif

                                        <br>

                                        <span style="color: #ccc; font-size: 0.7em;">{{ $appointment->created_at->format('d.m.Y H:i') }}</span>

                                    </td>

                                    <td style="width: 1%; min-width: 30px;">
                                        @if($appointment->isCreatedByUser())
                                            <span class="self-added"><i class="fa fa-user"></i></span>
                                        @else

                                        @endif
                                    </td>

                                    <td style="width: 190px;">
                                        @if($appointment->user->master)
                                            <div class="flex-fill" style="display:flex; justify-content: space-between;">
                                                <a href="{{ route('admin.masters.show', $appointment->user->master) }}">
                                                    {{ $appointment->user->name }}
                                                </a>
                                            </div>
                                        @else
                                            {{ $appointment->user->name }}
                                        @endif
                                    </td>

                                    <td style="width: 140px;">
                                        {{ $appointment->user->phone }}
                                    </td>

                                    <td style="width: 40px;">
                                        @if($appointment->user->master && $appointment->user->master->direct)
                                            <a target="_blank" href="{{ $appointment->user->master->direct }}">direct</a>
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

                                        <div class="comments">
                                            @foreach($appointment->comments as $comment)
                                                <div class="comment {{ $comment->type }} {{ $comment->user->hasRole('admin') ? 'admin' : 'master' }} mb-1">
                                                    <div class="label" style="font-size: 0.8em; color: #ccc;">
                                                        {{ $comment->created_at->format('d.m.Y H:i') }} - {{ $comment->user->name }}
                                                    </div>
                                                    <div class="text" style="border: 1px solid #ccc; padding: 5px 10px;">
                                                        {{ $comment->text }}
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if($appointment->canceled_at)
                                                <hr>
                                                @if($appointment->canceled_at < $appointment->start_at)
                                                    <span style="font-size: 0.9em; color: #ccc;">От отмены до начала записи: <br> {{ \Carbon\Carbon::parse($appointment->canceled_at)->diffAsCarbonInterval($appointment->start_at)->forHumans() }}</span>
                                                @else
                                                    <span style="font-size: 0.9em; color: #ccc;">Отмена после начала записи</span>
                                                @endif
                                            @endif
                                        </div>
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
                                    <td colspan="10">Нет записей</td>
                                </tr>
                            @endforelse


                        </table>
                    </div>
                </div>

            @endfor

            <!-- ----------------------------------------->

        </div>
    </div>
@endsection

