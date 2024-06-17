@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Places</h1>

            <hr>
            <a href="{{ route('public.places.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered">
                @foreach($places as $place)
                    <tr>
                        <td>{{ $place->id }}</td>
                        <td><a href="{{ route('public.places.show', $place) }}">{{ $place->name }}</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
