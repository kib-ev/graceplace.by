@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Оплаты</h1>

            <hr>

            @foreach(\App\Models\Payment::orderByDesc('created_at')->get()->groupBy(function ($payment) { return $payment->created_at->format('Y-m-d'); }) as $date => $paymentGroup)

                <div class="row">
                    <div class="col-8">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td colspan="10" style="font-size: 1.4em;">{{ $date }}</td>
                            </tr>
                            @foreach($paymentGroup as $payment)
                                <tr>
                                    <td>ID: {{ $payment->id }}</td>
                                    <td>
                                        {{ $payment->created_at->format('Y-m-d H:i') }}
                                    </td>

                                    <td>
                                        @if($payment->payable_type == \App\Models\Appointment::class)
                                            @php
                                                $appointment = \App\Models\Appointment::find($payment->payable_id);
                                            @endphp

                                            @if($appointment)
                                                <i class="fa fa-star"></i> <a href="{{ route('admin.appointments.edit', $appointment) }}">Запись</a>
                                                | {{ $appointment->user->name }}
                                            @endif
                                        @endif

                                        @if($payment->payable_type == \App\Models\StorageBooking::class)
                                            @php
                                                $storageBooking = \App\Models\StorageBooking::find($payment->payable_id);
                                            @endphp

                                            @if($storageBooking)
                                                <i class="fa fa-square"></i> <a href="{{ route('admin.storage-bookings.edit', $storageBooking) }}">Ячейка</a>
                                                | {{ $storageBooking->user->name }}
                                            @endif
                                        @endif

                                    </td>
                                    <td style="text-align: right;">{{ $payment->amount }}</td>
                                    <td style="text-align: right;">{{ $payment->payment_method }}</td>
                                    <td>
                                        <form action="{{ route('admin.payments.update-status', $payment) }}" method="post">
                                            @method('patch')
                                            @csrf
                                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                            <select name="status" id="cash">
                                                @foreach(\App\Models\Payment::getPaymentStatuses() as $statusValue => $statusName)
                                                    <option value="{{ $statusValue }}" @selected($payment->status == $statusValue)>{{ $statusName }}</option>
                                                @endforeach
                                            </select>

                                            <button type="submit"><span class="fa fa-save"></span></button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($payment->status == \App\Models\Payment::STATUS_CANCELLED)
                                            <a href="{{ route('admin.payments.destroy', $payment->id) }}">удалить</a>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $payment->deleted_at?->format('d.m.Y') }}
                                    </td>

                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="4" style="text-align: right;">{{ $paymentGroup->sum('amount') }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>

                    </div>
                    <div class="col-4">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endforeach


        </div>

    </div>
@endsection

