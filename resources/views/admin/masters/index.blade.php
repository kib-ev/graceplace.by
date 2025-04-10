@extends('app')

@section('style')
    <style>
        .comments__list {
            margin-top: 5px;
        }
        .comments__list .comments__item {
            background: lightgoldenrodyellow;
            padding: 15px 5px 1px;
            border: 1px solid #e1da8e;
            border-radius: 5px;
            margin-bottom: 2px;
        }

        .comments__list .comments__item_date {
            position: absolute;
            font-size: 10px;
            margin-top: -15px;
            color: #333;
        }


    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col">
            <h1>Список мастеров</h1>

            <div class="form">
                <form id="searchMaster" method="get" autocomplete="off">
                    <div class="form-group" style="width: 300px; display: inline-block;">
                        <input class="form-control"  type="text" name="search" value="{{ request('search') }}" placeholder="Имя, Фамилия, ID диркет">
                    </div>
                    <input class="btn btn-primary" type="submit" value="Найти">

                    @if(request('search'))
                        <a class="btn btn-danger" href="{{ route('admin.masters.index') }}" >X</a>
                    @endif
                </form>
            </div>

            <hr>
            <a href="{{ route('admin.masters.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <a href="?is_active=1">Активные</a>
            <a href="?is_active=0">Не активные</a>

            <table class="table table-bordered mb-5">
                <tr>
                    <td></td>
{{--                    <td></td>--}}
                    <td>Имя мастера</td>
                    <td>Телефон</td>
                    <td>Инста</td>
                    <td>Директ</td>
                    <td>Услуги</td>
                    <td></td>
                    <td>Дата <br> регистрации</td>
                    <td>Записи</td>
                    <td>Последний <br> визит</td>
{{--                    <td>Баланс</td>--}}
                    <td></td>
                </tr>
                @foreach($masters as $master)
                    <tr>
                        <td style="width: 50px; background: {{ $master->user }}">{{ $loop->index + 1 }}</td>

                        <td style="width: 300px;" title="master_id: {{ $master->id }} | user_id: {{ $master->user_id }}">
                            <a href="{{ route('admin.masters.show', $master) }}">{{ $master->person->full_name }}</a>
                            @if(is_null($master->person->patronymic))
                                <span style="color: red;">(отчество)</span>
                            @endif

                            <div class="comments__list">
                                @foreach($master->comments as $masterComment)
                                    <div class="comments__item">
                                        <span class="comments__item_date">
                                            {{ $masterComment->created_at->format('d.m.Y') }}
                                        </span>
                                        {{ $masterComment->text }}
                                    </div>
                                @endforeach
                            </div>

                        </td>

                        <td style="width: 200px;">
                            <ul style="list-style-type: none; margin-bottom: 0px; padding: 0px;">
                                <li>{{ $master->user->phone }}</li>
                            </ul>
                        </td>

                        <td>
                            @if(isset($master->instagram))
                                <a target="_blank" href="{{ $master->instagram }}">Inst</a>
                            @endif
                        </td>

                        <td>
                            @if(isset($master->direct))
                                <span class="float-end">
                                    <a href="{{ $master->direct }}" target="_blank">direct</a>
                                </span>
                            @endif
                        </td>

                        <td>{{ $master->description }}</td>

                        <td title="ЕРИП" style="width: 10px; background: {{ $master->user->getSetting('payment_link.place') && $master->user->getSetting('payment_link.storage') ? '#84db9b' : 'none' }}">
                        </td>
                        <td>
                            {{ $master->created_at->format('d.m.Y') }}

                            @isset($master->user->offer_accept_date)
                                <br>
                                <span style="background: greenyellow;">
                                    {{ $master->user->offer_accept_date?->format('d.m.Y') }}
                                </span>
                            @endisset


{{--                            @if($master->user->getSetting('payment_link.place') || $master->user->getSetting('payment_link.storage'))--}}
{{--                                <br>--}}
{{--                            @endif--}}

{{--                            @if($master->user->getSetting('payment_link.place'))--}}
{{--                                <i class="fa fa-check"></i>--}}
{{--                            @endif--}}

{{--                            @if($master->user->getSetting('payment_link.storage'))--}}
{{--                                <i class="fa fa-check"></i>--}}
{{--                            @endif--}}

                        </td>

                        <td style="white-space: nowrap;">
                            @php
                                $appointments = $master->user->appointments;
                            @endphp

                            {{ $appointments->count() }} /
                            {{ $appointments->whereNull('canceled_at')->count() }} /
                            {{ $appointments->whereNotNull('canceled_at')->count() }}

                        </td>

                        <td style="white-space: nowrap;">
                            @php
                                $lastAppointment = $master->lastAppointment();
                            @endphp

                            @if($lastAppointment && $lastAppointment->start_at < now())
                                {{ \Carbon\Carbon::now()->startOfDay()->diffInDays($lastAppointment->start_at) }} д. назад
                            @elseif($lastAppointment && $lastAppointment->start_at >= now())
                                <span style="color: greenyellow;">запись</span>
                            @else
                                <span style="color: orangered;">нет</span>
                            @endif


                        </td>

{{--                        <td>{{ (new \App\Services\PaymentService())->getUserBalance($master->user) }} / {{ $master->user->getBalance() }}</td>--}}

                        <td><a href="{{ route('admin.masters.edit', $master) }}">edit</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
