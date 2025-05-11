@php
    $appointmentsGroups = $appointments->sortBy('start_at')->groupBy(function ($a) { return $a->end_at < now() ? 'finished' : 'active'; })->reverse();
    if(!isset($appointmentsGroups['active'])) {
        $appointmentsGroups['active'] = [];
    }
    if(!isset($appointmentsGroups['finished'])) {
        $appointmentsGroups['finished'] = collect();
    }
@endphp

<ul class="nav nav-tabs" id="myTab" role="tablist" style="margin-bottom: 1px; border-bottom: none;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="home" aria-selected="true">
            Активные ({{ count($appointmentsGroups['active']) }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#finished" type="button" role="tab" aria-controls="profile" aria-selected="false">
            Завершенные ({{ count($appointmentsGroups['finished']) }})
        </button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    @foreach($appointmentsGroups->sort() as $key => $appointmentByStatus)
        <div class="tab-pane fade {{ $key == 'active' ? 'active show' : '' }}" id="{{ $key }}" role="tabpanel" aria-labelledby="home-tab">
            @if(count($appointmentByStatus) == 0)
                <table class="table table-bordered">
                    <tr>
                        <td>Нет записей</td>
                    </tr>
                </table>
            @else
                <table class="table table-bordered">
                    @foreach($appointmentByStatus as $appointment)

                        <tr class="{{ $appointment->canceled_at ? 'canceled' : '' }}">
                            <td style="width: 1%; min-width: 30px;">
                                {{ $loop->index + 1 }}
                            </td>

                            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                                {{ $appointment->start_at->format('d.m.Y') }}
                            </td>

                            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                                {{ short_day_name($appointment->start_at, true) }}
                            </td>

                            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                                @if($appointment->is_full_day)
                                    Полный день
                                @elseif(isset($appointment->start_at))
                                    {{ $appointment->start_at->format('H:i') }} -
                                    {{ $appointment->start_at->addMinutes($appointment->duration)?->format('H:i') }}
                                @endif
                            </td>

                            <td style="width: 1%; min-width: 30px;">
                                @if($appointment->isCreatedByUser())
                                    <span class="self-added"><i class="fa fa-user"></i></span>
                                @endif
                            </td>

                            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                                @if($appointment->user->master)
                                    <a href="{{ route('admin.masters.show', $appointment->user->master) }}">{{ $appointment->user->master->full_name }}</a>
                                @endif
                            </td>

                            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                                @if($appointment->place)
                                    <a href="{{ route('admin.places.show', $appointment->place) }}">{{ $appointment->place->name }}</a>

                                @endif
                            </td>

                            <td style="width: 1%; min-width: 30px; white-space: nowrap; text-align: right;">
                                @if(isset($appointment->price))
                                    @if($appointment->price == 0)
                                        FREE
                                    @else
                                        {{ number_format($appointment->price, 2) }} BYN
                                    @endif

                                @else
                                    <span style="color: #dddddd;">{{ number_format($appointment->getExpectedPrice(), 2) }} BYN</span>
                                @endif
                            </td>

                            <td>
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

                            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                                <a href="{{ route('admin.appointments.edit', $appointment) }}"><span class="fa fa-edit"></span></a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>
    @endforeach
</div>
