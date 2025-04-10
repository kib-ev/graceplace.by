@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Пользователи</h1>

            {{--            <div class="form">--}}
            {{--                <form id="searchMaster" method="get" autocomplete="off">--}}
            {{--                    <div class="form-group" style="width: 300px; display: inline-block;">--}}
            {{--                        <input class="form-control"  type="text" name="search" value="{{ request('search') }}" placeholder="Имя, Фамилия, ID диркет">--}}
            {{--                    </div>--}}
            {{--                    <input class="btn btn-primary" type="submit" value="Найти">--}}
            {{--                </form>--}}

            {{--            </div>--}}

            {{--            <hr>--}}
            {{--            <a href="{{ route('admin.masters.create') }}" class="btn btn-primary">Создать</a>--}}
            {{--            <hr>--}}

            <table class="table table-bordered mb-5">
                {{--                <tr>--}}
                {{--                    <td></td>--}}
                {{--                    <td></td>--}}
                {{--                    <td>Имя мастера</td>--}}
                {{--                    <td>Телефон</td>--}}
                {{--                    <td>Инста</td>--}}
                {{--                    <td>Директ</td>--}}
                {{--                    <td>Услуги</td>--}}
                {{--                    <td>Дата <br> регистрации</td>--}}
                {{--                    <td>Записи</td>--}}
                {{--                    <td>Последний <br> визит</td>--}}


                {{--                    <td></td>--}}
                {{--                    <td></td>--}}
                {{--                </tr>--}}
                @foreach(\App\Models\User::all() as $user)
                    <tr>
                        <td style="width: 50px;">{{ $loop->index + 1 }}</td>
                        <td>
                            {{ $user->name }}
                        </td>
                        <td style="background: {{ $user->balance > 0 ? '#cef1ce' : '#fff' }}; text-align: right;">
                            {{ $user->balance }}
                        </td>

                        <td style="background: {{ $user->balance > 0 ? '#cef1ce' : '#fff' }}; text-align: right;">
                            <form action="{{ route('admin.users.update', $user) }}" method="post" autocomplete="off">
                                @method('patch')
                                @csrf
                                <input type="text" name="password" placeholder="new password">

                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
