@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Places</h1>

            <hr>
            <a href="{{ route('admin.rents.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered">
                <tr>
                    <th></th>
                    <th>Наименование</th>
                    <th>Описание</th>
                    <th>Цена за час</th>
                    <th></th>
                </tr>
                @foreach($rents as $rent)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td><a href="{{ route('admin.rents.show', $rent) }}">id: {{ $rent->id }}</a></td>
                        <td>{{ $rent->description }}</td>
                        <td>{{ $rent->price_hour }}</td>
                        <td><a href="{{ route('admin.rents.edit', $rent) }}">edit</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
