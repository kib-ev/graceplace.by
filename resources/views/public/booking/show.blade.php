<!DOCTYPE html>
<html>
<head>
    <title>Запись к {{ $master->full_name }}</title>
    <style>
        .slot { display: inline-block; padding: 10px; margin: 5px; border: 1px solid #ccc; cursor: pointer; }
        .busy { background-color: #f8d7da; cursor: not-allowed; }
        .free { background-color: #d4edda; }
    </style>
</head>
<body>

<h2>Запись к мастеру {{ $master->full_name }}</h2>
<p>Рабочее место: {{ $place->name ?? 'Не указано' }}</p>

@if (session('success'))
    <p style="color: green">{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('booking.reserve', $master) }}">
    @csrf
    <label for="client_name">Ваше имя:</label>
    <input type="text" name="client_name" required>

    <label for="start_at">Выберите время:</label>
    <select name="start_at" required>
        @foreach ($slots as $slot)
            @if ($slot['available'])
                <option value="{{ $slot['time'] }}">{{ $slot['time']->format('d.m H:i') }}</option>
            @endif
        @endforeach
    </select>

    <button type="submit">Забронировать</button>
</form>

<h3>Доступные слоты:</h3>
@foreach ($slots as $slot)
    <div class="slot {{ $slot['available'] ? 'free' : 'busy' }}">
        {{ $slot['time']->format('d.m H:i') }}
    </div>
@endforeach

</body>
</html>
