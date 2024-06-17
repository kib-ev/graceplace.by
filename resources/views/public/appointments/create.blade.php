@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Add Appointment</h1>

            <hr>

            <form action="{{ isset($appointment) ? route('public.appointments.update', $appointment) : route('public.appointments.store') }}" method="post" autocomplete="off">
                @csrf
                @method(isset($appointment) ? 'patch' : 'post')


                <div class="form-group">
                    <label for="masterId">Master</label>
                    <select id="masterId" name="master_id" class="form-control">
                        <option value=""></option>
                        @foreach(\App\Models\Master::all() as $master)
                            <option value="{{ $master->id }}">{{ $master->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="placeId">Place</label>
                    <select id="placeId" name="place_id" class="form-control">
                        <option value=""></option>
                        @foreach(\App\Models\Place::all() as $place)
                            <option value="{{ $place->id }}">{{ $place->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="datetime">Date</label>
                    <input id="datetime" type="datetime-local" class="form-control" name="date" value="{{ isset($appointment) ? $appointment->date : '' }}">
                </div>

                <div class="form-group">
                    <label for="duration">Duration</label>
                    <select id="duration" name="duration" class="form-control">
                        <option value=""></option>
                        <option value="30">0:30</option>
                        <option value="90">1:30</option>
                        <option value="120">2:00</option>
                        <option value="150">2:30</option>
                        <option value="180">2:00</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description" autocomplete="off">{{ isset($appointment) ? $appointment->description : '' }}</textarea>
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
        </div>
    </div>
@endsection
