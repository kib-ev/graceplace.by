@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h1>Создать категорию услуг</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.service-categories.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Название *</label>
                        <input type="text" name="name" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sort" class="form-label">Сортировка *</label>
                        <input type="number" name="sort" id="sort" min="0"
                               class="form-control @error('sort') is-invalid @enderror"
                               value="{{ old('sort', 0) }}" required>
                        @error('sort')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Порядок отображения (меньше — выше)</small>
                    </div>

                    <div class="mb-3">
                        <label for="keywords" class="form-label">Ключевые слова</label>
                        <textarea name="keywords" id="keywords" rows="6"
                                  class="form-control @error('keywords') is-invalid @enderror"
                                  placeholder="Одно слово или фраза на строке. Используются для автоподбора категории по описанию мастера.">{{ old('keywords') }}</textarea>
                        @error('keywords')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">По одному на строку или через запятую. Подстрока ищется в описании мастера (регистр не важен). Символ * — любое количество любых символов, напр. реконструкц* волос.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.service-categories.index') }}" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">Создать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
