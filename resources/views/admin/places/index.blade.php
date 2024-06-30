@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Places</h1>

            <hr>
            <a href="{{ route('admin.places.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered">
                @foreach($places->sortBy('name') as $place)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td><a href="{{ route('admin.places.show', $place) }}">{{ $place->name }}</a></td>
                        <td>{{ $place->description }}</td>
                        <td><a href="{{ route('admin.places.edit', $place) }}">edit</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
