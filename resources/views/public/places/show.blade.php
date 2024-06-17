@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Place</h1>
            <hr>
            <table class="table table-bordered">
                <tr>
                    <td>{{ $place->id }}</td>
                </tr>
                <tr>
                    <td>{{ $place->name }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h1>Appointments</h1>
            <hr>

            <form action="">
                <input type="date" width="276" name="date_from" autocomplete="off" value="{{ request('date_from') }}">
                <input type="date" width="276" name="date_to" autocomplete="off" value="{{ request('date_to') }}">

                <button type="submit">Select</button>
            </form>

            <hr>

            <h1>{{ \Carbon\Carbon::parse(request('date_from'))->format('D, d M Y') }} - {{ \Carbon\Carbon::parse(request('date_to'))->format('D, d M Y') }}</h1>

            <hr>

            <table class="table table-bordered">
                @foreach(\App\Models\Appointment::whereBetween('date', [\Carbon\Carbon::parse(request('date_from')), \Carbon\Carbon::parse(request('date_to'))])->get() as $appointment)
                    <tr>
                        <td>{{ $appointment->date?->format('d.m.Y') }}</td>

                        <td>
                            @if(isset($appointment->date))
                                {{ $appointment->date?->format('H:i') }} -
                                {{ $appointment->date->addMinutes($appointment->duration)?->format('H:i') }}
                            @endif
                        </td>

                        <td>{{ $appointment->master?->full_name }}</td>
                    </tr>
                @endforeach
            </table>



        </div>
    </div>

@endsection
