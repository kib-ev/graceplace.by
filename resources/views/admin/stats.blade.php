@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Places</h1>

            <hr>
            <a href="{{ route('admin.places.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            @php
                $appointments = \App\Models\Appointment::all()
            @endphp

            <table class="table table-bordered">
                <tr>
                    <td>Мастеров в базе</td>
                    <td>{{ \App\Models\Master::count() }}</td>
                </tr>
                <tr>
                    <td>Записей / Посещений / Отмен</td>
                    <td>
                        {{ $appointments->count() }} /
                        {{ $appointments->whereNull('canceled_at')->count() }} /
                        {{ $appointments->whereNotNull('canceled_at')->count() }}
                    </td>
                </tr>

                <tr>
                    <td>Записей через ЛК</td>
                    <td>
                        {{ $selfAddedCount = $appointments->sum(function ($item) { return $item->isSelfAdded() ? 1 : 0; }) }}

                        @if($appointments->count() > 0)
                            ({{ round($selfAddedCount / $appointments->count() * 100, 1) }} %)
                        @endif
                    </td>
                </tr>

                <tr>
                    <td>Часов аренды</td>
                    <td>{{ \App\Models\Appointment::sum('duration') / 60 }}</td>
                </tr>
            </table>


            <table class="table table-bordered">
                <tr>
                    @for($i = 1; $i <=12; $i++)
                        <td>{{ \Carbon\Carbon::parse('01-'. $i . '-2024')->format('M-Y') }}</td>
                    @endfor
                    <td><b>ВСЕГО</b></td>
                </tr>
                <tr>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ \App\Models\Appointment::whereMonth('date', $i)->sum('price') }}
                        </td>
                    @endfor
                    <td>
                        {{ \App\Models\Appointment::whereYear('date', '2024')->sum('price') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection
