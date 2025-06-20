@extends('public.layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
        .time-slot.selecting {
            background-color: rgba(40, 167, 69, 0.5);
            color: white;
        }
        .time-slot.deselecting {
            background-color: rgba(220, 53, 69, 0.5);
            color: white;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        .select2-container--bootstrap-5 .select2-selection {
            width: 100%;
            min-height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            padding-right: 2.25rem;
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            font-size: 0.9rem;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
            /*font-size: 0.9em;*/
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border-color: #dee2e6;
            border-radius: 0.375rem;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .select2-container--bootstrap-5 .select2-results__options {
            max-height: 200px;
            overflow-y: auto;
            padding: 0.5rem 0;
            color: #212529;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            padding: 0.375rem 0.75rem;
            font-size: 1em !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #0d6efd;
            color: #fff;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .select2-container--bootstrap-5.select2-container--disabled .select2-selection,
        .select2-container--bootstrap-5 .select2-results__option[aria-disabled=true] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
            position: absolute;
            right: 2.5rem;
            width: 0.75rem;
            height: 0.75rem;
            padding: 0.25em 0.25em;
            cursor: pointer;
            opacity: 0.4;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear:hover {
            opacity: 0.6;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function initSelect2Master() {
            if ($('#appointmentMaster').data('select2')) {
                $('#appointmentMaster').select2('destroy');
            }
            $('#appointmentMaster').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Выберите мастера',
                allowClear: true,
                dropdownParent: $('#exampleModal'),
                language: {
                    inputTooShort: function() {
                        return 'Пожалуйста, введите 2 или более символов';
                    },
                    noResults: function() {
                        return 'Мастеров не найдено';
                    },
                    searching: function() {
                        return 'Поиск...';
                    }
                },
                minimumInputLength: 2
            });
        }
        $(document).ready(function() {
            // Инициализация при открытии модального окна
            $('#exampleModal').on('shown.bs.modal', function () {
                initSelect2Master();
            });
        });
    </script>

    <script>
        const dateCarousel = document.getElementById('dateCarousel');
        const selectedDateInput = document.getElementById('selectedDate');

        const monthShort = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
            'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

        const today = new Date();
        const todayUTC = new Date(Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()));

        const maxDate = new Date(todayUTC);
        maxDate.setUTCMonth(maxDate.getUTCMonth() + 3);

        // Берём дату из параметра URL (если есть), YYYY-MM-DD парсится как UTC
        const urlParams = new URLSearchParams(window.location.search);
        const selectedDateFromURL = urlParams.get('date');
        let selectedDate = selectedDateFromURL ? new Date(selectedDateFromURL) : todayUTC;

        let currentDate = new Date(todayUTC);

        while (currentDate <= maxDate) {
            const dayNumber = currentDate.getUTCDate();
            const dayOfWeek = currentDate.toLocaleDateString('ru-RU', { weekday: 'short', timeZone: 'UTC' });
            const monthName = monthShort[currentDate.getUTCMonth()];
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
            const day = currentDate.getUTCDay(); // 0 = вс, 6 = сб
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
            if (currentDate.getTime() === selectedDate.getTime()) {
                card.classList.add('bg-selected', 'text-white');
                selectedDateInput.value = dateValue;
                card.id = 'activeCard'; // добавляем id для scroll
            }

            dateCarousel.appendChild(card);
            currentDate.setUTCDate(currentDate.getUTCDate() + 1);
        }

        // Скроллим активную карточку в центр области видимости
        const activeCard = document.getElementById('activeCard');
        if (activeCard) {
            activeCard.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
        }
    </script>
@endpush

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

    <form class="mb-2" id="dateForm" action="">
        <div class="d-flex overflow-auto" id="dateCarousel"></div>
        <input type="hidden" name="date" id="selectedDate">
    </form>

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
                        <form id="addAppointmentForm" action="{{ route('user.appointments.store') }}" method="POST">
                            @csrf

                            <input type="hidden" name="datetime" value="">
                            <input type="hidden" name="place_id" value="">

                            <div class="mb-3">
                                <label for="appointmentDate" class="form-label">Дата</label>
                                <input type="text" class="form-control" id="appointmentDate" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="appointmentTime" class="form-label">Время</label>
                                <input type="text" class="form-control" id="appointmentTime" disabled>
                            </div>

                            @role('admin')
                            <div class="mb-3">
                                <label for="appointmentMaster" class="form-label">Мастер</label>
                                <select class="form-select" id="appointmentMaster" name="user_id" required>
                                    <option value="">Выберите мастера</option>
                                    @foreach(\App\Models\User::role('master')->with('master.person')->orderBy('name')->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->master->person->full_name }} ({{ $user->master->description }})</option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole

                            <div class="mb-3">
                                <label for="appointmentDuration" class="form-label">Продолжительность</label>
                                <select class="form-control" id="appointmentDuration" name="duration" required>
                                    <option value="">Выберите продолжительность</option>
                                </select>
                                <select id="appointmentDurationOptions" style="display: none;">
                                    @php
                                        $minDuration = (new \App\Services\AppointmentService())->getMinDuration();
                                    @endphp
                                    @for($i = $minDuration; $i <= 960; $i += 30)
                                        <option value="{{ $i }}">{{ str_pad(floor($i/60), 2, '0', STR_PAD_LEFT) }}:{{ str_pad($i%60, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="appointmentComment" class="form-label">Комментарий</label>
                                <textarea class="form-control" id="appointmentComment" name="comment" rows="3"></textarea>
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
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof jQuery === 'undefined') {
                    console.error('jQuery is not loaded');
                    return;
                }

                $(document).ready(function () {
                    // Обработка отмены записи
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

                    // Обработка добавления записи
                    $('.js-add-app').on('click', function () {
                        try {
                            let selectedHour = $(this).parent('.hour');

                            // Сброс выбора мастера для администратора
                            if ($('#appointmentMaster').length) {
                                $('#appointmentMaster').val(null).trigger('change');
                            }

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
                        } catch (error) {
                            console.error('Error in js-add-app click handler:', error);
                        }
                    });

                    // Обработка кнопки добавления
                    $('#addAppointmentButton').on('click', function () {
                        try {
                            $(this).prop('disabled', true);
                            let form = $('#addAppointmentForm');

                            if (checkRequiredFields(form)) {
                                showLoading();
                                form.submit();
                            } else {
                                $(this).prop('disabled', false);
                            }
                        } catch (error) {
                            console.error('Error in addAppointmentButton click handler:', error);
                            $(this).prop('disabled', false);
                        }
                    });

                    function checkRequiredFields(form) {
                        try {
                            let isValid = true;
                            form.find('[required]').each(function() {
                                if (!$(this).val()) {
                                    isValid = false;
                                    $(this).addClass('is-invalid');
                                    if (!$(this).next('.invalid-feedback').length) {
                                        $(this).after('<div class="invalid-feedback">Это поле обязательно для заполнения</div>');
                                    }
                                } else {
                                    $(this).removeClass('is-invalid');
                                    $(this).next('.invalid-feedback').remove();
                                }
                            });

                            return isValid;
                        } catch (error) {
                            console.error('Error in checkRequiredFields:', error);
                            return false;
                        }
                    }

                    function showLoading() {
                        $('#addAppointmentButton').prop('disabled', true);
                        $('#addAppointmentButton').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Сохранение...');
                    }

                    // Добавляем обработку ошибок с сервера
                    @if($errors->any())
                        let errorMessages = [];
                        @foreach($errors->all() as $error)
                            errorMessages.push("{{ $error }}");
                        @endforeach
                        alert('Ошибка:\n' + errorMessages.join('\n'));
                        $('#addAppointmentButton').prop('disabled', false);
                        $('#addAppointmentButton').html('Добавить');
                    @endif
                });
            });
        </script>

    @endif

@endsection
