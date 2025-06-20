@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Add User</h1>
            <hr>
            <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" autocomplete="off" method="post">
                @csrf
                @method(isset($user) ? 'patch' : 'post')

                <div class="form-group">
                    <label for="firstName">Имя</label>
                    <input id="firstName" class="form-control" type="text" name="first_name" value="{{ isset($user) ? $master->person->first_name : '' }}">
                </div>

                <div class="form-group">
                    <label for="lastName">Фамилия</label>
                    <input id="lastName" class="form-control" type="text" name="last_name" value="{{ isset($user) ? $master->person->last_name : '' }}">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input id="phone" class="form-control" type="text" name="phone" value="{{ isset($user) ? $master->person->phones->first()?->number : '' }}">
                </div>


                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description">{{ isset($user) ? $master->description : '' }}</textarea>
                </div>

                <div class="form-group">
                    <label for="imagePath">Ссылка на фото</label>
                    <input id="imagePath" class="form-control" name="image_path" value="{{ isset($master) ? $master->image_path : '' }}">
                </div>

                <div class="form-group">
                    <label for="instagram">Инстаграм</label>
                    <textarea id="instagram" class="form-control" name="instagram">{{ isset($master) ? $master->instagram : '' }}</textarea>
                </div>

                <div class="form-group">
                    <label for="direct">Ссылка на direct</label>
                    <input id="direct" class="form-control" name="direct" value="{{ isset($master) ? $master->direct : '' }}">
                </div>

{{--                <div class="form-group">--}}
{{--                    <label for="postTitle">Текст</label>--}}

{{--                    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.0/classic/ckeditor.js"></script>--}}

{{--                    <textarea id="editor" class="form-control" name="content">{{ $post->id ? $post->content : '' }}</textarea>--}}

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
