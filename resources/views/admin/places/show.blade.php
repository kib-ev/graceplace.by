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

            <div class="card mb-3">
                <div class="card-header">
                    <h5>Price Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Current Price:</strong></td>
                            <td>{{ number_format($place->getCurrentPrice(), 2) }} BYN/hour</td>
                        </tr>
                        <tr>
                            <td><strong>Price History Records:</strong></td>
                            <td>{{ $place->prices()->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

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
                    <td>СУММА: {{ $place->appointments->sum('price') }} BYN</td>
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
                        <td>
                            {{ $place->appointments()->whereYear('start_at',2024)->whereMonth('start_at', $i)->sum('price') }}
                        </td>
                    @endfor
                </tr>
                <tr>
                    <th>Часов аренды</th>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ $place->appointments()->whereYear('start_at',2024)->whereMonth('start_at', $i)->sum('duration') / 60 }}
                        </td>
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
                        <td>
                            {{ $place->appointments()->whereYear('start_at',2025)->whereMonth('start_at', $i)->sum('price') }}
                        </td>
                    @endfor
                </tr>
                <tr>
                    <th>Часов аренды</th>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ $place->appointments()->whereYear('start_at',2025)->whereMonth('start_at', $i)->sum('duration') / 60 }}
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
