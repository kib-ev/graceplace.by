@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Master</h1>
            <a href="{{ route('admin.masters.index') }}">Masters</a>
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
                <tr>
                    <td>{{ $master->description }}</td>
                </tr>
                <tr>
                    <td>{{ $master->instagram }}</td>
                </tr>
                <tr>
                    <td>Количество посещений: {{ \App\Models\Appointment::where('master_id', $master->id)->count() }}</td>
                </tr>

                <tr>
                    <td>СУММА: {{ $sum = \App\Models\Appointment::where('master_id', $master->id)->sum('price') }} BYN</td>
                </tr>

                <tr>
                    <td>Количество часов: {{ $hours = \App\Models\Appointment::where('master_id', $master->id)->sum('duration') / 60 }}</td>
                </tr>

                <tr>
                    <td>Сред. стоимость часа: {{ $hours ? $sum / $hours : 0 }}</td>
                </tr>

            </table>

            <a href="{{ route('admin.masters.edit', $master) }}">edit</a>

            @if($master->appointments->count() == 0)
                <form action="{{ route('admin.masters.destroy', $master) }}" method="post">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger">удалить</button>
                </form>
            @endif


            <table class="table table-bordered">
                @foreach($master->appointments->sortBy('date') as $appointment)
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
                            @if($appointment->place)
                                <a href="{{ route('admin.places.show', $appointment->place) }}">{{ $appointment->place->name }}</a>
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

                        <td>
                            <a href="{{ route('admin.appointments.edit', $appointment) }}">edit</a>
                        </td>
                    </tr>
                @endforeach
            </table>


        </div>
    </div>
@endsection
