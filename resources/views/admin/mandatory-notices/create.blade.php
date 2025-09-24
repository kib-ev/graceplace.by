@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-3">Создание уведомления</h3>

        <form method="POST" action="{{ route('admin.mandatory-notices.store') }}">
            @csrf

            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Заголовок</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Текст</label>
                        <textarea name="body" class="form-control" rows="6" required>{{ old('body') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Старт показа</label>
                            <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Срок жизни (дней)</label>
                            <input type="number" name="days_to_live" class="form-control" min="1" max="3650" value="{{ old('days_to_live') }}">
                        </div>
                        <div class="col-md-4 mb-3 form-check mt-4">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active') ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Активно</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Аудитория</div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="audience_mode" id="aud_all" value="all_masters" {{ old('audience_mode', 'all_masters') === 'all_masters' ? 'checked' : '' }}>
                            <label class="form-check-label" for="aud_all">Все пользователи с ролью master</label>
                        </div>
                    </div>
                    <div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="audience_mode" id="aud_specific" value="specific" {{ old('audience_mode') === 'specific' ? 'checked' : '' }}>
                            <label class="form-check-label" for="aud_specific">Только указанные пользователи</label>
                        </div>
                        <textarea name="user_ids" class="form-control" rows="3" placeholder="Перечислите ID пользователей через запятую или пробелы">{{ old('user_ids') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Создать</button>
            <a href="{{ route('admin.mandatory-notices.index') }}" class="btn btn-outline-secondary">Отмена</a>
        </form>
    </div>
@endsection
