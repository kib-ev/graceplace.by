@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Place: {{ $place->name }}</h1>
                <div>
                    <a href="{{ route('admin.places.prices.index', $place) }}" class="btn btn-primary">
                        <i class="fa fa-money"></i> Manage Prices
                    </a>
                    <a href="{{ route('admin.places.edit', $place) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                </div>
            </div>

            <hr>

            <table class="table table-bordered">
                <tr>
                    <td>ID: {{ $place->id }}</td>
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
                    <td>СУММА: {{ \App\Models\PaymentRequirement::where('payable_type', \App\Models\Appointment::class)
                        ->whereIn('payable_id', $place->appointments->pluck('id'))
                        ->sum('expected_amount') }} BYN</td>
                </tr>

            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-bordered">
                <tr>
                    <th></th>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ \Carbon\Carbon::parse('01-'. $i . '-2024')->format('M-Y') }}</td>
                    @endfor
                </tr>
                <tr>
                    <th>Аренда руб</th>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ $stats2024[$i]['price'] }}</td>
                    @endfor
                </tr>
                <tr>
                    <th>Часов аренды</th>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ $stats2024[$i]['duration'] }}</td>
                    @endfor
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <table class="table table-bordered">
                <tr>
                    <th></th>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ \Carbon\Carbon::parse('01-'. $i . '-2025')->format('M-Y') }}</td>
                    @endfor
                </tr>
                <tr>
                    <th>Аренда руб</th>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ $stats2025[$i]['price'] }}</td>
                    @endfor
                </tr>
                <tr>
                    <th>Часов аренды</th>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ $stats2025[$i]['duration'] }}</td>
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
