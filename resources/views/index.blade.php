@extends('app')


@section('content')

    <div class="row mb-3 mt-3">
        <div class="col">
            <form id="dateForm" action="" style="font-size: 20px;">
                <input type="date" name="date" value="{{ request('date') }}"  onchange="document.getElementById('dateForm').submit();">
            </form>
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

                            <div class="time">
                                @for($i = 30; $i <= 16*60+30; $i+=30)
                                    @php
                                        $nextDate = \Carbon\Carbon::parse($date)->startOfDay()->addMinutes(6*60+30)->addMinutes($i);
                                        $isAppointment = $place->isAppointment($nextDate);
                                    @endphp

                                    <div class="hour {{ $isAppointment ? 'busy' : 'free' }}">
                                        {{ $nextDate->format('H:i') }}

                                        @if($isAppointment && $isAppointment->master && auth()->id())
                                            {{ $isAppointment->master->person->first_name }}
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
@endsection
