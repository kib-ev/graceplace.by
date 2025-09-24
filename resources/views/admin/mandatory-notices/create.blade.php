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
                        <input type="text" name="title" class="form-control" value="{{ old('title', 'Уведомление') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Текст</label>
                        <textarea name="body" class="form-control" rows="6" required>{{ old('body') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Старт показа</label>
                            <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', now()->format('Y-m-d\\TH:i')) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Срок жизни (дней)</label>
                            <input type="number" name="days_to_live" class="form-control" min="1" max="3650" value="{{ old('days_to_live', 30) }}">
                        </div>
                        <div class="col-md-4 mb-3 form-check mt-4">
                            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
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
                    <div class="mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="audience_mode" id="aud_specific" value="specific" {{ old('audience_mode') === 'specific' ? 'checked' : '' }}>
                            <label class="form-check-label" for="aud_specific">Только указанные пользователи</label>
                        </div>
                    </div>

                    <div id="specificUsersBlock" style="{{ old('audience_mode') === 'specific' ? '' : 'display: none;' }}">
                        <div class="mb-2">
                            <input type="text" class="form-control" placeholder="Быстрый поиск по имени или email..." oninput="filterMasters(this.value)">
                        </div>

                        <div class="border rounded p-2" style="max-height: 320px; overflow: auto;">
                            @foreach(($masters ?? collect()) as $u)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="user_ids[]" id="user_{{ $u->id }}" value="{{ $u->id }}"
                                        {{ in_array($u->id, old('user_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="user_{{ $u->id }}">
                                        {{ $u->name }} <span class="text-muted">({{ $u->email }}, #{{ $u->id }})</span>
                                    </label>
                                </div>
                            @endforeach
                            @if(($masters ?? collect())->isEmpty())
                                <div class="text-muted">Нет активных мастеров.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Создать</button>
            <a href="{{ route('admin.mandatory-notices.index') }}" class="btn btn-outline-secondary">Отмена</a>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const audAll = document.getElementById('aud_all');
            const audSpec = document.getElementById('aud_specific');
            const block = document.getElementById('specificUsersBlock');

            function toggle() {
                block.style.display = audSpec.checked ? '' : 'none';
            }
            audAll.addEventListener('change', toggle);
            audSpec.addEventListener('change', toggle);
        });

        function filterMasters(query) {
            query = (query || '').toLowerCase();
            document.querySelectorAll('#specificUsersBlock .form-check').forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }
    </script>
@endsection
