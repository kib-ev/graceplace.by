@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Appointment</h1>
            <a href="{{ route('admin.appointments.index') }}">appointments</a>
            <hr>
            <table class="table table-bordered">
                <tr>
                    <td>{{ $appointment->id }}</td>
                </tr>
                <tr>
                    <td>{{ $appointment->master->full_name }}</td>
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
