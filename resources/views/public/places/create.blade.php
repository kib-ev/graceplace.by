@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Add Place</h1>
            <hr>
            <form action="{{ isset($place) ? route('public.places.update', $place) : route('public.places.store') }}" method="post">
                @csrf
                @method(isset($place) ? 'patch' : 'post')

                <div class="form-group">
                    <label for="name">Имя</label>
                    <input id="name" class="form-control" type="text" name="name" autocomplete="off" value="{{ isset($place) ? $place->name : '' }}">
                </div>

                <hr>

                <div class="form-group">
                    @if(isset($place))
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    @else
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    @endif
                </div>

            </form>
        </div>
    </div>
@endsection
