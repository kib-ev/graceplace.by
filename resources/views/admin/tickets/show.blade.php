@extends('admin.layouts.app')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <h2>Просмотр заявки #{{ $ticket->id }}</h2>

        <div class="mb-3">
            <strong>Заголовок:</strong> {{ $ticket->title }}
        </div>
        <div class="mb-3">
            <strong>Описание:</strong> {{ $ticket->description }}
        </div>
        <div class="mb-3">
            <strong>Категория:</strong> {{ $ticket->category }}
        </div>
        <div class="mb-3">
            <strong>Приоритет:</strong> {{ ucfirst($ticket->priority) }}
        </div>
        <div class="mb-3">
            <strong>Статус:</strong> {{ ucfirst($ticket->status) }}
        </div>
        <div class="mb-3">
            <strong>Дата создания:</strong> {{ $ticket->created_at->format('d-m-Y H:i') }}
        </div>

        <div class="mb-3">
            <strong>Фото:</strong>
            @foreach ($ticket->attachments as $attachment)
                <img src="{{ asset('storage/' . $attachment->file_path) }}" class="img-thumbnail" width="150">
            @endforeach
        </div>

        <div class="mb-3">
            <div class="comments">
                @include('admin.comments.includes.widget', ['model' => $ticket, 'title' => 'Комментарий', 'type' => 'admin'])
            </div>
        </div>




        <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary">Назад к списку</a>
    </div>
@endsection
