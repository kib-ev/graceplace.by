@extends('app')


@section('content')

    <div class="row mb-3 mt-3">
        <div class="col">
            @if(auth()->user())
                Вы вошли как: <b>{{ auth()->user()->name }}</b> <a href="/logout">Выйти</a>
            @else
                <a href="{{ route('login') }}">Вход на сайт</a>
            @endif
        </div>
    </div>

    <div class="row mb-3 mt-3">
        <div class="col">
            <form class="me-2" id="dateForm" action="" style="display: inline-block;">
                <input type="date" name="date" value="{{ request('date') }}"
                       onchange="document.getElementById('dateForm').submit();">
            </form>
        </div>
    </div>

    @if(auth()->user())
        <div class="row mb-3 mt-3">
            <div class="col">
                @php
                    $currentMaster = \App\Services\AppointmentService::getMasterByUserId(auth()->id());
                @endphp

                @if(isset($currentMaster))
                    @php
                        $masterAppointments = $currentMaster->appointments()->where('date', '>=', now()->startOfDay())->get();
                    @endphp

                    @foreach($masterAppointments->sortBy('date')->groupBy(function ($a) { return $a->date->format('Y/m/d'); }) as $masterDate => $masterAppointmentByDate)
                        @php
                            $masterCarbonDate = \Carbon\Carbon::parse($masterDate);
                        @endphp
                        <a href="/?date={{ $masterCarbonDate->format('Y-m-d') }}"
                           class="m-1">{{ $masterCarbonDate->format('d.m') }} ({{ count($masterAppointmentByDate) }}
                            )</a>
                    @endforeach
                @endif
            </div>
        </div>
    @endif

    @if(isset($date) && (\Carbon\Carbon::parse($date)->greaterThan(now()->startOfDay()->subDays(3)) || is_admin()))
        <div class="row mb-3" style="overflow-x: scroll;">
            <div class="col">
                <div id="places" class="" style="display: flex; gap: 3px; margin-right: 5px;" class="overflow-scroll">
                    @foreach(\App\Models\Place::all()->sortBy('name') as $place)
                        <div class="place">
                            <div class="image">
                                <img style="width: 100%;" src="{{ $place->image_path ?? 'https://placehold.co/200x125' }}">
                            </div>

                            <div class="title" style="height: 60px; text-align: center;">
                                {{ $place->name }}
                            </div>

                            <div class="title"
                                 style="height: 30px; text-align: center; background: #37c35b; color: #fff;">
                                {{--                                @if(now()->isSameDay($date))--}}
                                {{--                                    <s>&nbsp;{{ $place->price_hour }}&nbsp;</s>&nbsp;--}}
                                {{--                                    {{ $place->price_hour / 2 }} руб. / час--}}
                                {{--                                @else--}}
                                {{--                                    {{ $place->price_hour }} руб. / час--}}
                                {{--                                @endif--}}

                                {{ $place->price_hour }} руб. / час

                            </div>

                            <div class="title"
                                 style="height: 30px; text-align: center; background: #37c35b; color: #fff;">

                                {{ $place->price_hour * 8 }} руб. / день

                            </div>

                            <div
                                class="time {{ auth()->user() && $place->isFullDayBusy(\Carbon\Carbon::parse($date)) && is_master($place->isFullDayBusy(\Carbon\Carbon::parse($date))->master_id) ? 'master' : '' }}">

                                @php
                                    $calendar = new \App\Services\AppointmentService();
                                    $calendar->loadAppointmentsByPlaceId($place->id , \Illuminate\Support\Carbon::parse($date));
                                @endphp

