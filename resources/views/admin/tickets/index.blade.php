@extends('admin.layouts.app')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
@endsection


@section('content')
    <div class="container">
        <h2>Управление заявками</h2>

        <!-- Сообщение об успешном выполнении действия -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-3">
            <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Создать заявку
            </a>
        </div>

        <!-- Таблица тикетов -->
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Заголовок</th>
                <th scope="col">Приоритет</th>
                <th scope="col">Статус</th>
                <th scope="col">Дата создания</th>
                <th scope="col">Действия</th>
            </tr>
            </thead>
            <tbody>

            <tr>
                <th colspan="8" class="bg-secondary text-white">
                    Актуальные
                </th>
            </tr>

            @foreach ($tickets->where('status', '<>', 'resolved') as $ticket)
                <tr>
                    <td>{{ $ticket->id }}</td>
                    <td style="width: 540px;">
                        <div class="mb-2"><b>{{ $ticket->title }}</b></div>


                        <div style="padding: 5px 10px;">
                            {{ $ticket->description }}
                        </div>

                        @include('admin.comments.includes.widget', ['model' => $ticket, 'title' => '', 'type' => 'admin', 'showForm' => false, 'showControl' => false])


                    </td>
                    <td>{{ ucfirst($ticket->priority) }}</td>
                    <td>
                        @if($ticket->status == 'open')
                            <span class="text-bg-primary p-1">{{ ucfirst($ticket->status) }}</span>
                        @elseif($ticket->status == 'closed')
                            <span class="text-bg-danger p-1">{{ ucfirst($ticket->status) }}</span>
                        @elseif($ticket->status == 'in_progress')
                            <span class="text-bg-success p-1">{{ ucfirst($ticket->status) }}</span>
                        @else
                            <span class="text-bg-secondary p-1">{{ ucfirst($ticket->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $ticket->created_at->format('d-m-Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i> Просмотр
                        </a>
                        <a href="{{ route('admin.tickets.edit', $ticket->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Редактировать
                        </a>
                        <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить этот тикет?')">
                                <i class="bi bi-trash"></i> Удалить
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            <tr>
                <th colspan="8" class="bg-secondary text-white">
                    Завершенные
                </th>
            </tr>
            @foreach ($tickets->where('status', 'resolved') as $ticket)
                <tr>
                    <td>{{ $ticket->id }}</td>
                    <td style="width: 540px;">
                        <div class="mb-2"><b>{{ $ticket->title }}</b></div>
                        <div style="padding: 5px 10px;">
                            {{ $ticket->description }}
                        </div>
                        @include('admin.comments.includes.widget', ['model' => $ticket, 'title' => '', 'type' => 'admin', 'showForm' => false, 'showControl' => false])
                    </td>
                    <td>{{ ucfirst($ticket->priority) }}</td>
                    <td>
                        @if($ticket->status == 'open')
                            <span class="text-bg-primary p-1">{{ ucfirst($ticket->status) }}</span>
                        @elseif($ticket->status == 'closed')
                            <span class="text-bg-danger p-1">{{ ucfirst($ticket->status) }}</span>
                        @elseif($ticket->status == 'in_progress')
                            <span class="text-bg-success p-1">{{ ucfirst($ticket->status) }}</span>
                        @else
                            <span class="text-bg-secondary p-1">{{ ucfirst($ticket->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $ticket->created_at->format('d-m-Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn btn-info btn-sm">
                            <i class="bi bi-eye"></i> Просмотр
                        </a>
                        <a href="{{ route('admin.tickets.edit', $ticket->id) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Редактировать
                        </a>
                        <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены, что хотите удалить этот тикет?')">
                                <i class="bi bi-trash"></i> Удалить
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <!-- Пагинация -->
        {{ $tickets->links('pagination::bootstrap-5') }}
    </div>
@endsection
