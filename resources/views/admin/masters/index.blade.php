@extends('admin.layouts.app')

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
                    <td style="width: 50px;"></td>
                    <td style="width: 440px;">Имя мастера</td>
                    <td style="width: 140px;">Телефон</td>
                    <td>Инста</td>
                    <td>Директ</td>
                    <td>Услуги</td>
                    <td></td>
                    <td>Дата <br> регистрации</td>
                    <td>Записи</td>
                    <td>Последний <br> визит</td>
                    <td></td>
                </tr>
                @foreach($masters as $master)
                    @if($master->user)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>

                        <td title="master_id: {{ $master->id }} | user_id: {{ $master->user_id }}">
                            <a href="{{ route('admin.masters.show', $master) }}">{{ $master->person->full_name }}</a>
                            @if(is_null($master->person->patronymic))
                                <span style="color: red;">(отчество)</span>
                            @endif

                            @if($master->user->getDebtAmount() > 0)
                                <div class="bg-danger text-white p-2">Задолженность: {{ number_format($master->user->getDebtAmount(), 2) }} </div>
                            @endif

                            @include('admin.comments.includes.widget', ['model' => $master, 'title' => '', 'type' => 'admin', 'showForm' => false, 'showControl' => false])
                        </td>

                        <td>
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

                        </td>

                        <td style="white-space: nowrap;">
                            @php
                                $appointments = $master->user->appointments;
                            @endphp

                            {{ $totalCount = $appointments->count() }} /
                            {{ $visitCount = $appointments->whereNull('canceled_at')->count() }} /
                            {{ $cancelCount = $appointments->whereNotNull('canceled_at')->count() }}

                            <br>
                            @if($totalCount > 10)
                                @php
                                    $lateCancelCount = $master->user->getLateCancellationCount();
                                    $visitPercent = $lateCancelCount / $totalCount * 100;
                                @endphp
                                <span class="{{ $lateCancelCount > 10 ? 'bg-danger text-white' : '' }}">{{ number_format($lateCancelCount) }} %</span>
                            @endif
                            <br>
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

                        <td><a href="{{ route('admin.masters.edit', $master) }}">edit</a></td>
                    </tr>
                    @else
                        <tr>
                            <td colspan="11">
                                {{ $master->full_name }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </table>
        </div>
    </div>
@endsection
