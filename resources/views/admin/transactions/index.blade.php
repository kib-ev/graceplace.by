@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Транзакции</h1>

            <style>
                table td {
                    background: inherit !important;
                }
            </style>

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile"
                            aria-selected="true">deposit
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="false">
                        withdrawal
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">
                        Manual
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    @foreach($transactions->where('type', 'deposit')->groupBy(function ($t) { return $t->created_at->format('Y-m-d'); }) as $date => $transactionGroup)
                        <table class="table table-bordered mb-5">
                            <tr>
                                <td colspan="3">
                                    {{ $date }}
                                </td>
                            </tr>
                            @foreach($transactionGroup as $transaction)
                                <tr style="background: {{ is_null($transaction->deleted_at) ? 'none' : 'red' }}">
                                    <td>
                                        {{ $transaction->id }}
                                    </td>
                                    <td>
                                        {{ $transaction->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td>
                                        {{ $transaction->user->name }}
                                    </td>
                                    <td>
                                        {{ $transaction->description }}
                                    </td>
                                    <td>
                                        {{ $transaction->amount }}
                                    </td>
                                    <td>
                                        {{ $transaction->balance_after }}
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.transactions.destroy', $transaction) }}" method="post">
                                            @csrf
                                            @method('delete')
                                            <button type="submit"><i class="fa fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="4"></td>
                                <td colspan="">
                                    {{ $transactionGroup->whereNull('deleted_at')->sum('amount') }}
                                </td>
                                <td></td>
                            </tr>
                        </table>
                    @endforeach
                </div>
                <div class="tab-pane fade  " id="home" role="tabpanel" aria-labelledby="home-tab">

                    @foreach($transactions->where('type', 'withdrawal')->groupBy(function ($t) { return $t->created_at->format('Y-m-d'); }) as $date => $transactionGroup)
                        <table class="table table-bordered mb-5">
                            <tr>
                                <td colspan="3">
                                    {{ $date }}
                                </td>
                            </tr>
                            @foreach($transactionGroup as $transaction)
                                <tr style="background: {{ is_null($transaction->deleted_at) ? 'none' : 'red' }}">
                                    <td>
                                        {{ $transaction->id }}
                                    </td>
                                    <td>
                                        {{ $transaction->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td>
                                        {{ $transaction->user->name }}
                                    </td>
                                    <td>
                                        {{ $transaction->description }}
                                    </td>
                                    <td>
                                        {{ $transaction->amount }}
                                    </td>
                                    <td>
                                        {{ $transaction->balance_after }}
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.transactions.destroy', $transaction->id) }}" method="post">
                                            @csrf
                                            @method('delete')
                                            <button type="submit"><i class="fa fa-times"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="4"></td>
                                <td colspan="">
                                    {{ $transactionGroup->whereNull('deleted_at')->sum('amount') }}
                                </td>
                                <td></td>
                            </tr>
                        </table>
                    @endforeach

                </div>

                <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                    <div class="row">
                        <div class="col-4">
                            <form action="{{ route('admin.transactions.store') }}" method="post">
                                @csrf
                                @method('post')

                                <div class="form-group mb-2">
                                    <label for="date">Дата</label>
                                    <input type="datetime-local" name="created_at" value="{{ now()->format('Y-m-d H:i') }}">
                                </div>

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
                                    <select class="form-control" name="type">
                                        <option value="deposit">deposit</option>
                                        <option value="withdrawal">withdrawal</option>
                                    </select>
                                </div>


                                <div class="form-group mb-2">
                                    <label for="date">Сумма</label>
                                    <input class="form-control" type="number" name="amount">
                                </div>

                                <div class="form-group mb-2">
                                    <label for="depositWithBonus">Начислить бонусы</label>
                                    <input id="depositWithBonus" type="checkbox" name="deposit_with_bonus" value="on">
                                </div>

                                <textarea name="description">Пополнение баланса</textarea>

                                <button type="submit">Пополнить баланс</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
