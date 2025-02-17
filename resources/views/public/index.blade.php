@extends('public.layouts.app')


@section('content')

    {{-- Select Date --}}
    <div class="row mb-3 mt-3">
        <div class="col">
            <form class="me-2" id="dateForm" action="" style="display: inline-block;">
                <input type="date" name="date" value="{{ request('date') ?? $date ?? '' }}" onchange="document.getElementById('dateForm').submit();">
            </form>
        </div>
    </div>


    {{-- Calendar --}}
    @if(isset($date) && (\Carbon\Carbon::parse($date)->greaterThan(now()->startOfDay()->subDays(3)) || is_admin()))
        <div class="row mb-3" style="overflow-x: scroll;">
            <div class="col">
                <div id="places" class="overflow-scroll">
                    @foreach(\App\Models\Place::where('is_hidden', false)->when(auth()->user(), function ($query) {
                                $visibleWorkspaces = auth()->user()->getSetting('workspace_visibility', []);
                                $query->whereIn('id', $visibleWorkspaces);
                        })->orderBy('sort')->get() as $place)

                        <div class="place">
                            <div class="image">
                                <img style="width: 100%;" src="{{ $place->image_path ?? 'https://placehold.co/200x125?text=фотограф+\nуже+в+пути' }}">
                            </div>

                            <div class="title" style="height: 60px; text-align: center;">
                                {{ $place->name }}
                            </div>

                            <div class="title" style="height: 30px; text-align: center; background: #37c35b; color: #fff;">
                                {{ $place->price_hour }} руб. / час
                            </div>

                            <div class="title" style="height: 30px; text-align: center; background: #37c35b; color: #fff;">
                                {{ $place->price_hour * 8 }} руб. / день
                            </div>

                            <div
                                class="time {{ auth()->user() && $place->isFullDayBusy(\Carbon\Carbon::parse($date)) && $place->isFullDayBusy(\Carbon\Carbon::parse($date))->user_id == auth()->id() ? 'master' : '' }}">

                                @php
                                    $calendar = new \App\Services\AppointmentService();
                                    $calendar->loadAppointmentsByPlaceId($place->id , \Illuminate\Support\Carbon::parse($date));
                                @endphp

                                @for($i = 30; $i <= 16*60+30; $i+= \App\Services\AppointmentService::$defaultTimeStep)
                                    @php
                                        $nextTime = \Carbon\Carbon::parse($date)->startOfDay()->addMinutes(6*60+30)->addMinutes($i);
                                        $appointment = $calendar->getAppointment($nextTime);
                                    @endphp

                                    <div
                                        class="hour {{ $calendar->isTimeFree($nextTime) ? 'free' : 'busy' }} {{ $calendar->isTimeBreak($nextTime) ? 'break' : '' }} {{ auth()->user() && $appointment && $appointment->user_id == auth()->id() ? 'master' : '' }}"
                                        data-date="{{ $nextTime->format('Y-m-d') }}"
                                        data-time="{{ $nextTime->format('H:i') }}"
                                        data-datetime="{{ $nextTime->format('Y-m-d H:i') }}"
                                        data-max_duration="{{ $calendar->getMinutesToNextAppointment($nextTime) ?: '' }}"
                                        data-place_id="{{ $place->id }}">


                                        @if(isset($appointment) && auth()->user() && auth()->user()->hasRole(['admin']))
                                            <a href="{{ route('admin.appointments.edit', $appointment) }}"
                                               title="{{ \Carbon\CarbonInterval::minutes($appointment->duration)->forHumans() }}">
                                                <span>{{ $nextTime->format('H:i') }} - {{ $nextTime->clone()->addMinutes(30)->format('H:i') }}</span>
                                            </a>
                                        @else
                                            <span>{{ $nextTime->format('H:i') }} - {{ $nextTime->clone()->addMinutes(30)->format('H:i') }}</span>
                                        @endif

                                        @if(!$calendar->isTimeFree($nextTime))
                                            @if($appointment && auth()->user())
                                                <span class="js-edit-app info" style="text-overflow: ellipsis; overflow: hidden; margin-left: 5px;">

                                                    @if(auth()->user()->hasRole(['admin']) && $appointment->user->master)
                                                        <a href="{{ route('admin.masters.show', $appointment->user->master) }}" title="{{ $appointment->user->master->full_name }}">
                                                            {{ $appointment->user->first_name }}
                                                        </a>
                                                    @else
                                                        {{ $appointment->user->first_name }}
                                                    @endif

                                                </span>
                                            @else
                                                <span class="info">Занято</span>
                                            @endif
                                        @elseif(auth()->id())
                                            @if($calendar->getMinutesToNextAppointment($nextTime) != 30)
                                                <span class="add-app js-add-app">+</span>
                                            @endif
                                        @endif
                                    </div>

                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col">
            Запись производится через директ: <a href="https://ig.me/m/beautycoworkingminsk">Перейти в директ</a>
        </div>
    </div>

    <div class="row">
        <div class="col">
             Оставляя запись на сайте вы соглашаетесь с условиями <a href="/public-offer">Публичной оферты</a>.
        </div>
    </div>

    <!-- Button to trigger modal -->
    {{--    <button type="button" class="btn btn-primary" id="modalBtn">--}}
    {{--        Launch Modal (Double Click)--}}
    {{--    </button>--}}

    @if(auth()->id())
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Добавить запись</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <form id="addAppointmentForm" action="{{ route('public.appointments.store') }}" method="post" autocomplete="off">
                            @csrf
                            @method('post')

                            <input type="hidden" name="datetime" value="">
                            <input type="hidden" name="place_id" value="">

                            <div class="form-group mb-2">
                                <label for="appointmentDate">Дата</label>
                                <input class="form-control" id="appointmentDate" type="text" name="date" value="" disabled>
                            </div>

                            <div class="form-group mb-2">
                                <label for="appointmentTime">Время</label>
                                <input class="form-control" id="appointmentTime" type="text" name="time" value="" disabled>
                            </div>

                            @if(auth()->user() && auth()->user()->hasRole('admin'))
                                <div class="form-group mb-2">
                                    <label for="appointmentUser">Пользователь <span class="text-danger">*</span></label>
                                    <select id="appointmentUser" name="user_id" class="form-control" required>
                                        <option value=""></option>
                                        @foreach(\App\Models\User::role('master')->orderBy('name')->with(['master.person'])->get() as $user)
                                            <option value="{{ $user->id }}" @selected($user->id == (isset($appointment) ? $appointment->user_id : request('user_id')))>
                                                {{ $user->master->full_name }} | {{ $user->master->description }} | {{ $user->master->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="form-group mb-2">
                                <label for="appointmentDuration">Продолжительность (ч) <span class="text-danger">*</span></label>

                                <select id="appointmentDurationOptions" style="display: none;">
                                    <option value=""></option>
                                    @for($i = \App\Services\AppointmentService::$minAppointmentDuration; $i <= 26*30; $i+=\App\Services\AppointmentService::$defaultTimeStep)
                                        <option value="{{ $i }}" @selected(isset($appointment) ? $appointment->duration == $i : '')>
                                            {{ now()->startOfDay()->addMinutes($i)->format('H:i') }}
                                        </option>
                                    @endfor
                                </select>

                                <select id="appointmentDuration" class="form-control" name="duration" id="duration" required>
                                    <option value=""></option>
                                    @for($i = 60; $i <= 22*30; $i+=30)
                                        <option value="{{ $i }}" @selected(isset($appointment) ? $appointment->duration == $i : '')>
                                            {{ now()->startOfDay()->addMinutes($i)->format('H:i') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group mb-2">
                                <label for="appointmentComment">Дополнительная информация</label>
                                <textarea class="form-control" id="appointmentComment" name="comment" placeholder=""></textarea>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button id="addAppointmentButton" type="button" class="btn btn-primary">Добавить</button>
                    </div>
                </div>
            </div>
        </div>


        <script>
            $(document).ready(function () {

                $('.js_cancel-appointment').on('click', function () {

                    let info = $(this).closest('.appointment-info');
                    let modal = $('#modalCancelAppointment');

                    let id = info.find('.js_appointment-id').text();
                    let date = info.find('.js_appointment-date').text();
                    let time = info.find('.js_appointment-time').text();
                    let place = info.find('.js_appointment-place').text();

                    modal.find('.js_appointment-id').text(id);
                    modal.find('.js_appointment-date').text(date);
                    modal.find('.js_appointment-time').text(time);
                    modal.find('.js_appointment-place').text(place);

                    modal.modal('show');
                });

                $('#sendCancelAppointmentData').on('click', function () {
                    let modal = $('#modalCancelAppointment');

                    let appointmentId = $('#modalCancelAppointment').find('.js_appointment-id').text();
                    let cancelReason = $('#modalCancelAppointment').find('.js_appointment-cancel-reason').val();

                    $.ajax({
                        url: '/appointments/' + appointmentId + '/cancel',  // URL для отправки запроса
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',  // CSRF-токен для защиты запроса
                            cancellation_reason: cancelReason
                        },
                        success: function (response) {
                            if (response.success) {
                                alert('Запись успешно отменена.');
                                // Обновляем интерфейс, можно скрыть запись или изменить её статус
                                $('#appointment-' + appointmentId).remove();

                                $('.js_app_' + appointmentId).remove();

                                window.location.reload();

                            } else {
                                alert('Произошла ошибка: ' + response.message);
                            }
                        },
                        error: function (xhr) {
                            alert('Произошла ошибка. Попробуйте снова.');
                        }
                    });

                    modal.modal('hide');
                })

                $('.time').each(function (el) {
                    $(this).on('dblclick', function () {
                        console.log(1);
                    });
                });

                $('.js-add-app').each(function (el) {
                    $(this).on('click', function () {

                        let selectedHour = $(this).parent('.hour');

                        $('#addAppointmentForm #appointmentMaster option:first').prop('selected', true);

                        $('#addAppointmentForm #appointmentDuration option').remove();
                        $('#addAppointmentForm #appointmentDuration').html($('#addAppointmentForm #appointmentDurationOptions').html());

                        let time = selectedHour.attr('data-time');
                        $('#addAppointmentForm #appointmentTime').val(time);

                        let date = selectedHour.attr('data-date');
                        $('#addAppointmentForm #appointmentDate').val(date);

                        let datetime = selectedHour.attr('data-datetime');
                        $('#addAppointmentForm [name="datetime"]').val(datetime);

                        let placeId = selectedHour.attr('data-place_id');
                        $('#addAppointmentForm [name="place_id"]').val(placeId);

                        let maxDuration = selectedHour.attr('data-max_duration');
                        $('#addAppointmentForm #appointmentDuration option').each(function () {
                            if (parseInt(maxDuration) < parseInt($(this).attr('value'))) {
                                $(this).remove();
                            }
                        });

                        $('#exampleModal').modal('show');
                        $('#addAppointmentButton').prop('disabled', false);
                    })
                });


                $('#addAppointmentButton').on('click', function () {
                    $(this).prop('disabled', true);
                    let form = $('#addAppointmentForm');

                    if (checkRequiredFields(form)) {
                        form.submit();
                    }
                });

                function checkRequiredFields(form) {
                    let needToFillCount = 0;

                    form.find('[required]').each(function () {
                        if ($(this).val() == '') {
                            $(this).css('border-color', 'red');
                            needToFillCount += 1;
                        } else {
                            $(this).css('border-color', ' ');
                        }
                    });

                    return needToFillCount == 0;
                }
            });
        </script>

    @endif

@endsection
