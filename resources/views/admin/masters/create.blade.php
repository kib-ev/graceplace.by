@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col-12">
            <h1>Add Master</h1>
            <hr>

        </div>
        <div class="col-8">
            <form action="{{ isset($master) ? route('admin.masters.update', $master) : route('admin.masters.store') }}" autocomplete="off" method="post">
                @csrf
                @method(isset($master) ? 'patch' : 'post')

                <div class="form-group">
                    <label for="lastName">Фамилия</label>
                    <input id="lastName" class="form-control" type="text" name="last_name" value="{{ isset($master) ? $master->person->last_name : '' }}">
                </div>

                <div class="form-group">
                    <label for="firstName">Имя</label>
                    <input id="firstName" class="form-control" type="text" name="first_name" value="{{ isset($master) ? $master->person->first_name : '' }}">
                </div>

                <div class="form-group">
                    <label for="patronymic">Отчество</label>
                    <input id="patronymic" class="form-control" type="text" name="patronymic" autocomplete="off" value="{{ isset($master) ? $master->person->patronymic : '' }}">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input id="phone" class="form-control" type="text" name="phone" value="{{ isset($master) ? $master->person->phones->first()?->number : '' }}">
                </div>

                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description">{{ isset($master) ? $master->description : '' }}</textarea>
                </div>

{{--                <div class="form-group">--}}
{{--                    <label for="imagePath">Ссылка на фото</label>--}}
{{--                    <input id="imagePath" class="form-control" name="image_path" value="{{ isset($master) ? $master->image_path : '' }}">--}}
{{--                </div>--}}

                <div class="form-group">
                    <label for="instagram">Инстаграм</label>
                    <textarea id="instagram" class="form-control" name="instagram">{{ isset($master) ? $master->instagram : '' }}</textarea>
                </div>

                <div class="form-group">
                    <label for="direct">Ссылка на direct</label>
                    <input id="direct" class="form-control" name="direct" value="{{ isset($master) ? $master->direct : '' }}">
                </div>

                @if(isset($master))
                    <div class="form-group">
                        <label for="is_active">Активен</label>
                        <select id="isActive" class="form-control" name="is_active" id="">
                            <option @selected($master->user->is_active == 1) value="1">Да</option>
                            <option @selected($master->user->is_active == 0) value="0">Нет</option>
                        </select>
                    </div>
                @endif

                <hr>

                <div class="form-group">
                    @if(isset($master))
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    @else
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    @endif
                </div>

            </form>

            @if(isset($master) && $master->user->appointments->count() == 0)
                <form action="{{ route('admin.masters.destroy', $master) }}" method="post" style="float: right;">
                    @csrf
                    @method('delete')
                    <button type="submit">Удалить</button>
                </form>
            @endif

        </div>


    </div>
@endsection
