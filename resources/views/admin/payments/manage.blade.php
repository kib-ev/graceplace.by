@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>Управление платежами</h1>
            <p>
                @if($payable instanceof \App\Models\Appointment)
                    <a href="{{ route('admin.appointments.edit', $payable) }}">← К записи на рабочее место</a>
                @else
                    <a href="{{ route('admin.storage-bookings.edit', $payable) }}">← К брони ячейки</a>
                @endif
            </p>
            <p class="mb-0"><strong>{{ $payable->getPaymentContextLabel() }}</strong></p>
            <hr>

            <p class="text-muted">
                Ожидаемая: {{ number_format($payable->getExpectedTotal(), 2, '.') }} BYN
                | Остаток: {{ number_format($payable->leftToPay(), 2, '.') }} BYN
                | {{ $payable->isPaid() ? 'Оплачено' : 'Не оплачено' }}
            </p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show">
                    @foreach($errors->all() as $error)
                        <strong>{{ $error }}</strong><br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header"><strong>Создать требование на оплату</strong></div>
                <div class="card-body">
                    <form action="{{ route('admin.payables.payment-requirements.store') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="payable_type" value="{{ get_class($payable) }}">
                        <input type="hidden" name="payable_id" value="{{ $payable->id }}">
                        <div class="col-md-2">
                            <label class="form-label">Сумма</label>
                            <input type="number" name="amount" class="form-control form-control-sm" step="0.01" required min="0"
                                   value="{{ number_format($payable->getExpectedAmount(), 2, '.', '') }}">
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
                        @forelse($payable->paymentRequirements as $req)
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

            @php $hasUnpaidRequirements = $payable->paymentRequirements->isNotEmpty() && $payable->leftToPay() > 0; @endphp
            <div class="card mb-3" id="payments">
                <div class="card-header"><strong>Новый платеж</strong>@if(!$hasUnpaidRequirements) <span class="text-muted">(требуются незакрытые платежные требования)</span>@endif</div>
                <div class="card-body">
                    <form action="{{ route('admin.payables.payments.store') }}" method="POST" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="payable_type" value="{{ get_class($payable) }}">
                        <input type="hidden" name="payable_id" value="{{ $payable->id }}">
                        <div class="col-md-2">
                            <label class="form-label">Дата</label>
                            <input type="datetime-local" name="created_at" class="form-control form-control-sm" required
                                   value="{{ now()->format('Y-m-d\TH:i') }}" @disabled(!$hasUnpaidRequirements)>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Сумма</label>
                            <input type="number" name="amount" class="form-control form-control-sm @error('amount') is-invalid @enderror" step="0.01" required min="0.01"
                                   max="{{ $hasUnpaidRequirements ? $payable->leftToPay() : 0 }}"
                                   value="{{ old('amount', $payable->leftToPay()) }}" @disabled(!$hasUnpaidRequirements)>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Метод</label>
                            <select name="payment_method" class="form-control form-control-sm" required @disabled(!$hasUnpaidRequirements)>
                                @foreach(\App\Models\Payment::getPaymentMethods() as $v => $n)
                                    <option value="{{ $v }}">{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Примечание</label>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="Необязательно" @disabled(!$hasUnpaidRequirements)>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm" @disabled(!$hasUnpaidRequirements)>Провести оплату</button>
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
                        @foreach($payable->payments as $payment)
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
@endsection
