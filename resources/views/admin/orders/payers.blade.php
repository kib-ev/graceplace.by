@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Плательщики E-POS</h1>

            <hr>

            <p>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">← К счетам и платежам</a>
            </p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(isset($fallbackNotice))
                <div class="alert alert-info">{{ $fallbackNotice }}</div>
            @endif

            @if(empty($payers))
                <div class="alert alert-info">Список плательщиков пуст</div>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Наименование</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Информация</th>
                            <th>Viber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payers as $payer)
                            <tr>
                                <td>{{ $payer['payerId'] ?? '-' }}</td>
                                <td>{{ $payer['payerName'] ?? '-' }}</td>
                                <td>{{ $payer['email'] ?? '-' }}</td>
                                <td>{{ $payer['phone'] ?? '-' }}</td>
                                <td>{{ Str::limit($payer['payerInformation'] ?? '-', 50) }}</td>
                                <td>{{ $payer['viberPermissionName'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
