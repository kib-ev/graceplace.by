@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Add Place</h1>
            <hr>
            <form action="{{ isset($place) ? route('admin.places.update', $place) : route('admin.places.store') }}" method="post">
                @csrf
                @method(isset($place) ? 'patch' : 'post')

                <div class="form-group mb-2">
                    <label for="name">Имя</label>
                    <input id="name" class="form-control" type="text" name="name" autocomplete="off" value="{{ isset($place) ? $place->name : '' }}">
                </div>

                <div class="form-group mb-2">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description" autocomplete="off">{{ isset($place) ? $place->description : '' }}</textarea>
                </div>

                <div class="form-group mb-2">
                    <label for="priceHour">Цена за час</label>
                    <input id="priceHour" class="form-control" type="number" step="0.01" min="0" name="price_hour" value="{{ isset($place) ? $place->price_hour : '' }}" autocomplete="off">
                </div>

                <div class="form-group mb-2">
                    <label for="image">Путь к картинке</label>
                    <input id="image" class="form-control" type="text" name="image_path" autocomplete="off" value="{{ isset($place) ? $place->image_path : '' }}">
                </div>

                <div class="form-group mb-2">
                    <label for="sort">Сортировка</label>
                    <input id="sort" class="form-control" type="text" name="sort" autocomplete="off" value="{{ isset($place) ? $place->sort : '' }}">
                </div>

                <div class="form-group">
                    <input type="hidden" name="is_hidden" value="0">
                    <input id="isHidden" class="form-check-input" type="checkbox" name="is_hidden" value="1" {{ isset($place) && $place->is_hidden ? 'checked' : '' }}>
                    <label class="form-check-label" for="isHidden" style="user-select: none;">
                        Скрыто
                    </label>
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
