@extends('public.layouts.app')


@section('content')
    {{-- Select Date --}}
    <div class="row mb-2 mt-2">
        <div class="col-12">
            <form class="me-2" id="dateForm" action="" style="display: inline-block;">
                <input type="date" name="date" value="{{ $date ? $date->format('Y-m-d') : now()->format('Y-m-d') }}" onchange="document.getElementById('dateForm').submit();">
            </form>

            @role('admin')
                <span><a href="{{ route('admin.appointments.index', ['date_from' => $date->format('Y-m-d'), 'date_to' => $date->format('Y-m-d')]) }}"> Записи {{ $date->format('d.m.Y') }}</a></span>
            @endrole
        </div>
    </div>

    <style>
        .bg-default {
            background-color: #E5E7EB;
        }
        .bg-weekend {
            background-color: #d5d6d9;
        }
        .bg-selected {
            background-color: #3F8CFF !important;
            color: white;
        }
    </style>

    <form class="mb-2" id="dateForm" action="">
        <div class="d-flex overflow-auto" id="dateCarousel"></div>
        <input type="hidden" name="date" id="selectedDate">
    </form>

    <script>
        const dateCarousel = document.getElementById('dateCarousel');
        const selectedDateInput = document.getElementById('selectedDate');

        const monthShort = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
            'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

        const today = new Date();
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 3);

        // Берём дату из параметра URL (если есть)
        const urlParams = new URLSearchParams(window.location.search);
        const selectedDateFromURL = urlParams.get('date');
        let selectedDate = selectedDateFromURL ? new Date(selectedDateFromURL) : today;

        let currentDate = new Date(today);

        while (currentDate <= maxDate) {
            const dayNumber = currentDate.getDate();
            const dayOfWeek = currentDate.toLocaleDateString('ru-RU', { weekday: 'short' });
            const monthName = monthShort[currentDate.getMonth()];
            const dateValue = currentDate.toISOString().split('T')[0];

            const card = document.createElement('div');
            card.className = 'card me-1 text-center p-1';
            card.style.minWidth = '50px';
            card.style.lineHeight = '20px';
            card.style.cursor = 'pointer';
            card.innerHTML = `
          <div><strong>${dayNumber}</strong></div>
          <div style="text-transform: uppercase;">${dayOfWeek}</div>
          <div style="text-transform: lowercase;">${monthName}</div>
        `;
            card.dataset.date = dateValue;

            // Выходные
            const day = currentDate.getDay(); // 0 = вс, 6 = сб
            if (day === 0 || day === 6) {
                card.classList.add('bg-weekend');
            } else {
                card.classList.add('bg-default');
            }

            card.addEventListener('click', () => {
                document.querySelectorAll('#dateCarousel .card').forEach(c => c.classList.remove('bg-selected', 'text-white'));
                card.classList.add('bg-selected', 'text-white');
                selectedDateInput.value = dateValue;
                window.location.href = '/?date=' + dateValue;
            });

            // Подсвечиваем выбранную дату
            if (currentDate.toDateString() === selectedDate.toDateString()) {
                card.classList.add('bg-selected', 'text-white');
                selectedDateInput.value = dateValue;
                card.id = 'activeCard'; // добавляем id для scroll
            }

            dateCarousel.appendChild(card);
            currentDate.setDate(currentDate.getDate() + 1);
        }

        // Скроллим активную карточку в центр области видимости
        const activeCard = document.getElementById('activeCard');
        if (activeCard) {
            activeCard.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
        }
    </script>


    {{-- Calendar --}}
    @if(isset($date) && (\Carbon\Carbon::parse($date)->greaterThan(now()->startOfDay()->subDays(3)) || is_admin()))
        <div class="row mb-3" style="overflow-x: scroll;">
            <div class="col-12">
                <div id="places" class="overflow-scroll table-drag-scroll">
                    @foreach(\App\Models\Place::where('is_hidden', false)->when(auth()->user(), function ($query) {
                                $visibleWorkspaces = auth()->user()->getSetting('workspace_visibility', []);
                                $query->whereIn('id', $visibleWorkspaces);
                        })->orderBy('sort')->get() as $place)

                        <div class="place" style="width: 200px;">
                            <div class="image">
                                <img style="width: 100%;" src="{{ $place->image_path ?? 'https://placehold.co/200x125?text=фото' }}">
                            </div>

                            <div class="title" style="height: 60px; text-align: center;">
                                <a class="text-white" href="{{ route('public.places.show', $place) }}">{{ $place->name }}</a>
                            </div>

                            <div class="title" style="height: 30px; text-align: center; background: #37c35b; color: #fff;">
                                {{ $place->price_per_hour }} руб. / час
                            </div>

                            <div class="title" style="height: 30px; text-align: center; background: #37c35b; color: #fff;">
                                {{ $place->price_per_hour * 8 }} руб. / день
                            </div>

                            <div class="time">

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
                                               title="{{ $appointment->start_at->format('H:i') }} - {{ $appointment->end_at->format('H:i') }} ({{ \Carbon\CarbonInterval::minutes($appointment->duration)->forHumans() }})">
                                                <span>{{ $nextTime->format('H:i') }} - {{ $nextTime->clone()->addMinutes(30)->format('H:i') }}</span>
                                            </a>
                                        @else
                                            <span>{{ $nextTime->format('H:i') }} - {{ $nextTime->clone()->addMinutes(30)->format('H:i') }}</span>
                                        @endif

                                        @if(!$calendar->isTimeFree($nextTime))
                                            @if($appointment && auth()->user())
                                                <span class="js-edit-app info" style="text-overflow: ellipsis; overflow: hidden; margin-left: 5px;">
                                                    @if(auth()->user()->hasRole(['admin']) && $appointment->user->master)
                                                        <a href="{{ route('admin.masters.show', $appointment->user->master) }}" title="{{ $appointment->user->master->person->full_name }}">
                                                            {{ $appointment->user?->master?->person?->first_name }}
                                                        </a>
                                                    @else
                                                        {{ $appointment->user?->master?->person?->first_name }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="info">Занято</span>
                                            @endif
                                        @elseif(auth()->id())
                                            @if(auth()->user()->can('add appointment') && $calendar->getMinutesToNextAppointment($nextTime) != 30)
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

    <div class="row mb-2">
        <div class="col-12">
{{--            <p>Запись производится через директ: <a href="https://ig.me/m/beautycoworkingminsk">Перейти в директ</a>.</p>--}}
            <p class="mb-0">Оставляя запись на сайте вы соглашаетесь с условиями <a href="/public-offer">Публичной оферты</a>.</p>
        </div>
    </div>


    <div class="row mb-2">
        <div class="col-12">

            <div class="payment">
                @if(auth()->user() && auth()->user()->getSetting('payment_link.place'))
                    <a class="btn btn-lg btn-primary mb-3" target="_blank" href="{{ auth()->user()->getSetting('payment_link.place') }}" style="width: 300px;">Оплатить Аренду Места</a>

                    <p class="d-inline-flex gap-1">
                        <a class="" data-bs-toggle="collapse" href="#collapsePlace" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Еще...
                        </a>
                    </p>
                    <div class="collapse" id="collapsePlace">
                        <div class="card card-body">
                            <h3>Оплата Аренды Места</h3>
                            <hr>
                            <img src="{{ qr_code(auth()->user()->getSetting('payment_link.place')) }}" style="width: 250px; height: 250px;">

                            <hr>

                            <ul style="list-style-type: decimal; margin-bottom: 0px; padding-left: 25px;">
                                <li>Войти в Мобильный банк или Интернет-банк любого банка</li>
                                <li>Выбрать платежи через ЕРИП</li>
                                <li>В дереве ЕРИП выбрать "Сервис e-pos" (или через поиск по коду услуги <b>4440631</b>)</li>
                                <li>Вести лицевой счет <b>{{ substr(auth()->user()->getSetting('payment_link.place'), 63, 14) }}</b> ( после второго тире буква i )</li>
                                <li>Подтвердить оплату</li>
                            </ul>
                        </div>
                    </div>
                @endif


                <br>

                @php
                    $storageBookings = \App\Models\StorageBooking::whereNull('finished_at')->where('user_id', auth()->id())->get();
                @endphp

                @if(auth()->user() && auth()->user()->getSetting('payment_link.storage') && count($storageBookings))
                    <a class="btn btn-lg btn-primary mb-3" target="_blank" href="{{ auth()->user()->getSetting('payment_link.storage') }}" style="width: 300px;">Оплатить Аренду Локера</a>

                    <p class="d-inline-flex gap-1">
                        <a class="" data-bs-toggle="collapse" href="#collapseStorage" role="button" aria-expanded="false" aria-controls="collapseExample">
                            Еще...
                        </a>
                    </p>
                    <div class="collapse" id="collapseStorage">
                        <div class="card card-body">
                            <h3>Оплата Аренды Локера</h3>
                            <hr>
                            <img src="{{ qr_code(auth()->user()->getSetting('payment_link.storage')) }}" style="width: 250px; height: 250px;">

                            <hr>

                            <ul style="list-style-type: decimal; margin-bottom: 0px; padding-left: 25px;">
                                <li>Войти в Мобильный банк или Интернет-банк любого банка</li>
                                <li>Выбрать платежи через ЕРИП</li>
                                <li>В дереве ЕРИП выбрать "Сервис e-pos" (или через поиск по коду услуги <b>4440631</b>)</li>
                                <li>Вести лицевой счет <b>{{ substr(auth()->user()->getSetting('payment_link.storage'), 63, 14) }}</b> ( после второго тире буква i )</li>
                                <li>Подтвердить оплату</li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <div class="social mt-3">
                <a href="https://www.instagram.com/beautycoworkingminsk/" target="_blank" class="mb-3"><img style="width: 50px;" src="./images/instagram.png" alt="Instagram GracePlace.By Minsk"></a>
                <a target="_blank" href="https://ig.me/m/beautycoworkingminsk">Написать в Direct</a>
            </div>

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

                                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
                                    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
                                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />

                                    <select id="appointmentUser" name="user_id" class="form-control" required>
                                        <option value=""></option>
                                        @foreach(\App\Models\User::role('master')->with(['master.person'])->get()->sortBy('master.person.full_name') as $user)
                                            <option value="{{ $user->id }}" @selected($user->id == (isset($appointment) ? $appointment->user_id : request('user_id')))>
                                                {{ $user->master->person->last_name }} {{ $user->master->person->first_name }} | {{ $user->master->description }} | {{ $user->master->phone }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <script>
                                        $(document).ready(function() {
                                            $('#appointmentUser').selectize({
                                                sortField: 'text'
                                            });
                                        });
                                    </script>
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
