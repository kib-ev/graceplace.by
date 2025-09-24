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
                window.location.href = '/booking?date=' + dateValue;
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
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    {{-- Select Date --}}
    <div class="row mb-2 mt-2">
        <div class="col-12">
            <form class="me-2" id="dateForm" action="" style="display: inline-block;">
                <input type="date" name="date" value="{{ $date ? $date->format('Y-m-d') : now()->format('Y-m-d') }}" onchange="document.getElementById('dateForm').submit();">
            </form>

            @role('admin')
            <a class="btn btn-outline-primary" href="/admin/appointments?date={{ $date ? $date->format('Y-m-d') : '' }}&place_id={{ request('place_id') }}">
                <img src="https://img.icons8.com/color/22/null/execute-query.png"/>
                Все записи
            </a>

            <a class="btn btn-outline-primary" href="/admin/appointments/create?date={{ $date ? $date->format('Y-m-d') : '' }}&place_id={{ request('place_id') }}">
                <img src="https://img.icons8.com/color/22/null/plus--v1.png"/>
                Добавить
            </a>

            <a class="btn btn-outline-primary" href="/admin/appointments/merge-closest?date={{ $date ? $date->format('Y-m-d') : '' }}">
                <img src="https://img.icons8.com/fluency/22/null/stitching.png"/>
                Сшить
            </a>
            @endrole

            <div class="overflow-auto mt-2 d-flex" style="white-space: nowrap;" id="dateCarousel"></div>
            <input type="hidden" id="selectedDate" name="selected_date">
        </div>
    </div>

    {{-- Select Place --}}
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs">
                @foreach(\App\Models\Place::where('is_hidden', false)->get() as $place)
                    <li class="nav-item">
                        <a class="nav-link {{ (request('place_id') == $place->id || (is_null(request('place_id')) && $loop->first)) ? 'active' : '' }}" href="?date={{ $date->format('Y-m-d') }}&place_id={{ $place->id }}">
                            {{ $place->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @php
        $selectedPlaceId = request('place_id') ?? \App\Models\Place::where('is_hidden', false)->first()->id;
        $selectedPlace = \App\Models\Place::find($selectedPlaceId);
    @endphp

    <div class="row mt-2">
        <div class="col-12">
            {{-- Time Slots --}}
            <div class="row" id="timeSlotsContainer">
                @php
                    $calendar = new \App\Services\AppointmentService();
                    $calendar->loadAppointmentsByPlaceId($selectedPlace->id, \Illuminate\Support\Carbon::parse($date));
                @endphp

                @for($i = 30; $i <= 16*60+30; $i+= \App\Services\AppointmentService::$defaultTimeStep)
                    @php
                        $nextTime = \Carbon\Carbon::parse($date)->startOfDay()->addMinutes(6*60+30)->addMinutes($i);
                        $appointment = $calendar->getAppointment($nextTime);
                    @endphp
                    <div class="col-2 col-md-1 mb-1 p-0 m-0">
                        <div
                            class="time-slot p-1 text-center border
                                {{ !$calendar->isTimeFree($nextTime) ? 'bg-danger' : 'bg-light' }}
                                {{ !$calendar->isTimeFree($nextTime) ? 'text-white' : '' }}"
                            data-time="{{ $nextTime->format('H:i') }}"
                            style="cursor: {{ !$calendar->isTimeFree($nextTime) ? 'not-allowed' : 'pointer' }}; font-size: 0.8em"
                        >
                            {{ $nextTime->format('H:i') }}
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Booking Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="appointmentForm" action="{{ route('public.booking.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="place_id" value="{{ $selectedPlace->id }}">
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                    <input type="hidden" name="start_time" id="appointmentStartTime">
                    <input type="hidden" name="end_time" id="appointmentEndTime">
                    <input type="hidden" name="duration" id="appointmentDuration">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Новая запись</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="appointmentInfo" class="form-label">Вы выбрали</label>
                            <input type="text" class="form-control" id="appointmentInfo" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="appointmentMaster" class="form-label">Мастер</label>
                            <select class="form-control" id="appointmentMaster" name="master_id" required>
                                <option value="" disabled selected>Выберите мастера</option>
                            </select>
                            <div class="invalid-feedback" id="masterError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="appointmentPrice" class="form-label">Цена</label>
                            <input type="number" class="form-control" id="appointmentPrice" name="price" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Записаться</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let selectedSlots = [];
            let isSelecting = false;

            $('.time-slot:not(.bg-danger)').on('mousedown', function(e) {
                e.preventDefault();
                isSelecting = true;
                selectedSlots = [$(this)];
                $(this).toggleClass('selecting');
                updateModal();
            }).on('mouseover', function() {
                if (isSelecting) {
                    const currentTime = $(this).data('time');
                    const firstTime = selectedSlots[0].data('time');

                    // Снимаем выделение со всех
                    $('.time-slot').removeClass('selecting');

                    // Находим все слоты между первым и текущим
                    const allSlots = $('.time-slot:not(.bg-danger)');
                    const firstIndex = allSlots.index($(`.time-slot[data-time="${firstTime}"]`));
                    const currentIndex = allSlots.index($(this));

                    const startIndex = Math.min(firstIndex, currentIndex);
                    const endIndex = Math.max(firstIndex, currentIndex);

                    selectedSlots = [];
                    for (let i = startIndex; i <= endIndex; i++) {
                        const slot = $(allSlots.get(i));
                        if (slot.hasClass('bg-danger')) {
                            // Если на пути есть занятый слот, прекращаем выделение
                            isSelecting = false;
                            $('.time-slot').removeClass('selecting');
                            selectedSlots = [];
                            alert('Нельзя выбрать промежуток, в котором есть занятое время.');
                            return;
                        }
                        slot.addClass('selecting');
                        selectedSlots.push(slot);
                    }
                }
                updateModal();
            });

            $(document).on('mouseup', function() {
                if(isSelecting) {
                    isSelecting = false;
                    if (selectedSlots.length > 0) {
                        $('#exampleModal').modal('show');
                    }
                }
            });

            function updateModal() {
                if (selectedSlots.length > 0) {
                    const placePrice = {{ $selectedPlace->price_per_hour }};
                    const firstSlot = selectedSlots[0];
                    const lastSlot = selectedSlots[selectedSlots.length - 1];

                    const startTime = firstSlot.data('time');
                    const endTimeParts = lastSlot.data('time').split(':');
                    const endHour = parseInt(endTimeParts[0]) + 1;
                    const endTime = (endHour < 10 ? '0' + endHour : endHour) + ':' + endTimeParts[1];

                    const duration = selectedSlots.length * 60; // продолжительность в минутах
                    const price = (duration / 60) * placePrice;

                    $('#appointmentInfo').val(`Место: {{ $selectedPlace->name }} | Дата: {{ $date->format('d.m.Y') }} | Время: ${startTime} - ${endTime}`);
                    $('#appointmentStartTime').val(startTime);
                    $('#appointmentEndTime').val(endTime);
                    $('#appointmentDuration').val(duration);
                    $('#appointmentPrice').val(price);
                }
            }

            $('#appointmentForm').submit(function(e) {
                e.preventDefault();

                // Сброс ошибок
                $('#masterError').text('');
                $('#appointmentMaster').removeClass('is-invalid');

                const formData = $(this).serialize();

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        window.location.href = response.redirect_url;
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.master_id) {
                                $('#masterError').text(errors.master_id[0]);
                                $('#appointmentMaster').addClass('is-invalid');
                            }
                        } else if(xhr.status === 401) {
                            window.location.href = '/login';
                        } else {
                            alert('Произошла ошибка. Пожалуйста, попробуйте еще раз.');
                        }
                    }
                });
            });

            $('#exampleModal').on('show.bs.modal', function () {
                $('#appointmentMaster').val(null).trigger('change');
            });
        });
    </script>
@endpush
