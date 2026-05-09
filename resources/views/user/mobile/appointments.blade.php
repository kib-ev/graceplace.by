@extends('public.layouts.app')

@section('master-menu')
@endsection

@push('styles')
    <style>
        .mobile-appt-card {
            border: 1px solid #dfe3e8;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 10px;
            background: #fff;
        }
        .mobile-appt-date-block {
            margin: 10px 0 6px;
            padding: 8px 10px;
            border-radius: 10px;
            background: #595959;
            color: #f8fafc;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .mobile-appt-date-block::-webkit-details-marker {
            display: none;
        }
        .mobile-appt-date-block::after {
            content: '\f068';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 12px;
            color: #1e293b;
            background: #e5e7eb;
            border-radius: 999px;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }
        .mobile-day-group:not([open]) .mobile-appt-date-block::after {
            content: '\2b';
        }
        .mobile-appt-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .mobile-appt-date {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }
        .mobile-appt-place {
            margin-top: 4px;
            color: #374151;
            font-size: 14px;
        }
        .mobile-appt-sum {
            margin-top: 6px;
            font-size: 15px;
            color: #111827;
        }
        .mobile-appt-sum-old {
            text-decoration: line-through;
            opacity: 0.7;
            margin-right: 6px;
        }
        .mobile-appt-sum-new {
            color: #b91c1c;
            font-weight: 700;
        }
        .mobile-appt-meta {
            margin-top: 4px;
            color: #6b7280;
            font-size: 13px;
        }
        .mobile-appt-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        #appointmentsTabs {
            display: flex;
            flex-wrap: nowrap;
        }
        #appointmentsTabs .nav-item {
            flex: 1 1 33.3333%;
        }
        #appointmentsTabs .nav-link {
            width: 100%;
            text-align: center;
            white-space: nowrap;
            font-size: 14px;
            padding: 10px 4px;
            line-height: 1.2;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-2 mt-2">
        <div class="col-12">
            <h4 class="mb-2">Мои записи</h4>
            <div class="text-muted small">Все записи за последние 30 дней и записи с незакрытой оплатой.</div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <ul class="nav nav-tabs mb-2" id="appointmentsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    @php
                        $todayDate = now()->toDateString();
                        $todayAppointments = $upcomingAppointments
                            ->merge($completedAppointments)
                            ->filter(fn($a) => $a->start_at?->toDateString() === $todayDate)
                            ->sortByDesc('start_at')
                            ->values();
                    @endphp
                    <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today-pane" type="button" role="tab" aria-controls="today-pane" aria-selected="true">Сегодня</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming-pane" type="button" role="tab" aria-controls="upcoming-pane" aria-selected="false">Предстоящие</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed-pane" type="button" role="tab" aria-controls="completed-pane" aria-selected="false">Завершенные</button>
                </li>
            </ul>

            <div class="tab-content" id="appointmentsTabsContent">
                <div class="tab-pane fade show active" id="today-pane" role="tabpanel" aria-labelledby="today-tab" tabindex="0">
                    <div class="mobile-appt-meta mb-2">Записей: {{ $todayAppointments->count() }}</div>
                    @php $prevDate = null; @endphp
                    @forelse($todayAppointments as $appointment)
                        @php
                            $start = \Carbon\Carbon::parse($appointment->start_at);
                            $end = $start->copy()->addMinutes($appointment->duration);
                            $isCanceled = ! is_null($appointment->canceled_at);
                            $isInProgress = now()->greaterThanOrEqualTo($start) && now()->lessThan($end);
                            $isEnded = now()->greaterThanOrEqualTo($end);
                            $statusLabel = $isCanceled
                                ? 'Отменена'
                                : ($isInProgress ? 'Идет' : ($isEnded ? 'Завершена' : 'Предстоит'));
                            $statusClass = $isCanceled
                                ? 'bg-danger'
                                : ($isInProgress ? 'bg-warning text-dark' : ($isEnded ? 'bg-secondary' : 'bg-primary'));
                            $fullAmount = (new \App\Services\AppointmentService())->calculateAppointmentCost($appointment);
                            $leftToPay = $appointment->leftToPay();
                            $penaltyRequirement = $appointment->paymentRequirements->first(fn($r) => $r->isPenalty() && $r->remaining_amount > 0);
                        @endphp
                        @if($prevDate !== $start->toDateString())
                            <div class="mobile-appt-date-block">{{ $start->isoFormat('D MMM') }} • {{ mb_strtoupper($start->isoFormat('dd')) }}</div>
                            @php $prevDate = $start->toDateString(); @endphp
                        @endif
                        <div class="mobile-appt-card js-appt-card"
                             data-filter-group="today"
                             data-date="{{ $start->toDateString() }}"
                             data-is-canceled="{{ $isCanceled ? '1' : '0' }}"
                             data-is-unpaid="{{ $leftToPay > 0 ? '1' : '0' }}">
                            <div class="mobile-appt-top">
                                <div>
                                    <div class="mobile-appt-date">{{ $start->format('H:i') }} - {{ $end->format('H:i') }}</div>
                                </div>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="mobile-appt-place">{{ $appointment->place->name ?? '—' }}</div>
                            <div class="mobile-appt-sum">
                                @if($isCanceled && $penaltyRequirement)
                                    <span class="mobile-appt-sum-old">{{ number_format($fullAmount, 2) }}</span>
                                    <span class="mobile-appt-sum-new">{{ number_format($leftToPay, 2) }} BYN</span>
                                @else
                                    {{ number_format($leftToPay > 0 ? $leftToPay : $fullAmount, 2) }} BYN
                                @endif
                            </div>
                            @if($isCanceled && $penaltyRequirement)
                                <div class="mobile-appt-meta">{{ $penaltyRequirement->getPenaltyLabel() }}</div>
                            @elseif($leftToPay > 0)
                                <div class="mobile-appt-meta">Осталось к оплате: {{ number_format($leftToPay, 2) }} BYN</div>
                            @endif
                            @if(! $isCanceled && ! $isEnded)
                                <div class="mobile-appt-actions">
                                    <form method="POST" action="{{ route('user.appointments.cancel', $appointment) }}">
                                        @csrf
                                        <input type="hidden" name="cancel_penalty" value="default">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Отменить запись без штрафа?')">Отмена</button>
                                    </form>
                                    <form method="POST" action="{{ route('user.appointments.cancel', $appointment) }}">
                                        @csrf
                                        <input type="hidden" name="cancel_penalty" value="{{ $isInProgress ? 'penalty_100' : 'penalty_50' }}">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Отменить запись со штрафом?')">Отмена со штрафом</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <details class="mobile-day-group" open>
                            <summary class="mobile-appt-date-block">{{ now()->isoFormat('D MMM') }} • {{ mb_strtoupper(now()->isoFormat('dd')) }}</summary>
                            <div class="mobile-appt-card">
                                <div class="mobile-appt-meta">Нет записей.</div>
                            </div>
                        </details>
                    @endforelse
                </div>

                <div class="tab-pane fade" id="upcoming-pane" role="tabpanel" aria-labelledby="upcoming-tab" tabindex="0">
                    <div class="mobile-appt-meta mb-2">Записей: {{ $upcomingAppointments->count() }}</div>
                    @php $prevDate = null; @endphp
                    @forelse($upcomingAppointments as $appointment)
                        @php
                            $start = \Carbon\Carbon::parse($appointment->start_at);
                            $end = $start->copy()->addMinutes($appointment->duration);
                            $isCanceled = ! is_null($appointment->canceled_at);
                            $isInProgress = now()->greaterThanOrEqualTo($start) && now()->lessThan($end);
                            $isEnded = now()->greaterThanOrEqualTo($end);
                            $statusLabel = $isCanceled
                                ? 'Отменена'
                                : ($isInProgress ? 'Идет' : ($isEnded ? 'Завершена' : 'Предстоит'));
                            $statusClass = $isCanceled
                                ? 'bg-danger'
                                : ($isInProgress ? 'bg-warning text-dark' : ($isEnded ? 'bg-secondary' : 'bg-primary'));
                            $fullAmount = (new \App\Services\AppointmentService())->calculateAppointmentCost($appointment);
                            $leftToPay = $appointment->leftToPay();
                            $penaltyRequirement = $appointment->paymentRequirements->first(fn($r) => $r->isPenalty() && $r->remaining_amount > 0);
                        @endphp
                        @if($prevDate !== $start->toDateString())
                            @if(!is_null($prevDate))
                                </details>
                            @endif
                            @php $prevDate = $start->toDateString(); @endphp
                            <details class="mobile-day-group" open>
                                <summary class="mobile-appt-date-block">{{ $start->isoFormat('D MMM') }} • {{ mb_strtoupper($start->isoFormat('dd')) }}</summary>
                        @endif
                        <div class="mobile-appt-card js-appt-card"
                             data-filter-group="upcoming"
                             data-date="{{ $start->toDateString() }}"
                             data-is-canceled="{{ $isCanceled ? '1' : '0' }}"
                             data-is-unpaid="{{ $leftToPay > 0 ? '1' : '0' }}">
                            <div class="mobile-appt-top">
                                <div>
                                    <div class="mobile-appt-date">{{ $start->format('H:i') }} - {{ $end->format('H:i') }}</div>
                                </div>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="mobile-appt-place">{{ $appointment->place->name ?? '—' }}</div>
                            <div class="mobile-appt-sum">
                                @if($isCanceled && $penaltyRequirement)
                                    <span class="mobile-appt-sum-old">{{ number_format($fullAmount, 2) }}</span>
                                    <span class="mobile-appt-sum-new">{{ number_format($leftToPay, 2) }} BYN</span>
                                @else
                                    {{ number_format($leftToPay > 0 ? $leftToPay : $fullAmount, 2) }} BYN
                                @endif
                            </div>
                            @if($isCanceled && $penaltyRequirement)
                                <div class="mobile-appt-meta">{{ $penaltyRequirement->getPenaltyLabel() }}</div>
                            @elseif($leftToPay > 0)
                                <div class="mobile-appt-meta">Осталось к оплате: {{ number_format($leftToPay, 2) }} BYN</div>
                            @endif
                            @if(! $isCanceled && ! $isEnded)
                                <div class="mobile-appt-actions">
                                    <form method="POST" action="{{ route('user.appointments.cancel', $appointment) }}">
                                        @csrf
                                        <input type="hidden" name="cancel_penalty" value="default">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Отменить запись без штрафа?')">Отмена</button>
                                    </form>
                                    <form method="POST" action="{{ route('user.appointments.cancel', $appointment) }}">
                                        @csrf
                                        <input type="hidden" name="cancel_penalty" value="{{ $isInProgress ? 'penalty_100' : 'penalty_50' }}">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Отменить запись со штрафом?')">Отмена со штрафом</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-light border">Нет предстоящих записей.</div>
                    @endforelse
                    @if(!is_null($prevDate))
                        </details>
                    @endif
                </div>

                <div class="tab-pane fade" id="completed-pane" role="tabpanel" aria-labelledby="completed-tab" tabindex="0">
                    <div class="mobile-appt-meta mb-2">Записей: {{ $completedAppointments->count() }}</div>
                    @php $prevDate = null; @endphp
                    @forelse($completedAppointments as $appointment)
                        @php
                            $start = \Carbon\Carbon::parse($appointment->start_at);
                            $end = $start->copy()->addMinutes($appointment->duration);
                            $isCanceled = ! is_null($appointment->canceled_at);
                            $isInProgress = now()->greaterThanOrEqualTo($start) && now()->lessThan($end);
                            $isEnded = now()->greaterThanOrEqualTo($end);
                            $statusLabel = $isCanceled
                                ? 'Отменена'
                                : ($isInProgress ? 'Идет' : ($isEnded ? 'Завершена' : 'Предстоит'));
                            $statusClass = $isCanceled
                                ? 'bg-danger'
                                : ($isInProgress ? 'bg-warning text-dark' : ($isEnded ? 'bg-secondary' : 'bg-primary'));
                            $fullAmount = (new \App\Services\AppointmentService())->calculateAppointmentCost($appointment);
                            $leftToPay = $appointment->leftToPay();
                            $penaltyRequirement = $appointment->paymentRequirements->first(fn($r) => $r->isPenalty() && $r->remaining_amount > 0);
                        @endphp
                        @if($prevDate !== $start->toDateString())
                            @if(!is_null($prevDate))
                                </details>
                            @endif
                            @php $prevDate = $start->toDateString(); @endphp
                            <details class="mobile-day-group" open>
                                <summary class="mobile-appt-date-block">{{ $start->isoFormat('D MMM') }} • {{ mb_strtoupper($start->isoFormat('dd')) }}</summary>
                        @endif
                        <div class="mobile-appt-card js-appt-card"
                             data-filter-group="completed"
                             data-date="{{ $start->toDateString() }}"
                             data-is-canceled="{{ $isCanceled ? '1' : '0' }}"
                             data-is-unpaid="{{ $leftToPay > 0 ? '1' : '0' }}">
                            <div class="mobile-appt-top">
                                <div>
                                    <div class="mobile-appt-date">{{ $start->format('H:i') }} - {{ $end->format('H:i') }}</div>
                                </div>
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <div class="mobile-appt-place">{{ $appointment->place->name ?? '—' }}</div>
                            <div class="mobile-appt-sum">
                                @if($isCanceled && $penaltyRequirement)
                                    <span class="mobile-appt-sum-old">{{ number_format($fullAmount, 2) }}</span>
                                    <span class="mobile-appt-sum-new">{{ number_format($leftToPay, 2) }} BYN</span>
                                @else
                                    {{ number_format($leftToPay > 0 ? $leftToPay : $fullAmount, 2) }} BYN
                                @endif
                            </div>
                            @if($isCanceled && $penaltyRequirement)
                                <div class="mobile-appt-meta">{{ $penaltyRequirement->getPenaltyLabel() }}</div>
                            @elseif($leftToPay > 0)
                                <div class="mobile-appt-meta">Осталось к оплате: {{ number_format($leftToPay, 2) }} BYN</div>
                            @endif
                            @if(! $isCanceled && ! $isEnded)
                                <div class="mobile-appt-actions">
                                    <form method="POST" action="{{ route('user.appointments.cancel', $appointment) }}">
                                        @csrf
                                        <input type="hidden" name="cancel_penalty" value="default">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Отменить запись без штрафа?')">Отмена</button>
                                    </form>
                                    <form method="POST" action="{{ route('user.appointments.cancel', $appointment) }}">
                                        @csrf
                                        <input type="hidden" name="cancel_penalty" value="{{ $isInProgress ? 'penalty_100' : 'penalty_50' }}">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Отменить запись со штрафом?')">Отмена со штрафом</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="alert alert-light border">Нет завершенных записей.</div>
                    @endforelse
                    @if(!is_null($prevDate))
                        </details>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
