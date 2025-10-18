@extends('admin.layouts.app')


@section('style')
<style>
    .comments__list .comment__item {
        margin-bottom: 10px;
    }
    .comments__list .comment__item .comment__top {
        font-size: 0.8em;
        line-height: 14px;
    }
    .comments__list .comment__item .comment__date {

    }
    .comments__list .comment__item .comment__text {
        /*background: #e9ecef;*/
        /*padding: 5px 10px;*/
        /*border: 1px solid #dee2e6;*/

    }
    .comments__list .comment__item button[type=submit] {
        border: none;
        background: none;
    }
    .comments__list .comment__item .comment__text pre {
        font: inherit;
    }
</style>
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            @if(isset($appointment))
                <h1>Редактировать запись</h1>
            @else
                <h1>Добавить запись</h1>
            @endif

            @if(isset($appointment) && $appointment->canceled_at)
                <h2 class="bg-danger text-white p-2">
                    ОТМЕНА {{ $appointment->canceled_at->format('d.m.Y H:i') }}
                </h2>
            @endif

        </div>

        @if(isset($appointment) && isset($appointment->user->master))
            <div class="col-6 mb-3">
                <a href="{{ route('admin.masters.show', $appointment->user->master->id) }}">{{ $appointment->user->name }}</a>
            </div>

            <div class="col-6 mb-3">
                Дата и время создания {{ $appointment->created_at->format('d.m.Y H:i') }}
                <br>
                @if($appointment->isCreatedByUser())
                    Автор: Мастер
                @else
                    Автор: Админ
                @endif
            </div>

        @endif

        <hr>
        <div class="col-4">

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


            <form action="{{ isset($appointment) ? route('admin.appointments.update', $appointment) : route('admin.appointments.store') }}" method="post" autocomplete="off">
                @csrf
                @method(isset($appointment) ? 'patch' : 'post')

                <div class="form-group mb-2">
                    <label for="userId">Мастер</label>
                    <select id="userId" name="user_id" class="form-control" required>
                        <option value=""></option>
                        @foreach(\App\Models\User::role('master')->orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" @selected($user->id == (isset($appointment) ? $appointment->user_id : request('user_id')))>
                                {{ $user->name }}
                                @if($user->master)
                                    | {{ $user->master->description }} | {{ $user->master->phone }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="placeId">Место</label>
                    <select id="placeId" name="place_id" class="form-control" required>
                        <option value=""></option>
                        @foreach(\App\Models\Place::all()->sortBy('name') as $place)
                            <option value="{{ $place->id }}" @selected($place->id == (isset($appointment) ? $appointment->place_id : request('place_id')))>
                                {{ $place->name }} | {{ $place->description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="date">Дата</label>
                    @if(isset($appointment))
                        <input id="date" type="date" class="form-control" name="date" value="{{ $appointment->start_at->format('Y-m-d') }}" required>
                    @else
                        <input id="date" type="date" class="form-control" name="date"
                               value="{{ (request('date') ? \Carbon\Carbon::parse(request('date'))->format('Y-m-d') : now()->addDay()->floorHour(1)->format('Y-m-d')) }}" required>
                    @endif
                </div>

                <div class="form-group mb-2">
                    <label for="time">Время</label>

                    <select class="form-control" name="time">
                        <option value=""></option>
                        @for($timeStart = now()->startOfDay(); $timeStart < now()->endOfDay(); $timeStart->addMinutes(30))
                            <option
                                value="{{ $timeStart->format('H:i') }}" @selected($timeStart->format('H:i') == $appointment?->start_at?->format('H:i'))>{{ $timeStart->format('H:i') }}</option>
                        @endfor
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="duration">Продолжительность (ч)</label>

                    <select id="duration" name="duration" class="form-control" required>
                        <option value=""></option>
                        @for($i = 30; $i <= 36*30; $i+=30)
                            <option value="{{ $i }}" @selected(isset($appointment) ? $appointment->duration == $i : '')>
                                {{ now()->startOfDay()->addMinutes($i)->format('H:i') }}
                            </option>
                        @endfor
                    </select>

                    <script>
                        $(document).ready(function () {
                            $('#fullDay').on('click', function () {
                                if ($(this).is(':checked')) {
                                    // $('select#duration option:first').prop('selected', true);
                                    $('select#duration').attr('disabled', 'disabled');
                                    $('input#time').attr('disabled', 'disabled');
                                } else {
                                    $('select#duration').removeAttr('disabled');
                                    $('input#time').removeAttr('disabled');
                                }
                            });
                        });
                    </script>
                </div>

                <hr>

                <div class="form-group">
                    @if(isset($appointment))
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    @else
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    @endif
                </div>

            </form>

        </div>

        <div class="col-4">
{{--            @if(isset($appointment) && is_null($appointment->canceled_at))--}}
{{--                <h4>Оплата</h4>--}}

{{--                @if($appointment->user)--}}
{{--                    <h5>Баланс пользователя: {{ number_format($appointment->user->real_balance + $appointment->user->bonus_balance, 2) }}</h5>--}}
{{--                @endif--}}

{{--                <form action="{{ route('admin.appointments.pay', $appointment) }}" method="post">--}}
{{--                    @csrf--}}
{{--                    @method('post')--}}

{{--                    <input type="hidden" name="created_at" value="{{ $appointment->start_at->addMinutes($appointment->duration) }}">--}}

{{--                    <div class="mb-3">--}}
{{--                        <label for="">Расчетная сумма</label>--}}
{{--                        <input class="form-control" type="number" name="amount" value="{{ number_format($appointment->getExpectedPrice(), 2, '.') }}" disabled>--}}
{{--                    </div>--}}

{{--                    @if(is_null($appointment->price))--}}
{{--                        <div class="mb-3">--}}
{{--                            <label for="">Фактическая сумма</label>--}}
{{--                            <input class="form-control" type="number" step="0.01" name="amount" value="{{ number_format($appointment->getExpectedPrice(), 2, '.') }}">--}}
{{--                        </div>--}}
{{--                    @else--}}
{{--                        <div class="mb-3">--}}
{{--                            <label for="">Фактическая сумма</label>--}}
{{--                            <input class="form-control" type="text" value="{{ number_format($appointment->price, 2, '.') }} " disabled>--}}
{{--                        </div>--}}
{{--                    @endif--}}

{{--                    @if(is_null($appointment->price))--}}
{{--                        <div class="form-check mb-3">--}}
{{--                            <input class="form-check-input" type="checkbox" value="on" id="useBalance"--}}
{{--                                   name="use_balance" {{ $appointment->user->getBalance() > 0 ? 'checked' : '' }}>--}}
{{--                            <label class="form-check-label" for="useBalance">--}}
{{--                                Списать с баланса пользователя--}}
{{--                            </label>--}}
{{--                        </div>--}}

{{--                        <button class="btn btn-success" type="submit">Внести оплату</button>--}}
{{--                    @endif--}}
{{--                </form>--}}
{{--            @endif--}}




            @if(isset($appointment))

                <h4>Комментарии ({{ count($appointment->comments) }})</h4>

                <div class="comments__list">
                    @foreach($appointment->comments as $comment)
                        <div class="comment__item">
                            <div class="comment__top d-flex justify-content-between">
                                <div class="comment__date">
                                    {{ $comment->created_at->format('d.m.Y H:i') }}
                                </div>
                                <div class="comment__author">
                                    {{ $comment->user->name }}
                                </div>
                                <div class="comment__delete">
                                    <form action="{{ route('admin.comments.destroy', $comment) }}" method="post">
                                        @csrf
                                        @method('delete')

                                        <button type="submit">[удалить]</button>
                                    </form>
                                </div>
                            </div>
                            <div class="comment__text"><pre class="mb-0">{!! $comment->text !!}</pre></div>

                        </div>
                    @endforeach
                </div>

                <h4 class="mt-5">Оставить комментарий</h4>

                <form action="{{ route('admin.comments.store') }}" method="post">
                    @csrf
                    @method('post')

                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <input type="hidden" name="model_class" value="{{ $appointment::class }}">
                    <input type="hidden" name="model_id" value="{{ $appointment->id }}">
                    <input type="hidden" name="type" value="admin">

                    <textarea class="form-control mb-2" name="text"></textarea>
                    <button type="submit">Добавить</button>
                </form>

            @endif







        </div>

        <div class="col-4">
            @if(isset($appointment) && !$appointment->isPaid() && is_null($appointment->canceled_at))
                <form action="{{ route('admin.appointments.update', $appointment) }}" method="post">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="cancel" value="1">
                    <div class="form-group mb-3">
                        <label for="">Причина отмены</label>
                        <textarea class="form-control" name="cancellation_reason"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <button class="btn btn-danger" type="submit" name="cancel_penalty" value="none">Отменить</button>
{{--                        <button class="btn btn-warning" type="submit" name="cancel_penalty" value="50">Отменить - штраф 50%</button>--}}
{{--                        <button class="btn btn-secondary" type="submit" name="cancel_penalty" value="100">Отменить - штраф 100%</button>--}}
                    </div>
                </form>
            @endif

            @if(isset($appointment) && isset($appointment->canceled_at))
                <form action="{{ route('admin.appointments.update', $appointment) }}" method="post">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="canceled_at" value="">

                    <div class="form-group mb-3">
                        <button class="btn btn-warning" type="submit">Вернуть</button>
                    </div>
                </form>
            @endif


            @if(isset($appointment) && !$appointment->isPaid() && $appointment->canceled_at)
                <form action="{{ route('admin.appointments.destroy', $appointment) }}" method="post" style="float: right;">
                    @csrf
                    @method('delete')
                    <button type="submit">Удалить</button>
                </form>
            @endif
        </div>

    </div>

    @if(isset($appointment))
        <div class="row mt-4">
            <div class="col-12">
                <hr>
                <h3>Управление оплатами</h3>

                @if($appointment->paymentRequirements->count() > 0 && $appointment->leftToPay() == 0)
                    <div class="alert alert-success mb-3">
                        <i class="fa fa-check-circle"></i> Оплачено полностью
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <h4>Платежные требования</h4>

                @if($appointment->paymentRequirements->count() === 0)
                    <form action="{{ route('admin.appointments.payment-requirements.store') }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        <input type="hidden" name="created_at" value="{{ $appointment->created_at->format('Y-m-d H:i:s') }}">
                        <input type="hidden" name="expiration_days" value="30">

                        <div class="input-group">
                            <input type="number" name="amount" id="amount" class="form-control" step="0.01" required value="{{ number_format($appointment->getExpectedPrice(), 2, '.', '') }}" placeholder="Expected: {{ number_format($appointment->getExpectedPrice(), 2) }} BYN">
                            <button type="submit" class="btn btn-primary">Создать</button>
                        </div>
                        <small class="text-muted">Ожидаемая расчетная плата: {{ number_format($appointment->getExpectedPrice(), 2) }} BYN</small>
                    </form>
                @else
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ожидаемая</th>
                            <th>К оплате</th>
                            <th>Остаток</th>
                            <th>Статус</th>
                            <th>Срок</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($appointment->paymentRequirements as $requirement)
                            <tr>
                                <td>{{ $requirement->id }}</td>
                                <td>{{ number_format($requirement->expected_amount, 2) }}</td>
                                <td>{{ number_format($requirement->amount_due, 2) }}</td>
                                <td>
                                    <strong>{{ number_format($requirement->remaining_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $requirement->status === 'paid' ? 'success' : ($requirement->status === 'overdue' ? 'danger' : 'warning') }}">
                                        {{ $requirement->status }}
                                    </span>
                                </td>
                                <td>{{ $requirement->due_date?->format('d.m.Y') }}</td>
                                <td>
                                    @php
                                        $hasCompletedPayments = $appointment->payments->where('status', \App\Models\Payment::STATUS_COMPLETED)->count() > 0;
                                    @endphp
                                    @if($hasCompletedPayments)
                                        <span class="text-muted" title="Нельзя удалить: есть завершенные платежи" style="cursor: help;">🔒</span>
                                    @else
                                        <a href="{{ route('admin.payment-requirements.destroy', $requirement->id) }}" class="text-danger" onclick="return confirm('Удалить требование?')">×</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="3">Итого к оплате:</th>
                            <th colspan="4"><strong>{{ number_format($appointment->leftToPay(), 2) }} BYN</strong></th>
                        </tr>
                        </tfoot>
                    </table>
                @endif
            </div>

            <div class="col-6">
                <h4>Платежи</h4>

                @if($appointment->paymentRequirements->count() > 0 && $appointment->leftToPay() > 0)
                    <form action="{{ route('admin.appointments.payments.store') }}" method="POST" autocomplete="off" class="mb-3">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        <input type="hidden" name="created_at" value="{{ now()->format('Y-m-d H:i:s') }}">

                        <div class="input-group mb-2">
                            <input type="number" name="amount" id="payment_amount" class="form-control" step="0.01" required value="{{ number_format($appointment->leftToPay(), 2, '.', '') }}" placeholder="Amount">
                            <input type="text" name="note" class="form-control" placeholder="Комментарий">

                            <select name="payment_method" id="payment_method" class="form-select" required style="max-width: 150px;">
                                <option value="service">Сервис ЕРИП</option>
                                <option value="cash">Наличные</option>
                            </select>
                            <button type="submit" class="btn btn-success">Добавить</button>
                        </div>
                        <small class="text-muted">Осталось к оплате: {{ number_format($appointment->leftToPay(), 2) }} BYN</small>
                    </form>
                @endif

                @if($appointment->payments->count() > 0)
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Дата</th>
                            <th>Сумма</th>
                            <th>Метод</th>
                            <th>Комментарий</th>
                            <th>Статус</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($appointment->payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_method }}</td>
                                <td>
                                    <small class="text-muted">{{ $payment->note ? Str::limit($payment->note, 30) : '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($payment->status == \App\Models\Payment::STATUS_CANCELLED)
                                        <a href="{{ route('admin.payments.destroy', $payment->id) }}" class="text-danger" onclick="return confirm('Удалить платеж?')" title="Удалить">×</a>
                                    @else
                                        <form action="{{ route('admin.payments.update-status', $payment) }}" method="post" style="display: inline;" onsubmit="return confirm('Отменить платеж?')">
                                            @csrf
                                            @method('patch')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-link btn-sm text-warning p-0" title="Отменить">⊘</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="2">Оплачено:</th>
                            <th colspan="5"><strong>{{ number_format($appointment->payments->where('status', 'completed')->sum('amount'), 2) }} BYN</strong></th>
                        </tr>
                        </tfoot>
                    </table>
                @else
                    @if($appointment->paymentRequirements->count() > 0)
                        <p class="text-muted">Нет платежей</p>
                    @else
                        <div class="text-muted">
                            Для добавления платежей сначала создайте платежное требование в левой колонке
                        </div>
                    @endif
                @endif

{{--                <div class="mt-3">--}}
{{--                    <a href="{{ route('admin.appointments.payments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">--}}
{{--                        Детальное управление →--}}
{{--                    </a>--}}
{{--                </div>--}}
            </div>
        </div>
    @endif
@endsection
