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
                    <td>
                        Среднее время аренды:

                        @if($place->appointments->count())
                            {{ $place->appointments->sum('duration') / 60 / $place->appointments->count() }}
                        @else
                            0
                        @endif
                    </td>
                </tr>

                <tr>
                    <td>СУММА: {{ $place->appointments->sum('price') }} BYN</td>
                </tr>

            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-bordered">
                <tr>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ \Carbon\Carbon::parse('01-'. $i . '-2024')->format('M-Y') }}</td>
                    @endfor
                </tr>
                <tr>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ $place->appointments()->whereMonth('date', $i)->sum('price') }}
                        </td>
                    @endfor
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h1>Appointments</h1>

            <hr>

            @include('admin.appointments.includes.table', ['appointments' => $place->appointments])

        </div>
    </div>

@endsection
