@if(auth()->user() && auth()->user()->hasRole('master'))
    @php
        $masterAppointments = auth()->user()->appointments()->whereNull('canceled_at')->where('start_at', '>=', now())->get();
    @endphp

    <div class="row mb-3 mt-3">
        <div class="col-12">
            <a data-bs-toggle="collapse" href="#collapseAppointments" role="button">
                Мои записи
            </a>
            <div class="collapse" id="collapseAppointments">
                <div class="card card-body overflow-scroll">

                    @if($masterAppointments->count() > 0)
                        <table class="table table-sm table-bordered table-responsive mb-0">
                            <tr>
                                <th class="bg-secondary text-white" colspan="3" >Дата и время</th>
                                <th class="bg-secondary text-white">Рабочее место</th>
                                <th class="bg-secondary text-white">Сумма</th>
                                <th class="bg-secondary text-white">Отмена</th>
                            </tr>
                            @foreach($masterAppointments->load('place')->sortBy('start_at')->groupBy(function ($a) { return $a->start_at->isoFormat('D MMM'); }) as $masterDate => $masterAppointmentByDate)

                                @foreach($masterAppointmentByDate as $nextAppointment)
                                    <tr data-index="{{ $loop->index }}" class="appointment-info {{ $masterAppointmentByDate->count() == 1 ? 'js_app_'.$nextAppointment->id : '' }}">

                                        @if($loop->index == 0)
                                            <td class="bg-white text-nowrap" style="width: 1%;" rowspan="{{ $masterAppointmentByDate->count() }}">
                                                <a href="/?date={{ $nextAppointment->start_at->format('Y-m-d') }}">{{ $masterDate }}</a>
                                            </td>
                                            <td class="bg-white text-nowrap" style="width: 1%;" rowspan="{{ $masterAppointmentByDate->count() }}">
                                                <span>{{ mb_strtoupper($nextAppointment->start_at->isoFormat('dd')) }}</span>
                                            </td>
                                        @endif

                                        <td class="bg-white js_app_{{ $nextAppointment->id }}" style="display: none;">
                                            ID: <span class="js_appointment-id">{{ $nextAppointment->id }}</span>
                                        </td>

                                        <td class="bg-white js_app_{{ $nextAppointment->id }}" style="display: none;">
                                            <span class="js_appointment-date">{{ $nextAppointment->start_at->isoFormat('D MMM') }}</span>
                                        </td>

                                        <td class="bg-white text-nowrap js_app_{{ $nextAppointment->id }}" style="width: 1%;">

                                                <span class="js_appointment-time">
                                                    {{ $nextAppointment->start_at->format('H:i') }} - {{ $nextAppointment->end_at->format('H:i') }}
                                                </span>

                                        </td>

                                        <td class="bg-white js_app_{{ $nextAppointment->id }} text-nowrap">
                                            <span class="js_appointment-place">{{ $nextAppointment->place->name }}</span>
                                        </td>

                                        <td class="bg-white text-nowrap text-end">
                                            {{ number_format((new \App\Services\AppointmentService())->calculateAppointmentCost($nextAppointment), 2) }} BYN
                                        </td>

                                        <td class="bg-white text-nowrap js_app_{{ $nextAppointment->id }}">
                                            @if(auth()->user() && auth()->user()->can('cancel appointment') && $nextAppointment->canBeCancelledByUser())
                                                <a class="btn btn-sm btn-danger js_cancel-appointment" style="line-height: 13px;">
                                                    Отменить
                                                </a>
                                            @else
                                                <a target="_blank" href="https://ig.me/m/beautycoworkingminsk">Через Direct</a>
                                            @endif
                                        </td>


                                    </tr>

                                @endforeach
                            @endforeach
                        </table>
                    @else
                        <table class="table table-sm table-bordered mb-0">
                            <tr>
                                <td>У вас нет предстоящих записей.</td>
                            </tr>
                        </table>
                    @endif

                    <p class="mt-1 mb-0">Отмена записей менее чем за {{ \App\Models\Appointment::CANCELLATION_TIMEOUT }} {{ trans_choice('час|часа|часов', \App\Models\Appointment::CANCELLATION_TIMEOUT) }}
                        производится <a target="_blank" href="https://ig.me/m/beautycoworkingminsk">через Direct</a>.</p>

                    <!-- Modal -->
                    <div class="modal fade" id="modalCancelAppointment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Отменить запись</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Вы собираетесь отменить запись:

                                    <table class="table-bordered table table-sm">
                                        <tr style="display: none;">
                                            <td>ID</td>
                                            <td><span class="js_appointment-id"></span></td>
                                        </tr>
                                        <tr>
                                            <td>Дата</td>
                                            <td><span class="js_appointment-date"></span></td>
                                        </tr>
                                        <tr>
                                            <td>Время</td>
                                            <td><span class="js_appointment-time"></span></td>
                                        </tr>
                                        <tr>
                                            <td>Рабочее место</td>
                                            <td><span class="js_appointment-place"></span></td>
                                        </tr>
                                    </table>

                                    <div class="form-group">
                                        <label for="">Укажите, пожалуйста, причину отмены:</label>
                                        <textarea class="form-control js_appointment-cancel-reason"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                    <button id="sendCancelAppointmentData" type="button" class="btn btn-danger">Да, отменить</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        $(document).ready(function() {
                            // Обработчик нажатия на кнопку отмены записи
                            $('#sendCancelAppointmentData').on('click', function() {
                                const button = $(this);
                                const modal = $('#modalCancelAppointment');
                                const appointmentId = modal.find('.js_appointment-id').text();
                                const reason = modal.find('.js_appointment-cancel-reason').val();

                                button.prop('disabled', true);
                                button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Отмена...');

                                $.ajax({
                                    url: '/user/appointments/' + appointmentId + '/cancel',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        cancellation_reason: reason
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            window.location.reload();
                                        } else {
                                            alert(response.message || 'Произошла ошибка при отмене записи');
                                            button.prop('disabled', false);
                                            button.html('Да, отменить');
                                        }
                                    },
                                    error: function(xhr) {
                                        alert(xhr.responseJSON?.message || 'Произошла ошибка при отмене записи');
                                        button.prop('disabled', false);
                                        button.html('Да, отменить');
                                    }
                                });
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>

    @php
        $masterEndedAppointments = auth()->user()->appointments()->where('price', '>', 0)->whereNull('canceled_at')->where('start_at', '<', now())->where('start_at', '>=', '2025-01-01 00:00')->where('start_at', '>=', now()->subDays(30))->get();
    @endphp

    @if(count($masterEndedAppointments) > 0)
        <div class="row mb-3 mt-3">
            <div class="col-12">
                <a data-bs-toggle="collapse" href="#collapseDocs" role="button">
                    Акты
                </a>
                <div class="collapse" id="collapseDocs">
                    <div class="card card-body overflow-scroll">

                        <table class="table table-sm table-bordered table-responsive mb-0">
                            <tr>
                                <th class="bg-secondary text-white" colspan="2" >Дата и время</th>
                                <th class="bg-secondary text-white">Рабочее место</th>
                                <th class="bg-secondary text-white">Сумма</th>
                                <th class="bg-secondary text-white"></th>
                            </tr>
                            @foreach($masterEndedAppointments as $nextAppointment)
                                <tr class="appointment-info">

                                    <td class="text-nowrap" style="width: 1%;">
                                        {{ $nextAppointment->start_at->format('d.m.Y') }}
                                    </td>

                                    <td class="text-nowrap js_app_{{ $nextAppointment->id }}" style="display: none;">
                                        ID: <span class="js_appointment-id">{{ $nextAppointment->id }}</span>
                                    </td>

                                    <td class="text-nowrap js_app_{{ $nextAppointment->id }}" style="display: none;">
                                        <span class="js_appointment-date">{{ $nextAppointment->start_at->isoFormat('D MMM') }}</span>
                                    </td>

                                    <td class="text-nowrap js_app_{{ $nextAppointment->id }}" style="width: 1%;">

                                        <span class="text-nowrap js_appointment-time">
                                            {{ $nextAppointment->start_at->format('H:i') }} - {{ $nextAppointment->end_at->format('H:i') }}
                                        </span>

                                    </td>

                                    <td class="text-nowrap js_app_{{ $nextAppointment->id }}">
                                        <span class="js_appointment-place">{{ $nextAppointment->place->name }}</span>
                                    </td>

                                    <td class="text-nowrap text-end">
                                        {{ number_format((new \App\Services\AppointmentService())->calculateAppointmentCost($nextAppointment), 2) }} BYN
                                    </td>

                                    <td>
                                        <a target="_blank" href="/user/documents/{{ $nextAppointment->id }}?download">Скачать</a>
                                    </td>

                                </tr>

                            @endforeach
                        </table>

                    </div>
                </div>
            </div>
        </div>
    @endif
@endif


{{-- Storage Cell --}}
@if(auth()->user())

    @php
        $bookings = \App\Models\StorageBooking::whereNull('finished_at')->where('user_id', auth()->id())->get();
    @endphp

    @if(count($bookings))
        <div class="row mb-3 mt-3">
            <div class="col">
                <a data-bs-toggle="collapse" href="#collapseStorageCells" role="button">Локер</a>

                @if($bookings->first()->daysLeft() < 0)
                    <span class="bg-danger text-white p-1 px-2"> <b>ПРОСРОЧЕНО: {{ abs($bookings->first()->daysLeft()) }} {{ trans_choice('день|дня|дней', $bookings->first()->daysLeft()) }}</b></span>
                @elseif($bookings->first()->daysLeft() <= 3)
                    <span class="bg-warning text-white p-1 px-2"> <b>ОСТАЛОСЬ: {{ abs($bookings->first()->daysLeft()) }} {{ trans_choice('день|дня|дней', $bookings->first()->daysLeft()) }}</b></span>
                @endif

                <div class="collapse" id="collapseStorageCells">
                    <div class="card card-body">

                        @foreach($bookings as $booking)
                            @if($loop->index > 0)
                                <div class="mt-4"></div>
                            @endif

                            <table class="table table-bordered table-sm mb-0">
                                <tr>
                                    <td>Номер ячейки</td>
                                    <td>{{ $booking->cell->number }}</td>
                                </tr>

                                <tr>
                                    <td style="width: 1%; white-space: nowrap;">Статус</td>
                                    <td>
                                        @if($bookings->first()->daysLeft() <= 0)
                                            <span style="color: red;">Требуется оплата</span>
                                        @else
                                            <span style="color: green;">Оплачено</span>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td style="width: 1%; white-space: nowrap;">Дата начала</td>
                                    <td>{{ $booking->start_at->format('d-m-Y') }}</td>
                                </tr>

                                <tr>
                                    <td style="width: 1%; white-space: nowrap;">Дата окончания</td>
                                    <td>
                                        {{ $booking->start_at->addDays($booking->duration)->format('d-m-Y') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="width: 1%; white-space: nowrap;">Осталось</td>
                                    <td>
                                        @php
                                            $leftLockerDays = now()->diffInDays($booking->start_at->addDays($booking->duration), false);
                                        @endphp

                                        {{--                                            @if($leftLockerDays > 0)--}}
                                        {{ $leftLockerDays }} {{ trans_choice('день|дня|дней', $leftLockerDays) }}
                                        {{--                                            @else--}}
                                        {{--                                                0 дней--}}
                                        {{--                                            @endif--}}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="width: 1%; white-space: nowrap;">Стоимость продления</td>
                                    <td>
                                        {{ $bookings->first()->cell->cost_per_month }} BYN / 30 дней
                                    </td>
                                </tr>

                                @if(isset($bookings->first()->cell->secret))
                                    <tr>
                                        <td style="width: 1%; white-space: nowrap;">Код</td>
                                        <td>
                                            {{ $bookings->first()->cell->secret }}
                                        </td>
                                    </tr>
                                @endif


                            </table>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif


{{-- Settings --}}
@if(auth()->user() && auth()->user()->hasRole('master'))
    <div class="row mb-3 mt-3">
        <div class="col">

            <a data-bs-toggle="collapse" href="#collapseSettings" role="button">
                Настройки
            </a>

            <div class="collapse" id="collapseSettings">
                <div class="card card-body">

                    <form action="{{ route('user.update-settings') }}" method="POST">
                        @csrf
                        <h3>Показывать рабочие места:</h3>
                        @foreach(\App\Models\Place::where('is_hidden', false)->orderBy('sort')->get() as $workspace)
                            <div>
                                <input id="workspace_{{ $workspace->id }}" type="checkbox" name="workspace_visibility[]" value="{{ $workspace->id }}"
                                    {{ in_array($workspace->id, auth()->user()->getSetting('workspace_visibility', [])) ? 'checked' : '' }}>
                                <label for="workspace_{{ $workspace->id }}">{{ $workspace->name }}</label>
                            </div>
                        @endforeach
                        <button id="saveSettings" type="submit">Сохранить</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endif
