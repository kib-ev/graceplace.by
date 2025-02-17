@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Мастер - {{ $master->person->first_name }} {{ $master->person->last_name }}</h1>
            <hr>

            <table class="table table-bordered">
                <tr style="font-size: 2em;">
                    <td>Баланс: <span class="float-end">{{ $master->user->real_balance }}</span></td>
                    <td>Бонусы: <span class="float-end">{{ $master->user->bonus_balance }}</span></td>
                </tr>
            </table>

            <table class="table table-bordered">
                <tr>
                    <td>id: {{ $master->id }}

                    </td>
                </tr>
                <tr>
                    <td>user_id: {{ \App\Services\AppointmentService::getUserByMasterId($master->id)?->id }}</td>
                </tr>

{{--                <tr>--}}
{{--                    <td>{{ $master->person->birth_date }}</td>--}}
{{--                </tr>--}}
                <tr>
                    <td>
                        <ul style="list-style-type: none; margin: 0px; padding: 0px;">
                            @foreach($master->person->phones as $phone)
                                <li>{{ $phone->number }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>
                        {{ $master->description }}
                    </td>
                </tr>
                <tr>
                    <td>{{ $master->instagram }}</td>
                </tr>

                <tr>
                    <td>Написать в <a href="{{ $master->direct }}">direct</a></td>
                </tr>

                <tr>
                    <td>Количество записей: {{ \App\Models\Appointment::where('user_id', $master->user_id)->count() }}</td>
                </tr>

                <tr>
                    <td>Количество отмен: {{ \App\Models\Appointment::whereNotNull('canceled_at')->where('user_id', $master->user_id)->count() }}</td>
                </tr>

                <tr>
                    <td>Количество посещений: {{ \App\Models\Appointment::whereNull('canceled_at')->where('user_id', $master->user_id)->count() }}</td>
                </tr>

                <tr>
                    <td>СУММА: {{ $sum = \App\Models\Appointment::whereNull('canceled_at')->where('user_id', $master->user_id)->sum('price') }} BYN</td>
                </tr>

                <tr>
                    <td>Всего часов: {{ $hours = \App\Models\Appointment::whereNull('canceled_at')->where('user_id', $master->user_id)->sum('duration') / 60 }}</td>
                </tr>

                <tr>
                    <td>Сред. стоимость часа: {{ $hours ? $sum / $hours : 0 }}</td>
                </tr>

                <tr>
                    <td>

                        <span style="background: #f7f7cd; padding: 5px 10px;">Ваш логин: {{ $master->user->phone }} пароль: graceplace{{ $master->id }}</span>

                        <span style="float: right"><a href="{{ url('/admin/users/' . $master->user->id . '/login') }}"><i class="fa fa-sign-in"></i></a></span>
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ json_encode($master->user->getSetting('workspace_visibility')) }}
                    </td>
                </tr>

                <tr>
                    <td>
                        Дата согласия с офертой:
                        @if(isset($master->user->offer_accept_date))
                        {{ \Illuminate\Support\Carbon::parse($master->user->offer_accept_date)->format('d.m.Y H:i') }}
                        @endif
                    </td>
                </tr>

            </table>

            <a class="btn btn-primary float-end" href="{{ route('admin.masters.edit', $master) }}">edit</a>

            @if($master->user->appointments->count() == 0)
                <form action="{{ route('admin.masters.destroy', $master) }}" method="post">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger">удалить</button>
                </form>
            @endif

        </div>
    </div>

    <div class="row mt-3">
        <div class="col">
            <div class="comments">
                @include('admin.comments.includes.widget', ['model' => $master, 'title' => 'Комментарий', 'type' => 'admin'])
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col">
            <table class="table table-bordered">
                <tr>
                    <td></td>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ \Carbon\Carbon::parse('01-'. $i . '-2024')->format('M-Y') }}</td>
                    @endfor
                </tr>
                <tr>
                    <td><b>Сумма оплат</b></td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ $master->user->appointments()->whereYear('start_at', '2024')->whereMonth('start_at', $i)->sum('price') }}
                        </td>
                    @endfor
                </tr>
                <tr>
                    <td><b>Часов аренды</b></td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ number_format($master->user->appointments()->whereYear('start_at', '2024')->whereMonth('start_at', $i)->sum('duration') / 60, 2) }}
                        </td>
                    @endfor
                </tr>
            </table>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col">
            <table class="table table-bordered">
                <tr>
                    <td></td>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ \Carbon\Carbon::parse('01-'. $i . '-2025')->format('M-Y') }}</td>
                    @endfor
                </tr>
                <tr>
                    <td><b>Сумма оплат</b></td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ $master->user->appointments()->whereYear('start_at', '2025')->whereMonth('start_at', $i)->sum('price') }}
                        </td>
                    @endfor
                </tr>
                <tr>
                    <td><b>Часов аренды</b></td>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ number_format($master->user->appointments()->whereYear('start_at', '2025')->whereMonth('start_at', $i)->sum('duration') / 60, 2) }}
                        </td>
                    @endfor
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <a class="btn btn-primary me-3 mb-3" href="https://graceplace.by/admin/appointments/create?master_id={{ $master->id }}">Добавить запись</a>

            @include('admin.appointments.includes.table', ['appointments' => $master->user->appointments])
        </div>
    </div>
@endsection
