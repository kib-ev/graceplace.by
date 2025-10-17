@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>История цен: {{ $place->name }}</h1>
            <div>
                <a href="{{ route('admin.places.index') }}" class="btn btn-secondary">Назад к списку</a>
                <a href="{{ route('admin.places.prices.create', $place) }}" class="btn btn-primary">Добавить цену</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Цена за час</th>
                    <th>Действует с</th>
                    <th>Статус</th>
                    <th>Создано</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $activePriceId = $place->prices()
                        ->where('effective_from', '<=', now())
                        ->orderBy('effective_from', 'desc')
                        ->first()?->id;
                @endphp
                @forelse($prices as $price)
                    @php
                        $isActive = $price->id === $activePriceId;
                        $isFuture = $price->effective_from->isFuture();
                    @endphp
                    <tr class="{{ $isFuture ? 'table-info' : ($isActive ? 'table-success' : '') }}">
                        <td>{{ $price->id }}</td>
                        <td>{{ number_format($price->price_per_hour, 2) }} BYN</td>
                        <td>{{ $price->effective_from->format('d.m.Y') }}</td>
                        <td>
                            @if($isFuture)
                                <span class="badge bg-info">Запланировано</span>
                            @elseif($isActive)
                                <span class="badge bg-success">Активно</span>
                            @else
                                <span class="badge bg-secondary">Прошлое</span>
                            @endif
                        </td>
                        <td>{{ $price->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.places.prices.edit', [$place, $price]) }}" class="btn btn-sm btn-warning">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.places.prices.destroy', [$place, $price]) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            Нет истории цен. Пожалуйста, добавьте запись о цене.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $prices->links() }}
    </div>
@endsection

