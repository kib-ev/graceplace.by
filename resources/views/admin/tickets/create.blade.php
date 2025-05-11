@extends('admin.layouts.app')

@section('style')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <h2>Создать заявку</h2>

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

        <form action="{{ route('admin.tickets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label">Заголовок *</label>
                <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Описание</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>

{{--            <div class="mb-3">--}}
{{--                <label for="category" class="form-label">Категория</label>--}}
{{--                <input type="text" name="category" class="form-control" value="{{ old('category') }}">--}}
{{--            </div>--}}

            <div class="mb-3">
                <label for="priority" class="form-label">Приоритет</label>
                <select name="priority" class="form-select">
                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Низкий</option>
                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Средний</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Высокий</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="photos" class="form-label">Фото</label>
                <input type="file" name="photos[]" class="form-control" multiple>
            </div>

            <button type="submit" class="btn btn-primary">Создать заявку</button>
        </form>
    </div>
@endsection
