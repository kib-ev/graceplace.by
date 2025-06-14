@extends('public.layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    @if($master->image_path)
                        <img src="{{ $master->image_path }}" class="img-fluid rounded mb-3" alt="{{ $master->person->full_name }}">
                    @endif
                    <h1 class="h3">{{ $master->person->full_name }}</h1>
                    
                    @if($master->description)
                        <p class="text-muted">{{ $master->description }}</p>
                    @endif

                    @if($master->instagram)
                        <p>
                            <a href="https://instagram.com/{{ $master->instagram }}" target="_blank" class="text-decoration-none">
                                <i class="fab fa-instagram"></i> {{ $master->instagram }}
                            </a>
                        </p>
                    @endif

                    @if($master->direct)
                        <p>
                            <a href="{{ $master->direct }}" target="_blank" class="btn btn-outline-primary">
                                <i class="fab fa-instagram"></i> Написать в Direct
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="h4 mb-4">Записаться на прием</h2>

                    <form action="{{ route('public.appointments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="master_id" value="{{ $master->id }}">

                        <div class="mb-3">
                            <label for="date" class="form-label">Дата</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   min="{{ now()->format('Y-m-d') }}" 
                                   value="{{ old('date', request('date', now()->format('Y-m-d'))) }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="time" class="form-label">Время</label>
                            <select class="form-control" id="time" name="time" required>
                                <option value="">Выберите время</option>
                                @for($hour = 9; $hour <= 21; $hour++)
                                    @for($minute = 0; $minute < 60; $minute += 30)
                                        <option value="{{ sprintf('%02d:%02d', $hour, $minute) }}">
                                            {{ sprintf('%02d:%02d', $hour, $minute) }}
                                        </option>
                                    @endfor
                                @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="duration" class="form-label">Длительность (в минутах)</label>
                            <select class="form-control" id="duration" name="duration" required>
                                <option value="60">1 час</option>
                                <option value="90">1.5 часа</option>
                                <option value="120">2 часа</option>
                                <option value="150">2.5 часа</option>
                                <option value="180">3 часа</option>
                                <option value="210">3.5 часа</option>
                                <option value="240">4 часа</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="place_id" class="form-label">Рабочее место</label>
                            <select class="form-control" id="place_id" name="place_id" required>
                                <option value="">Выберите рабочее место</option>
                                @foreach(\App\Models\Place::all() as $place)
                                    <option value="{{ $place->id }}" data-price="{{ $place->price_per_hour }}">
                                        {{ $place->name }} ({{ $place->price_per_hour }} BYN/час)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="client_name" class="form-label">Ваше имя</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="client_phone" class="form-label">Ваш телефон</label>
                            <input type="tel" class="form-control" id="client_phone" name="client_phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="total_price" class="form-label">Итоговая стоимость</label>
                            <input type="text" class="form-control" id="total_price" readonly>
                        </div>

                        <button type="submit" class="btn btn-primary">Записаться</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const durationSelect = document.getElementById('duration');
    const placeSelect = document.getElementById('place_id');
    const totalPriceInput = document.getElementById('total_price');

    function calculateTotalPrice() {
        const duration = parseInt(durationSelect.value) / 60; // Convert to hours
        const placeOption = placeSelect.selectedOptions[0];
        
        if (placeOption && placeOption.dataset.price) {
            const pricePerHour = parseFloat(placeOption.dataset.price);
            const total = duration * pricePerHour;
            totalPriceInput.value = total.toFixed(2) + ' BYN';
        } else {
            totalPriceInput.value = '';
        }
    }

    durationSelect.addEventListener('change', calculateTotalPrice);
    placeSelect.addEventListener('change', calculateTotalPrice);
});
</script>
@endpush
@endsection
