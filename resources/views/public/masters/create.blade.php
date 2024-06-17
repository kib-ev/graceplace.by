@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Add Master</h1>
            <hr>
            <form action="{{ isset($master) ? route('public.masters.update', $master) : route('public.masters.store') }}" method="post">
                @csrf
                @method(isset($master) ? 'patch' : 'post')

                <div class="form-group">
                    <label for="firstName">Имя</label>
                    <input id="firstName" class="form-control" type="text" name="first_name" autocomplete="off" value="{{ isset($master) ? $master->person->first_name : '' }}">
                </div>

                <div class="form-group">
                    <label for="lastName">Фамилия</label>
                    <input id="lastName" class="form-control" type="text" name="last_name" autocomplete="off" value="{{ isset($master) ? $master->person->last_name : '' }}">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input id="phone" class="form-control" type="text" name="phone" autocomplete="off" value="{{ isset($master) ? $master->person->phones->first()?->number : '' }}">
                </div>


                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" class="form-control" name="description" autocomplete="off">{{ isset($master) ? $master->description : '' }}</textarea>
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
                    @if(isset($master))
                        <button type="submit" class="btn btn-primary" disabled>Сохранить</button>
                    @else
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    @endif
                </div>

            </form>
        </div>
    </div>
@endsection
