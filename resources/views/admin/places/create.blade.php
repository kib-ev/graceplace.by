@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Add Place</h1>
            <hr>
            <form action="{{ isset($place) ? route('admin.places.update', $place) : route('admin.places.store') }}" method="post">
                @csrf
                @method(isset($place) ? 'patch' : 'post')

                <div class="form-group">
                    <label for="name">Имя</label>
                    <input id="name" class="form-control" type="text" name="name" autocomplete="off" value="{{ isset($place) ? $place->name : '' }}">
                </div>

                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description" autocomplete="off">{{ isset($place) ? $place->description : '' }}</textarea>
                </div>

                <div class="form-group">
                    <label for="priceHour">Цена за час</label>
                    <input id="priceHour" class="form-control" type="number" step="0.01" min="0" name="price_hour" value="{{ isset($place) ? $place->price_hour : '' }}" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="image">Путь к картинке</label>
                    <input id="image" class="form-control" type="text" name="image_path" autocomplete="off" value="{{ isset($place) ? $place->image_path : '' }}">
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
