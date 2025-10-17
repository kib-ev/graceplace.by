@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Places</h1>

            <hr>
            <a href="{{ route('admin.places.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered">
                <tr>
                    <th></th>
                    <th>Сортировка</th>
                    <th>Наименование</th>
                    <th>Описание</th>
                    <th>Цена за час</th>
                    <th>Средняя выручка в месяц (3 послед. мес.)</th>
                    <th>Среднее количество часов аренды в день</th>
                    <th></th>
                </tr>
                @foreach($places->sortBy('sort') as $place)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td class="text-end">{{ $place->sort }}</td>
                        <td><a href="{{ route('admin.places.show', $place) }}">{{ $place->name }}</a></td>
                        <td>{{ $place->description }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.places.prices.index', $place) }}" title="Manage prices">
                                {{ number_format($place->getCurrentPrice(), 2) }}
                            </a>
                        </td>
                        <td class="text-end">{{ number_format($place->getAverageProfitPerMonth(), 2) }}</td>
                        <td class="text-end">{{ $place->getAverageRentHoursPerDay() }}</td>
                        <td><a href="{{ route('admin.places.edit', $place) }}"><i class="fa fa-edit"></i></a></td>
                    </tr>
                @endforeach

                <tr>
                    <th>-</th>
                    <th class="text-end">-</th>
                    <th>-</th>
                    <th>-</th>
                    <th class="text-end">-</th>
                    <th class="text-end">{{ number_format($places->sum(function ($place) { return $place->getAverageProfitPerMonth(); }), 2) }}</th>
                    <th class="text-end">{{ number_format($places->sum(function ($place) { return $place->getAverageRentHoursPerDay(); }) / count($places), 2) }}</th>
                    <th>-</th>
                </tr>
            </table>

            Сегодня: {{ now()->format('d.m.Y') }}
        </div>
    </div>
@endsection