{{--                                @if(auth()->id() == 1)--}}
{{--                                    @foreach($calendar->getItems(\Carbon\Carbon::parse($date)) as $item)--}}
{{--                                        <p>{{ $item['status'] }}</p>--}}
{{--                                    @endforeach--}}
{{--                                @endif--}}

                                @for($i = 30; $i <= 16*60+30; $i+= \App\Services\AppointmentService::$defaultTimeStep)
                                    @php
                                        $nextTime = \Carbon\Carbon::parse($date)->startOfDay()->addMinutes(6*60+30)->addMinutes($i);
                                        $appointment = $calendar->getAppointment($nextTime);
                                    @endphp

                                    <div class="hour {{ $calendar->isTimeFree($nextTime) ? 'free' : 'busy' }} {{ $calendar->isTimeBreak($nextTime) ? 'break' : '' }} {{ auth()->user() && $appointment && is_master($appointment->master_id) ? 'master' : '' }}"
                                        data-date="{{ $nextTime->format('Y-m-d') }}"
                                        data-time="{{ $nextTime->format('H:i') }}"
                                        data-datetime="{{ $nextTime->format('Y-m-d H:i') }}"
                                        data-max_duration="{{ $calendar->getMinutesToNextAppointment($nextTime) ?: '' }}"
                                        data-place_id="{{ $place->id }}">


                                        @if(isset($appointment) && auth()->user() && auth()->user()->hasRole(['admin']))
                                            <a href="{{ route('admin.appointments.edit', $appointment) }}" title="{{ \Carbon\CarbonInterval::minutes($appointment->duration)->forHumans() }}">
                                                <span>{{ $nextTime->format('H:i') }} - {{ $nextTime->clone()->addMinutes(30)->format('H:i') }}</span>
                                            </a>
                                        @else
                                            <span>{{ $nextTime->format('H:i') }} - {{ $nextTime->clone()->addMinutes(30)->format('H:i') }}</span>
                                        @endif

                                        @if(!$calendar->isTimeFree($nextTime))
                                            @if($appointment && auth()->user())
                                                <span class="js-edit-app info" style="text-overflow: ellipsis; overflow: hidden; margin-left: 5px;">

                                                    @if(auth()->user()->hasRole(['admin']))
                                                        <a href="{{ route('admin.masters.show', $appointment->master) }}" title="{{ $appointment->master->full_name }}">
                                                            {{ $appointment->master->person->first_name }}
                                                        </a>
                                                    @else
                                                        {{ $appointment->master->person->first_name }}
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

                        <form id="addAppointmentForm" action="{{ route('public.appointments.store') }}" method="post"
                              autocomplete="off">
                            @csrf
                            @method('post')

                            <input type="hidden" name="datetime" value="">
                            <input type="hidden" name="place_id" value="">

                            <div class="form-group mb-2">
                                <label for="appointmentDate">Дата</label>
                                <input class="form-control" id="appointmentDate" type="text" name="time" value=""
                                       disabled>
                            </div>

                            <div class="form-group mb-2">
                                <label for="appointmentTime">Время</label>
                                <input class="form-control" id="appointmentTime" type="text" name="time" value=""
                                       disabled>
                            </div>

                            @if(auth()->user() && auth()->user()->hasRole('admin'))
                                <div class="form-group mb-2">
                                    <label for="appointmentMaster">Мастер <span class="text-danger">*</span></label>
                                    <select id="appointmentMaster" name="master_id" class="form-control" required>
                                        <option value=""></option>
                                        @foreach(\App\Models\Master::all()->sortBy('person.first_name') as $master)
                                            <option
                                                value="{{ $master->id }}" @selected($master->id == (isset($appointment) ? $appointment->master_id : request('master_id')))>
                                                {{ $master->full_name }} | {{ $master->description }}
                                                | {{ $master->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif(auth()->user() && \App\Services\AppointmentService::getMasterByUserId(auth()->id()))
                                <input type="hidden" name="master_id"
                                       value="{{ \App\Services\AppointmentService::getMasterByUserId(auth()->id())?->id }}">
                            @endif


                            <div class="form-group mb-2">
                                <label for="appointmentDuration">Продолжительность <span
                                        class="text-danger">*</span></label>

                                <select id="appointmentDurationOptions" style="display: none;">
                                    <option value=""></option>
                                    @for($i = \App\Services\AppointmentService::$minAppointmentDuration; $i <= 22*30; $i+=\App\Services\AppointmentService::$defaultTimeStep)
                                        <option
                                            value="{{ $i }}" @selected(isset($appointment) ? $appointment->duration == $i : '')>
                                            {{ now()->startOfDay()->addMinutes($i)->format('H:i') }}
                                        </option>
                                    @endfor
                                </select>

                                <select id="appointmentDuration" class="form-control" name="duration" id="duration"
                                        required>
                                    <option value=""></option>
                                    @for($i = 60; $i <= 22*30; $i+=30)
                                        <option
                                            value="{{ $i }}" @selected(isset($appointment) ? $appointment->duration == $i : '')>
                                            {{ now()->startOfDay()->addMinutes($i)->format('H:i') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group mb-2">
                                <label for="appointmentComment">Дополнительная информация</label>
                                <textarea class="form-control" id="appointmentComment" name="comment"
                                          placeholder=""></textarea>
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
                    })
                });


                $('#addAppointmentButton').on('click', function () {
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
