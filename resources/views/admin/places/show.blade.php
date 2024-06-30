@extends('app')


@section('content')
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

                <tr>
                    <td>Всего записей: {{ $place->appointments->count() }}</td>
                </tr>

                <tr>
                    <td>Часов в аренде: {{ $place->appointments->sum('duration') / 60 }}</td>
                </tr>

                <tr>
                    <td>СУММА: {{ $place->appointments->sum('price') }} BYN</td>
                </tr>

            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h1>Appointments</h1>

            <hr>

            <table class="table table-bordered">
                @foreach($place->appointments->sortBy('date') as $appointment)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $appointment->date?->format('d.m.Y') }}</td>

                        <td>
                            @if(isset($appointment->date))
                                {{ $appointment->date?->format('H:i') }} -
                                {{ $appointment->date->addMinutes($appointment->duration)?->format('H:i') }}
                            @endif
                        </td>

                        <td>
                            @if($appointment->master)
                                <a href="{{ route('admin.masters.show', $appointment->master) }}">{{ $appointment->master->full_name }}</a>
                            @endif
                        </td>

                        <td>
                            @if(isset($appointment->price))
                                @if($appointment->price == 0)
                                    FREE
                                @else
                                    {{ $appointment->price }} BYN
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>



        </div>
    </div>

@endsection
