@extends('app')


@section('container')
    <div class="row">
        <div class="col">
            <h1>Appointments</h1>

            <hr>
            <a href="{{ route('public.appointments.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <div class="mb-3 text-right">
            @if(request('place_id'))
                <a class="text-danger" href="{{ route('public.appointments.index') }}">{{ \App\Models\Place::find(request('place_id'))?->name }} (X)</a>
            @endif

            @if(request('master_id'))
                <a class="text-danger" href="{{ route('public.appointments.index') }}">{{ \App\Models\Master::find(request('master_id'))?->full_name }} (X)</a>
            @endif
            </div>

            <table class="table table-bordered">
                @foreach($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->id }}</td>

                        <td>{{ $appointment->date?->format('d.m.Y') }}</td>

                        <td>
                            @if(isset($appointment->date))
                                {{ $appointment->date?->format('H:i') }} -
                                {{ $appointment->date->addMinutes($appointment->duration)?->format('H:i') }}
                            @endif
                        </td>

                        <td>
                            @if(isset($appointment->master))

                                <a href=""><a href="?master_id={{ $appointment->master_id }}">{{ $appointment->master->full_name }}</a></a>


{{--                                <a href="{{ route('public.masters.show', $appointment->master) }}">{{ $appointment->master->full_name }}</a>--}}
                            @endif

{{--                            @if($appointment->client)--}}
{{--                                <br>--}}
{{--                                {{ $appointment->client->person->first_name }}  {{ $appointment->master->person->last_name }}--}}
{{--                            @endif--}}
                        </td>


                        <td>
                            @if(isset($appointment->place))
                                <a href="?place_id={{ $appointment->place_id }}">{{ $appointment->place->name }}</a>
{{--                                <a href="{{ route('public.places.show', $appointment->place) }}?date_from={{ now()->format('Y-m-d') }}&date_to={{ now()->addWeek()->format('Y-m-d') }}">{{ $appointment->place->name }}</a>--}}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection

