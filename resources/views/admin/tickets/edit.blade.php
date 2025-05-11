@extends('admin.layouts.app')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <h2>Редактировать заявку</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Ошибки:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="title" class="form-label">Заголовок *</label>
                <input type="text" name="title" class="form-control" required value="{{ old('title', $ticket->title) }}">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Описание</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $ticket->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Категория</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $ticket->category) }}">
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Приоритет</label>
                <select name="priority" class="form-select">
                    <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Низкий</option>
                    <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Средний</option>
                    <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>Высокий</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="photos" class="form-label">Фото (при необходимости)</label>
                <input type="file" name="photos[]" class="form-control" multiple>
            </div>

            <div class="mb-3">
                <label for="priority" class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>open</option>
                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>in_progress</option>
                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>resolved</option>
                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>closed</option>
                </select>
            </div>

            <button type="submit" class="btn btn-warning">Обновить заявку</button>
        </form>
    </div>
@endsection
