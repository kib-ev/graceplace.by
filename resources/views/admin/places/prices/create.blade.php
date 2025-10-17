@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h1>Добавить цену для {{ $place->name }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.places.prices.store', $place) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="price_per_hour" class="form-label">Цена за час *</label>
                        <input type="number" 
                               step="0.01" 
                               name="price_per_hour" 
                               id="price_per_hour" 
                               class="form-control @error('price_per_hour') is-invalid @enderror" 
                               value="{{ old('price_per_hour', $place->getCurrentPrice()) }}" 
                               required>
                        @error('price_per_hour')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="effective_from" class="form-label">Действует с *</label>
                        <input type="date" 
                               name="effective_from" 
                               id="effective_from" 
                               class="form-control @error('effective_from') is-invalid @enderror" 
                               value="{{ old('effective_from', now()->format('Y-m-d')) }}" 
                               required>
                        @error('effective_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Дата, с которой будет применяться эта цена. Можно указывать будущие даты для планирования.
                        </small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.places.prices.index', $place) }}" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

