@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col-12">
            @if(isset($storageBooking))
                <h1>Редактировать бронь</h1>
                <a href="{{ route('admin.storage-cells.show', $storageBooking->cell) }}">Просмотреть ячейку</a>
            @else
                <h1>Добавить бронь</h1>
            @endif

            {{--            @if(isset($storageBooking) && $storageBooking->canceled_at)--}}
            {{--                <h2 class="bg-danger text-white p-2">ОТМЕНА {{ $storageBooking->canceled_at->format('d.m.Y H:i') }}</h2>--}}
            {{--            @endif--}}

            <hr>

            @if($errors->any())
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            @foreach($errors->all() as $error)
                                <strong>{{ $error }}</strong><br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif


        </div>
    </div>

    <div class="row">
        <div class="col-4">
            <form action="{{ isset($storageBooking) ? route('admin.storage-bookings.update', $storageBooking) : route('admin.storage-bookings.store') }}" method="post"
                  autocomplete="off">
                @csrf
                @method(isset($storageBooking) ? 'patch' : 'post')


                <div class="form-group mb-2">
                    <label for="masterId">Master</label>
                    <select id="masterId" name="user_id" class="form-control" required disabled>
                        <option value=""></option>
                        @foreach(\App\Models\User::role('master')->get()->sortBy('name') as $user)
                            <option value="{{ $user->id }}" @selected($user->id == (isset($storageBooking) ? $storageBooking->user_id : request('user_id')))>
                                {{ $user->name }} | {{ $user->master->description }} | {{ $user->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="modelId">Ячейка</label>
                    <select id="modelId" class="form-control" name="model_id" required>
                        <option value=""></option>
                        @foreach(\App\Models\StorageCell::all() as $storageCell)
                            <option
                                value="{{ $storageCell->id }}" @selected($storageCell->id == (isset($storageBooking) ? $storageBooking->model_id : ''))>{{ $storageCell->number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="storageBookingStartAt">Дата начала</label>
                    <input id="storageBookingStartAt" class="form-control" type="date" name="start_at"
                           value="{{ (isset($storageBooking) ? $storageBooking->start_at->format('Y-m-d') : '') }}" required>
                </div>

                <div class="form-group mb-2">
                    <label for="storageBookingDuration">Продолжительность</label>
                    <input id="storageBookingDuration" class="form-control" type="number" step="1" name="duration"
                           value="{{ (isset($storageBooking) ? $storageBooking->duration : '') }}" required>
                </div>

                @if(is_null($storageBooking->finished_at))
                    <div class="form-group mb-2">
                        <button class="btn btn-primary" type="submit">Сохранить</button>
                    </div>
                @endif
            </form>


            @if(isset($storageBooking) && is_null($storageBooking->finished_at))
                <form action="{{ isset($storageBooking) ? route('admin.storage-bookings.update', $storageBooking) : route('admin.storage-bookings.store') }}" method="post"
                      autocomplete="off">
                    @csrf
                    @method(isset($storageBooking) ? 'patch' : 'post')

                    <input type="hidden" name="finished_at" value="{{ now() }}">

                    <button class="btn btn-danger" type="submit">Завершить бронь</button>

                </form>
            @endif

            @if(isset($storageBooking) && $storageBooking->finished_at)
                Бронь завершена: {{ $storageBooking->finished_at->format('d.m.Y H:i') }}
            @endif

            @if(isset($storageBooking))
                <form action="{{ route('admin.storage-bookings.destroy', $storageBooking) }}" method="post" style="float: right;">
                    @csrf
                    @method('delete')
                    <button type="submit" disabled>Удалить</button>
                </form>
            @endif
        </div>

        @if(isset($storageBooking))
            <div class="col-4">
                Комментарии

                <div class="comments">
                    @include('admin.comments.includes.widget', ['model' => $storageBooking, 'title' => 'Комментарии', 'type' => 'admin'])
                </div>
            </div>
        @endif
    </div>

    @if(isset($storageBooking))
        <div class="row mt-4" id="payments">
            <div class="col-12">
                <h3>Управление оплатами</h3>
                <p class="text-muted">
                    Ожидаемая: {{ number_format($storageBooking->getExpectedTotal(), 2, '.') }} BYN
                    | Остаток: {{ number_format($storageBooking->leftToPay(), 2, '.') }} BYN
                    | {{ $storageBooking->isPaid() ? 'Оплачено' : 'Не оплачено' }}
                </p>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card mb-3">
                    <div class="card-header"><strong>Создать требование на оплату</strong></div>
                    <div class="card-body">
                        <form action="{{ route('admin.storage-bookings.payment-requirements.store') }}" method="POST" class="row g-2 align-items-end">
                            @csrf
                            <input type="hidden" name="storage_booking_id" value="{{ $storageBooking->id }}">
                            <div class="col-md-2">
                                <label class="form-label">Сумма</label>
                                <input type="number" name="amount" class="form-control form-control-sm" step="0.01" required
                                       value="{{ $storageBooking->cell->cost_per_month ?? 0 }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Срок действия</label>
                                <select name="expiration_days" class="form-control form-control-sm">
                                    <option value="30">30 дней</option>
                                    <option value="14">14 дней</option>
                                    <option value="7">7 дней</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Дата создания</label>
                                <input type="datetime-local" name="created_at" class="form-control form-control-sm"
                                       value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm">Создать требование</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><strong>Требования на оплату</strong></div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Мастер</th>
                                <th>Создано</th>
                                <th>Ожидаемая</th>
                                <th>Остаток</th>
                                <th>Срок</th>
                                <th>Статус</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($storageBooking->paymentRequirements as $req)
                                <tr>
                                    <td>{{ $req->id }}</td>
                                    <td>
                                        @if($req->user?->master)
                                            <a href="{{ route('admin.masters.show', $req->user->master) }}">{{ $req->user->master->full_name }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $req->created_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ number_format($req->expected_amount ?? 0, 2) }} BYN</td>
                                    <td>{{ number_format($req->remaining_amount ?? 0, 2) }} BYN</td>
                                    <td>{{ $req->due_date?->format('d.m.Y') ?? '—' }}</td>
                                    <td>{{ $req->status }}</td>
                                    <td>
                                        <a href="{{ route('admin.payment-requirements.destroy', $req->id) }}"
                                           onclick="return confirm('Удалить требование?')">удалить</a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-muted">Нет требований</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><strong>Новый платеж</strong></div>
                    <div class="card-body">
                        <form action="{{ route('admin.storage-bookings.payments.store') }}" method="POST" class="row g-2 align-items-end">
                            @csrf
                            <input type="hidden" name="storage_booking_id" value="{{ $storageBooking->id }}">
                            <div class="col-md-2">
                                <label class="form-label">Дата</label>
                                <input type="datetime-local" name="created_at" class="form-control form-control-sm" required
                                       value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Сумма</label>
                                <input type="number" name="amount" class="form-control form-control-sm" step="0.01" required
                                       value="{{ $storageBooking->leftToPay() }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Метод</label>
                                <select name="payment_method" class="form-control form-control-sm" required>
                                    <option value="cash">Наличные</option>
                                    <option value="service">ЕРИП</option>
                                    <option value="card">Карта</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Примечание</label>
                                <input type="text" name="note" class="form-control form-control-sm" placeholder="Необязательно">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm">Провести оплату</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Платежи</strong></div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Мастер</th>
                                <th>Дата</th>
                                <th>Сумма</th>
                                <th>Метод</th>
                                <th>Статус</th>
                                <th>Примечание</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($storageBooking->payments as $payment)
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td>
                                        @if($payment->user?->master)
                                            <a href="{{ route('admin.masters.show', $payment->user->master) }}">{{ $payment->user->master->full_name }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ number_format($payment->amount, 2) }} BYN</td>
                                    <td>
                                        <form action="{{ route('admin.payments.update', $payment) }}" method="post" id="payment-form-{{ $payment->id }}">
                                            @csrf @method('patch')
                                            <select name="payment_method" class="form-select form-select-sm" style="width:auto; min-width: 90px;">
                                                @foreach(\App\Models\Payment::getPaymentMethods() as $v => $n)
                                                    <option value="{{ $v }}" @selected($payment->payment_method == $v)>{{ $n }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <select name="status" form="payment-form-{{ $payment->id }}" class="form-select form-select-sm" style="width:auto; min-width: 100px;">
                                            @foreach(\App\Models\Payment::getPaymentStatuses() as $v => $n)
                                                <option value="{{ $v }}" @selected($payment->status == $v)>{{ $n }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="note" form="payment-form-{{ $payment->id }}" class="form-control form-control-sm" value="{{ $payment->note ?? '' }}" placeholder="—" style="min-width: 120px;">
                                    </td>
                                    <td>
                                        <button type="submit" form="payment-form-{{ $payment->id }}" class="btn btn-sm btn-primary">Сохранить</button>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.payments.destroy', $payment) }}" onclick="return confirm('Удалить платеж?')">удалить</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
