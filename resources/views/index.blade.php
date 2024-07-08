@extends('app')


@section('content')

    <div class="row mb-3 mt-3">
        <div class="col">
            <form class="me-2" id="dateForm" action="" style="display: inline-block;">
                <input type="date" name="date" value="{{ request('date') }}"  onchange="document.getElementById('dateForm').submit();">
            </form>

{{--            @if(auth()->id())--}}
{{--                <div class="d-inline-block">{{ auth()->user()->name }}</div>--}}
{{--            @endif--}}
        </div>
    </div>

    @if(isset($date) && \Carbon\Carbon::parse($date)->greaterThan(now()->startOfDay()->subDays(3)))
        <div class="row mb-3" style="overflow-x: scroll;">
            <div class="col">
                <div id="places" class="" style="display: flex; gap: 3px;" class="overflow-scroll">
                    @foreach(\App\Models\Place::all()->sortBy('name') as $place)
                        <div class="place">
                            <div class="image">
                                <img style="width: 100%;" src="{{ $place->image_path }}">
                            </div>

                            <div class="title" style="height: 60px; text-align: center;">
                                {{ $place->name }}
                            </div>

                            <div class="title" style="height: 30px; text-align: center; background: #37c35b; color: #fff;">
{{--                                @if(now()->isSameDay($date))--}}
{{--                                    <s>&nbsp;{{ $place->price_hour }}&nbsp;</s>&nbsp;--}}
{{--                                    {{ $place->price_hour / 2 }} руб. / час--}}
{{--                                @else--}}
{{--                                    {{ $place->price_hour }} руб. / час--}}
{{--                                @endif--}}

                                {{ $place->price_hour }} руб. / час

                            </div>

                            <div class="title" style="height: 30px; text-align: center; background: #37c35b; color: #fff;">
{{--                                @if(now()->isSameDay($date))--}}
{{--                                    <s>&nbsp;{{ $place->price_hour * 8 }}&nbsp;</s>&nbsp;--}}
{{--                                    {{ $place->price_hour * 8 / 2 }} руб. / день--}}
{{--                                @else--}}
{{--                                    {{ $place->price_hour * 8 }} руб. / день--}}
{{--                                @endif--}}

                                {{ $place->price_hour * 8 }} руб. / день

                            </div>

                            <div class="time">
                                @for($i = 30; $i <= 16*60+30; $i+=30)
                                    @php
                                        $nextDate = \Carbon\Carbon::parse($date)->startOfDay()->addMinutes(6*60+30)->addMinutes($i);
                                        $isAppointment = $place->isAppointment($nextDate);

                                        $nextAppointmentToMinutes = $place->nextAppointmentToMinutes($nextDate);
                                    @endphp

                                    <div class="hour {{ $isAppointment ? 'busy' : 'free' }}"
                                         data-date="{{ $nextDate->format('Y-m-d') }}"
                                         data-time="{{ $nextDate->format('H:i') }}"
                                         data-datetime="{{ $nextDate->format('Y-m-d H:i') }}"
                                         data-max_duration="{{ $nextAppointmentToMinutes ?: '' }}"
                                         data-place_id="{{ $place->id }}">

                                        {{ $nextDate->format('H:i') }} - {{ $nextDate->clone()->addMinutes(30)->format('H:i') }}

                                        @if($isAppointment && $isAppointment->master)
                                            @if(auth()->id())
                                                <span class="info">{{ $isAppointment->master->person->first_name }}</span>
                                            @else
                                                <span class="info">Занято</span>
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
                        <h5 class="modal-title" id="exampleModalLabel">Забронировать место</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <form id="addAppointmentForm" action="{{ route('public.appointments.store') }}" method="post" autocomplete="off">
                            @csrf
                            @method('post')

                            <input type="hidden" name="datetime" value="">
                            <input type="hidden" name="place_id" value="">

                            <div class="form-group">
                                <label for="appointmentDate">Дата</label>
                                <input class="form-control" id="appointmentDate" type="text" name="time" value="" disabled>
                            </div>

                            <div class="form-group">
                                <label for="appointmentTime">Время</label>
                                <input class="form-control" id="appointmentTime" type="text" name="time" value="" disabled>
                            </div>

                            <div class="form-group">
                                <label for="appointmentMaster">Мастер</label>
                                <select id="appointmentMaster" name="master_id" class="form-control" required>
                                    <option value=""></option>
                                    @foreach(\App\Models\Master::all()->sortBy('person.first_name') as $master)
                                        <option value="{{ $master->id }}" @selected($master->id == (isset($appointment) ? $appointment->master_id : request('master_id')))>
                                            {{ $master->full_name }} | {{ $master->description }} | {{ $master->phone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="form-group">
                                <label for="appointmentDuration">Продолжительность</label>

                                <select id="appointmentDurationOptions" style="display: none;">
                                    <option value=""></option>
                                    @for($i = 60; $i <= 22*30; $i+=30)
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

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button id="addAppointmentButton" type="button" class="btn btn-primary">Записать</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function () {
                $('.time').each(function (el) {
                    $(this).on('dblclick', function () {
                        console.log(1);
                    });
                });

                $('.hour').each(function (el) {
                    $(this).on('dblclick', function () {

                        $('#addAppointmentForm #appointmentMaster option:first').prop('selected', true);

                        $('#addAppointmentForm #appointmentDuration option').remove();
                        $('#addAppointmentForm #appointmentDuration').html($('#addAppointmentForm #appointmentDurationOptions').html());

                        let time = $(this).attr('data-time');
                        $('#addAppointmentForm #appointmentTime').val(time);

                        let date = $(this).attr('data-date');
                        $('#addAppointmentForm #appointmentDate').val(date);

                        let datetime = $(this).attr('data-datetime');
                        $('#addAppointmentForm [name="datetime"]').val(datetime);

                        let placeId = $(this).attr('data-place_id');
                        $('#addAppointmentForm [name="place_id"]').val(placeId);

                        let maxDuration = $(this).attr('data-max_duration');
                        $('#addAppointmentForm #appointmentDuration option').each(function () {
                            if(parseInt(maxDuration) < parseInt($(this).attr('value'))) {
                                $(this).remove();
                            }
                        });

                        $('#exampleModal').modal('show');
                    })
                });


                $('#addAppointmentButton').on('click', function () {
                    $('#addAppointmentForm').submit();
                });

            });
        </script>

    @endif

@endsection
