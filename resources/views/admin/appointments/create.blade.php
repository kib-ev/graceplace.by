@extends('app')


@section('content')
    <div class="row">
        <div class="col-6 offset-3">
            <h1>Add Appointment</h1>

            @if($appointment->canceled_at)
                <h2 class="bg-danger text-white p-2">ОТМЕНА</h2>
            @endif

            <hr>

            <form action="{{ isset($appointment) ? route('admin.appointments.update', $appointment) : route('admin.appointments.store') }}" method="post" autocomplete="off">
                @csrf
                @method(isset($appointment) ? 'patch' : 'post')


                <div class="form-group">
                    <label for="masterId">Master</label>
                    <select id="masterId" name="master_id" class="form-control">
                        <option value=""></option>
                        @foreach(\App\Models\Master::all()->sortBy('person.first_name') as $master)
                            <option value="{{ $master->id }}" @selected($master->id == (isset($appointment) ? $appointment->master_id : request('master_id')))>
                                {{ $master->full_name }} | {{ $master->description }} | {{ $master->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="placeId">Place</label>
                    <select id="placeId" name="place_id" class="form-control">
                        <option value=""></option>
                        @foreach(\App\Models\Place::all()->sortBy('name') as $place)
                            <option value="{{ $place->id }}" @selected($place->id == (isset($appointment) ? $appointment->place_id : request('place_id')))>
                                {{ $place->name }} | {{ $place->description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="datetime">Date</label>
                    <input id="datetime" type="datetime-local" class="form-control" name="date" value="{{ isset($appointment) ? $appointment->date : (request('date') ? \Carbon\Carbon::parse(request('date'))->format('Y-m-d 09:00') : now()->addDay()->floorHour(1)->format('Y-m-d H:i')) }}">
                </div>

                <div class="form-group">
                    <label for="duration">Продолжительность (ч.)</label>
                    <select id="duration" name="duration" class="form-control">
                        <option value=""></option>

{{--                        @for($step = 30, $time = now()->startOfDay()->addMinutes($step); $time->lessThan(now()->startOfDay()->addMinutes(21*$step)); $newT$time->addMinutes($step))--}}
{{--                            <option value="{{  }}" >{{ $time->format('H:i') }}</option>--}}
{{--                        @endfor--}}

                        @for($i = 30; $i <= 22*30; $i+=30)
                            <option value="{{ $i }}" @selected(isset($appointment) ? $appointment->duration == $i : '')>
                                {{ now()->startOfDay()->addMinutes($i)->format('H:i') }}
                            </option>
                        @endfor

{{--                        <option value="30" @selected(isset($appointment) ? $appointment->duration == 30 : '')>0:30</option>--}}
{{--                        <option value="60" @selected(isset($appointment) ? $appointment->duration == 60 : '')>1:00</option>--}}
{{--                        <option value="90" @selected(isset($appointment) ? $appointment->duration == 90 : '')>1:30</option>--}}
{{--                        <option value="120" @selected(isset($appointment) ? $appointment->duration == 120 : '')>2:00</option>--}}
{{--                        <option value="150" @selected(isset($appointment) ? $appointment->duration == 150 : '')>2:30</option>--}}
{{--                        <option value="180" @selected(isset($appointment) ? $appointment->duration == 180 : '')>3:00</option>--}}
{{--                        <option value="210" @selected(isset($appointment) ? $appointment->duration == 210 : '')>3:30</option>--}}
{{--                        <option value="240" @selected(isset($appointment) ? $appointment->duration == 240 : '')>4:00</option>--}}
{{--                        <option value="270" @selected(isset($appointment) ? $appointment->duration == 270 : '')>4:30</option>--}}
{{--                        <option value="300" @selected(isset($appointment) ? $appointment->duration == 300 : '')>5:00</option>--}}
{{--                        <option value="330" @selected(isset($appointment) ? $appointment->duration == 330 : '')>5:30</option>--}}
{{--                        <option value="360" @selected(isset($appointment) ? $appointment->duration == 360 : '')>6:00</option>--}}
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description">{{ isset($appointment) ? $appointment->description : '' }}</textarea>
                </div>

                <hr>

                <div class="form-group">
                    <label for="price">Стоимость</label>
                    <input id="price" type="number" step="0.01" min="0" class="form-control" name="price" value="{{ isset($appointment) ? $appointment->price : '' }}">
                </div>

{{--                <div class="form-group">--}}
{{--                    <label for="postTitle">Текст</label>--}}

{{--                    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.0/classic/ckeditor.js"></script>--}}

{{--                    <textarea id="editor" class="form-control" name="content" autocomplete="off">{{ $post->id ? $post->content : '' }}</textarea>--}}

{{--                    <script>--}}
{{--                        ClassicEditor--}}
{{--                            .create( document.querySelector( '#editor' ) )--}}
{{--                            .then( editor => {--}}
{{--                                console.log( editor );--}}
{{--                            } )--}}
{{--                            .catch( error => {--}}
{{--                                console.error( error );--}}
{{--                            } );--}}
{{--                    </script>--}}

{{--                </div>--}}

                <hr>

                <div class="form-group">
                    @if(isset($appointment))
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    @else
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    @endif
                </div>

            </form>


            @if(isset($appointment) && is_null($appointment->price))
                <form action="{{ route('admin.appointments.update', $appointment) }}" method="post" style="float: right;">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="cancel" value="1">
                    <button class="btn btn-danger" type="submit">Отменить</button>
                </form>
            @endif


            @if(isset($appointment) && is_null($appointment->price))
                <form action="{{ route('admin.appointments.destroy', $appointment) }}" method="post" style="float: right;">
                    @csrf
                    @method('delete')
                    <button type="submit" disabled>Удалить</button>
                </form>
            @endif
        </div>
    </div>
@endsection
