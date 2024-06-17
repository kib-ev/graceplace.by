@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Master</h1>
            <a href="{{ route('public.masters.index') }}">Masters</a>
            <hr>
            <table class="table table-bordered">
                <tr>
                    <td>{{ $master->id }}</td>
                </tr>
                <tr>
                    <td>{{ $master->person->first_name }} {{ $master->person->last_name }}</td>
                </tr>
                <tr>
                    <td>{{ $master->person->birth_date }}</td>
                </tr>
                <tr>
                    <td>{{ $master->person->phones->first()?->number }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
