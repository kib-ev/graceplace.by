@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Masters</h1>

            <hr>
            <a href="{{ route('public.masters.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered">
                @foreach($masters as $master)
                    <tr>
                        <td style="width: 50px;">{{ $master->id }}</td>
                        <td style="width: 400px;"><a href="{{ route('public.masters.show', $master) }}">{{ $master->person->first_name }} {{ $master->person->last_name }}</a></td>
                        <td style="width: 200px;">{{ $master->person->phones->first()?->number }}</td>
                        <td>{{ $master->description }}</td>
                        <td>{{ $master->person->birth_date }}</td>
                        <td><a href="{{ route('public.masters.edit', $master) }}">edit</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
