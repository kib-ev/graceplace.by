@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Places</h1>

            <hr>
            <a href="{{ route('admin.places.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered">
                <tr>
                    <td>Мастеров в базе</td>
                    <td>{{ \App\Models\Master::count() }}</td>
                </tr>
                <tr>
                    <td>Посещений</td>
                    <td>{{ \App\Models\Appointment::count() }}</td>
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
                </tr>
                <tr>
                    @for($i = 1; $i <=12; $i++)
                        <td>
                            {{ \App\Models\Appointment::whereMonth('date', $i)->sum('price') }}
                        </td>
                    @endfor
                </tr>
            </table>
        </div>
    </div>
@endsection
