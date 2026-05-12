@extends('admin.layouts.app')

@section('style')
    <style>
        .badge-cash {
            background: lightgreen;
            padding: 2px 5px;
            border-radius: 5px;
        }
        .badge-service {
            background: lightblue;
            padding: 2px 5px;
            border-radius: 5px;
        }
    </style>
@endsection

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
                    <a class="text-danger me-3" href="{{ route('admin.appointments.index') }}">{{ $filterPlace?->name }} (X)</a>
                @endif

                @if(request('master_id'))
                    <a class="text-danger me-3" href="{{ route('admin.appointments.index') }}">{{ $filterMaster?->full_name }} (X)</a>
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
                <form action="{{ route('admin.appointments.create-requirements') }}" method="post" style="display: inline;">
                    @csrf
                    <input type="hidden" name="date" value="{{ $nextDate->format('Y-m-d') }}">
                    <button type="submit" style="color: #333;" class="btn btn-link p-0 me-3">[создать платежные требования]</button>
                </form>
                <a href="{{ route('admin.appointments.merge-closest') }}?date={{ $nextDate->format('Y-m-d') }}">[объединить соседние записи]</a>
            </div>

                @php
                    $appointmentsToDay = $appointments->filter(function ($appointment) use ($nextDate) { return $appointment->start_at->format('Y-m-d') == $nextDate->format('Y-m-d'); });
                    $appointmentsPenaltyDay = $appointmentsToDay->filter(fn ($appointment) => $appointment->paymentRequirements->contains(fn ($r) => $r->isPenalty()));
                    $appointmentsCanceledNoPenaltyDay = $appointmentsToDay
                        ->filter(fn ($appointment) => $appointment->canceled_at !== null)
                        ->filter(fn ($appointment) => ! $appointment->paymentRequirements->contains(fn ($r) => $r->isPenalty()));
                @endphp

                <ul class="nav nav-tabs mb-2" id="appointmentsDayTabs{{ $i }}" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="actual-tab-{{ $i }}" data-bs-toggle="tab" data-bs-target="#actual{{ $i }}" type="button" role="tab" aria-controls="actual{{ $i }}" aria-selected="true">Актуальные ({{ $appointmentsToDay->whereNull('canceled_at')->count() }})</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="penalty-tab-{{ $i }}" data-bs-toggle="tab" data-bs-target="#penalty{{ $i }}" type="button" role="tab" aria-controls="penalty{{ $i }}" aria-selected="false">Штрафы ({{ $appointmentsPenaltyDay->count() }})</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cancel-tab-{{ $i }}" data-bs-toggle="tab" data-bs-target="#cancel{{ $i }}" type="button" role="tab" aria-controls="cancel{{ $i }}" aria-selected="false">Отмененные ({{ $appointmentsCanceledNoPenaltyDay->count() }})</button>
                    </li>
                </ul>

                <div class="tab-content mb-4" id="appointmentsDayTabsContent{{ $i }}">
                    <div class="tab-pane fade show active" id="actual{{ $i }}" role="tabpanel" aria-labelledby="actual-tab-{{ $i }}">
                        <table id="appointmentsList1" class="table table-bordered table-responsive mb-5">

                            @forelse($appointmentsToDay->whereNull('canceled_at') as $appointment)

                                <tr class="{{ !is_null($appointment->canceled_at) ? 'canceled' : '' }}">
                                    <td style="width: 1%; white-space: nowrap;" title="{{ 'id: '.$appointment->id }}">

                                        {{ $appointment->start_at->format('H:i') }} - {{ $appointment->end_at->format('H:i') }}

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
                                                <div class="comment {{ $comment->type }} {{ $comment->user->hasAnyRole(['admin', 'manager']) ? 'admin' : 'master' }} mb-1">
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
                                        @if(count($appointment->paymentRequirements) == 0)
                                            <span style="color: #c1bebe;">{{ number_format($appointment->getExpectedPrice(), 2, '.') }} BYN</span>
                                        @elseif($appointment->isPaid())
                                            <b style="color: #000;">{{ number_format($appointment->paymentRequirements->first()->getPaidAmount(), 2, '.') }} BYN</b>
                                        @else
                                            <span style="color: #000; font-weight: 200;">{{ number_format($appointment->paymentRequirements->first()->expected_amount, 2, '.') }} BYN</span>
                                        @endif

                                        @if($appointment->payments->where('status', 'completed')->where('amount', '>', 0)->count() > 0)
                                            <br>

                                            @foreach($appointment->payments->where('status', 'completed') as $payment)
                                                <span class="badge-{{ $payment->payment_method }}" style="font-size: 0.85em; color: #666;">
                                                    {{ ucfirst($payment->payment_method) }}
                                                </span>
                                            @endforeach
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
                                    <td colspan="10">Нет записей</td>
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
                                    <b title="{{ number_format($appointmentsToDay->whereNull('canceled_at')->sum(function ($a) { return $a->getExpectedPrice(); }), 2, '.') }} BYN">{{ number_format($appointmentsToDay->whereNull('canceled_at')->sum(function ($a) { return count($a->paymentRequirements) > 0 ? $a->paymentRequirements->first()->getPaidAmount() : 0; }), 2, '.') }} BYN</b>
                                </th>

                                <th></th>

                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="penalty{{ $i }}" role="tabpanel" aria-labelledby="penalty-tab-{{ $i }}">
                        <table class="table table-bordered mb-5" id="appointmentsListPenalty{{ $i }}">

                            @forelse($appointmentsPenaltyDay as $appointment)
                                @php $penaltyReq = $appointment->paymentRequirements->firstWhere(fn ($r) => $r->isPenalty()); @endphp

                                <tr class="{{ !is_null($appointment->canceled_at) ? 'canceled' : '' }}">
                                    <td style="width: 1%; white-space: nowrap;" title="{{ 'id: '.$appointment->id }}">
                                        {{ $appointment->start_at?->format('H:i') }} -
                                        {{ $appointment->start_at->copy()->addMinutes($appointment->duration)?->format('H:i') }}

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
                                        @endif
                                    </td>

                                    <td style="">
                                        @if($penaltyReq)
                                            <span class="badge bg-warning text-dark me-1">{{ $penaltyReq->getPenaltyLabel() }}</span>
                                            @if($penaltyReq->remaining_amount > 0)
                                                <span class="badge bg-danger">Долг {{ number_format($penaltyReq->remaining_amount, 2, '.') }} BYN</span>
                                            @else
                                                <span class="badge bg-secondary">Оплачен</span>
                                            @endif
                                            <br>
                                        @endif
                                        {{ $appointment->description }}

                                        <div class="comments">
                                            @foreach($appointment->comments as $comment)
                                                <div class="comment {{ $comment->type }} {{ $comment->user->hasAnyRole(['admin', 'manager']) ? 'admin' : 'master' }} mb-1">
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
                                        @if(count($appointment->paymentRequirements) == 0)
                                            <span style="color: #c1bebe;">{{ number_format($appointment->getExpectedPrice(), 2, '.') }} BYN</span>
                                        @elseif($appointment->isPaid())
                                            <b style="color: #000;">{{ number_format($appointment->paymentRequirements->first()->getPaidAmount(), 2, '.') }} BYN</b>
                                        @else
                                            <span style="color: #000; font-weight: 300;">{{ number_format($appointment->paymentRequirements->first()->expected_amount, 2, '.') }} BYN</span>
                                        @endif

                                        @if($appointment->payments->where('status', 'completed')->count() > 0)
                                            <br>
                                            <span style="font-size: 0.85em; color: #666;">
                                                {{ ucfirst($appointment->payments->where('status', 'completed')->first()->payment_method) }}
                                            </span>
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

                                @php
                                    $totalPenaltyPaid = $appointmentsPenaltyDay->sum(function ($a) {
                                        return $a->payments->where('status', 'completed')->sum('amount');
                                    });
                                @endphp

                                <th style="width: 100px; white-space: nowrap; text-align: right;">
                                    <b>{{ number_format($totalPenaltyPaid, 2, '.') }} BYN</b>
                                </th>

                                <th></th>

                            </tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="cancel{{ $i }}" role="tabpanel" aria-labelledby="cancel-tab-{{ $i }}">
                        <table id="appointmentsList2" class="table table-bordered mb-5">

                            @forelse($appointmentsCanceledNoPenaltyDay as $appointment)

                                <tr class="{{ !is_null($appointment->canceled_at) ? 'canceled' : '' }}">
                                    <td style="width: 1%; white-space: nowrap;" title="{{ 'id: '.$appointment->id }}">
                                        {{ $appointment->start_at?->format('H:i') }} -
                                        {{ $appointment->start_at->copy()->addMinutes($appointment->duration)?->format('H:i') }}

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
                                                <div class="comment {{ $comment->type }} {{ $comment->user->hasAnyRole(['admin', 'manager']) ? 'admin' : 'master' }} mb-1">
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
                                        @if(count($appointment->paymentRequirements) == 0)
                                            <span style="color: #c1bebe;">{{ number_format($appointment->getExpectedPrice(), 2, '.') }} BYN</span>
                                        @elseif($appointment->isPaid())
                                            <b style="color: #000;">{{ number_format($appointment->paymentRequirements->first()->getPaidAmount(), 2, '.') }} BYN</b>
                                        @else
                                            <span style="color: #000; font-weight: 300;">{{ number_format($appointment->paymentRequirements->first()->expected_amount, 2, '.') }} BYN</span>
                                        @endif

                                        @if($appointment->payments->where('status', 'completed')->count() > 0)
                                            <br>
                                            <span style="font-size: 0.85em; color: #666;">
                                                {{ ucfirst($appointment->payments->where('status', 'completed')->first()->payment_method) }}
                                            </span>
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

                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="text-align: right;">ИТОГО</th>

                                @php
                                    $totalCanceledPaid = $appointmentsCanceledNoPenaltyDay->sum(function ($a) {
                                        return $a->payments->where('status', 'completed')->sum('amount');
                                    });
                                @endphp

                                <th style="width: 100px; white-space: nowrap; text-align: right;">
                                    <b>{{ number_format($totalCanceledPaid, 2, '.') }} BYN</b>
                                </th>

                                <th></th>

                            </tr>
                        </table>
                    </div>

                </div>

            @endfor

            <!-- ----------------------------------------->

        </div>
    </div>
@endsection

