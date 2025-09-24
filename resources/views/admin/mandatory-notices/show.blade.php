@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Уведомление #{{ $notice->id }}</h3>
            <form method="POST" action="{{ route('admin.mandatory-notices.destroy', $notice) }}" onsubmit="return confirm('Удалить уведомление?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger">Удалить</button>
            </form>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-2"><strong>Заголовок:</strong> {{ $notice->title }}</div>
                <div class="mb-2"><strong>Текст:</strong><br>{!! nl2br(e($notice->body)) !!}</div>
                <div class="row">
                    <div class="col-md-3 mb-2"><strong>Старт:</strong> {{ optional($notice->starts_at)->format('d.m.Y H:i') }}</div>
                    <div class="col-md-3 mb-2"><strong>Истекает:</strong> {{ optional($notice->expires_at)->format('d.m.Y H:i') ?? '—' }}</div>
                    <div class="col-md-3 mb-2"><strong>Активно:</strong> {{ $notice->is_active ? 'Да' : 'Нет' }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Получатели ({{ $notice->recipients_confirmed }}/{{ $notice->recipients_total }})
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Подтверждено</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recipients as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ optional($user->confirmed_at)->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $recipients->links() }}
            </div>
        </div>
    </div>
@endsection
