@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Пользователи</h1>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

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
                <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Админ</th>
                    <th>Менеджер</th>
                    <th>Пароль</th>
                </tr>
                </thead>
                <tbody>
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
                @foreach($users as $user)
                    <tr>
                        <td style="width: 50px;">{{ $loop->index + 1 }}</td>
                        <td>{{ $user->id }}</td>
                        <td>
                            {{ $user->name }}
                        </td>
                        <td>
                            {{ $user->phone }}
                        </td>
                        <td>
                            @if($user->id === auth()->id())
                                <input type="checkbox" checked disabled title="Вы не можете снять роль администратора с себя">
                            @elseif($user->id === 1)
                                <input type="checkbox" checked disabled title="Пользователь 1 — суперадмин, права нельзя снять">
                            @else
                                <form action="{{ route('admin.users.update', $user) }}" method="post" class="d-inline">
                                    @method('patch')
                                    @csrf
                                    <input type="hidden" name="admin" value="0">
                                    <input type="checkbox" name="admin" value="1"
                                        {{ $user->hasRole('admin') ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <noscript><button type="submit" class="btn btn-sm btn-outline-secondary ms-1">✓</button></noscript>
                                </form>
                            @endif
                        </td>
                        <td>
                            @if($user->id === auth()->id() || $user->id === 1)
                                @if($user->hasRole('manager'))
                                    <span title="Нельзя изменить">✓</span>
                                @else
                                    —
                                @endif
                            @else
                                <form action="{{ route('admin.users.update', $user) }}" method="post" class="d-inline">
                                    @method('patch')
                                    @csrf
                                    <input type="hidden" name="manager" value="0">
                                    <input type="checkbox" name="manager" value="1"
                                        {{ $user->hasRole('manager') ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <noscript><button type="submit" class="btn btn-sm btn-outline-secondary ms-1">✓</button></noscript>
                                </form>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.users.update', $user) }}" method="post" autocomplete="off" class="d-inline">
                                @method('patch')
                                @csrf
                                <input type="text" name="password" placeholder="новый пароль" class="form-control form-control-sm" style="width: 120px; display: inline-block;">
                                <button type="submit" class="btn btn-sm btn-outline-primary">✓</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
