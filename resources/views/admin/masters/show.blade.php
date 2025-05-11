@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Мастер - {{ $master->person->last_name }} {{ $master->person->first_name }} {{ $master->person->patronymic }}</h1>
            <hr>

            @if($master->user->getDebtAmount() > 0)
                <div class="bg-danger text-white p-3 mb-3" style="font-size: 1.4em;">Задолженность: {{ number_format($master->user->getDebtAmount(), 2) }} </div>
            @endif

            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#nav-info" type="button" role="tab">Информация</button>
                    <button id="nav-appointments-tab" data-bs-target="#nav-appointments" class="nav-link"  data-bs-toggle="tab" type="button" role="tab">Записи</button>
                    <button id="nav-stats-tab" data-bs-target="#nav-stats" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Статистика</button>
                    <button id="nav-comments-tab" data-bs-target="#nav-comments" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Комментарии ({{ $master->comments()->count() }})</button>
                    <button id="nav-payment-tab" data-bs-target="#nav-payment" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Ссылки ЕРИП ({{ !empty($master->user->getSetting('payment_link.place')) + !empty($master->user->getSetting('payment_link.storage'))  }})</button>

                    @if(count($master->user->storageBookings))
                        <button id="nav-storage-tab" data-bs-target="#nav-storage" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Локер ({{ count($master->user->storageBookings) }})</button>
                    @endif
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div id="nav-info" class="tab-pane fade show active" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        <table class="table table-bordered">
                            <tr>
                                <td>id: {{ $master->id }}</td>
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
                                <td>
                                    @if($master->direct)
                                        <a target="_blank" href="{{ $master->direct }}">Написать в Direct</a>
                                    @else

                                    @endif
                                </td>
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
                                <td style="background: lightgoldenrodyellow">
                                    @foreach($master->person->phones as $phone)
                                        <input class="form-control" type="text" value="{{ $phone->number }} {{ $master->person->last_name }} {{ $master->person->first_name }} {{ $master->person->patronymic }}" readonly>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td style="background: lightgoldenrodyellow">
                                    <input class="form-control" type="text" value="{{ $master->user->email }}" readonly>
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

                        <a class="btn btn-primary float-end" href="{{ route('admin.masters.edit', $master) }}">Изменить</a>

                        @if($master->user->appointments->count() == 0)
                            <form action="{{ route('admin.masters.destroy', $master) }}" method="post">
                                @method('delete')
                                @csrf
                                <button class="btn btn-danger">удалить</button>
                            </form>
                        @endif
                    </div>
                </div>


                <div id="nav-payment" class="tab-pane fade" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        @if(isset($master))
                            <form class="mb-4" action="{{ route('admin.update-settings') }}" method="post" autocomplete="off">
                                @csrf
                                @method('post')

                                Ссылка для оплаты МЕСТА [10{{ str_pad($master->id, 3, '0', STR_PAD_LEFT) }}]:

                                @if($master->user->getSetting('payment_link.place'))
                                    <span style="padding: 0px 5px; background: {{ str_contains($master->user->getSetting('payment_link.place'), 10 . str_pad($master->id, 3, '0', STR_PAD_LEFT)) ? '#c1edc1' : 'none' }}">
                                        {{ substr($master->user->getSetting('payment_link.place'), 63, 14) }}
                                    </span>
                                @endif

                                <br>
                                <input class="form-control" type="text" value="Оплата аренды рабочего места. Публичная оферта от 01.01.2025 г.">
                                <input type="hidden" name="user_id" value="{{ $master->user_id }}">
                                <input type="hidden" name="key" value="payment_link.place">
                                <div>
                                    <input class="form-control d-inline-block" type="text" name="value" value="{{ $master->user->getSetting('payment_link.place') }}">
                                    <button class="btn btn-primary d-inline-block" type="submit">Сохранить</button>
                                </div>

                            </form>

                            <form action="{{ route('admin.update-settings') }}" method="post" autocomplete="off">
                                @csrf
                                @method('post')
                                Ссылка для оплаты ЛОКЕРА [20{{ str_pad($master->id, 3, '0', STR_PAD_LEFT) }}]:

                                @if($master->user->getSetting('payment_link.storage'))
                                    <span style="padding: 0px 5px; background: {{ str_contains($master->user->getSetting('payment_link.storage'), 20 . str_pad($master->id, 3, '0', STR_PAD_LEFT)) ? '#c1edc1' : 'none' }}">
                                        {{ substr($master->user->getSetting('payment_link.storage'), 63, 14) }}
                                    </span>
                                @endif

                                <br>
                                <input class="form-control" type="text" value="Оплата аренды локера. Публичная оферта от 01.01.2025 г.">
                                <input type="hidden" name="user_id" value="{{ $master->user_id }}">
                                <input type="hidden" name="key" value="payment_link.storage">
                                <div>
                                    <input class="form-control d-inline-block" type="text" name="value" value="{{ $master->user->getSetting('payment_link.storage') }}">

                                    <button class="btn btn-primary d-inline-block" type="submit">Сохранить</button>
                                </div>
                            </form>

                        @endif
                    </div>
                </div>


                <div id="nav-stats" class="tab-pane fade"  role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
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
                                        {{ $master->user->appointments()->whereNull('canceled_at')->whereYear('start_at', '2024')->whereMonth('start_at', $i)->sum('price') }}
                                    </td>
                                @endfor
                            </tr>
                            <tr>
                                <td><b>Часов аренды</b></td>
                                @for($i = 1; $i <=12; $i++)
                                    <td>
                                        {{ number_format($master->user->appointments()->whereNull('canceled_at')->whereYear('start_at', '2024')->whereMonth('start_at', $i)->sum('duration') / 60, 2) }}
                                    </td>
                                @endfor
                            </tr>
                        </table>

                        <br>

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
                                        {{ $master->user->appointments()->whereNull('canceled_at')->whereYear('start_at', '2025')->whereMonth('start_at', $i)->sum('price') }}
                                    </td>
                                @endfor
                            </tr>
                            <tr>
                                <td><b>Часов аренды</b></td>
                                @for($i = 1; $i <=12; $i++)
                                    <td>
                                        {{ number_format($master->user->appointments()->whereNull('canceled_at')->whereYear('start_at', '2025')->whereMonth('start_at', $i)->sum('duration') / 60, 2) }}
                                    </td>
                                @endfor
                            </tr>
                        </table>
                    </div>
                </div>


                <div id="nav-comments" class="tab-pane fade" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        <div class="comments">
                            @include('admin.comments.includes.widget', ['model' => $master, 'title' => 'Комментарий', 'type' => 'admin'])
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="nav-appointments" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        <a class="btn btn-primary me-3 mb-3" href="https://graceplace.by/admin/appointments/create?master_id={{ $master->id }}">Добавить запись</a>
                        @include('admin.appointments.includes.table', ['appointments' => $master->user->appointments])
                    </div>
                </div>

                @if(count($master->user->storageBookings))
                    <div id="nav-storage" class="tab-pane fade" role="tabpanel" tabindex="0">
                        <div class="tab bg-light p-3">
                            <div class="storageBookings">
                                <table class="table table-bordered">
                                    @foreach($master->user->storageBookings as $storageBooking)
                                        <tr>
                                            <td><a href="{{ route('admin.storage-bookings.edit', $storageBooking) }}">{{ $storageBooking->cell->number }}</a></td>
                                        </tr>
                                        <tr>
                                            <td>Осталось дней: {{ $storageBooking->daysLeft() }}</td>
                                        </tr>
                                        <tr>
                                           <td>
                                               <div class="comments">
                                                   @include('admin.comments.includes.widget', ['model' => $storageBooking, 'title' => 'Комментарий', 'type' => 'admin'])
                                               </div>
                                           </td>
                                        </tr>
                                        <tr>
                                            <td>Код: {{ $storageBooking->cell->secret }}</td>
                                        </tr>
                                    @endforeach
                                </table>

                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>




    <div class="row mt-3">
        <div class="col">

        </div>
    </div>

    <div class="row mt-3">
        <div class="col">

        </div>
    </div>

    <div class="row mt-3">
        <div class="col">

        </div>
    </div>

    <div class="row">
        <div class="col">

        </div>
    </div>
@endsection
