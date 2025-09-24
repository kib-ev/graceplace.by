@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Уведомления</h3>
            <a href="{{ route('admin.mandatory-notices.create') }}" class="btn btn-primary">Создать</a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заголовок</th>
                        <th>Старт</th>
                        <th>Истекает</th>
                        <th>Активно</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($notices as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->title }}</td>
                            <td>{{ optional($item->starts_at)->format('d.m.Y H:i') }}</td>
                            <td>{{ optional($item->expires_at)->format('d.m.Y H:i') ?? '—' }}</td>
                            <td>{{ $item->is_active ? 'Да' : 'Нет' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.mandatory-notices.show', $item) }}" class="btn btn-sm btn-outline-secondary">Открыть</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $notices->links() }}
            </div>
        </div>
    </div>
@endsection
