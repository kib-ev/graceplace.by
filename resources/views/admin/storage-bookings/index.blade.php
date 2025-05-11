@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Places</h1>

            <hr>
            <a href="{{ route('admin.storage-bookings.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered">
                <tr>
                    <th></th>
                    <th>Наименование</th>
                    <th>Описание</th>
                    <th>Цена за час</th>
                    <th></th>
                </tr>
                @foreach($storageBookings as $booking)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td><a href="{{ route('admin.storage-bookings.show', $rent) }}">id: {{ $booking->id }}</a></td>
                        <td>{{ $booking->description }}</td>
                        <td>{{ $booking->price_hour }}</td>
                        <td><a href="{{ route('admin.storage-bookings.edit', $booking) }}">edit</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
