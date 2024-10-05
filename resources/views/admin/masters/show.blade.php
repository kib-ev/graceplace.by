@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Мастер</h1>
            <hr>
            <table class="table table-bordered">
                <tr>
                    <td>id: {{ $master->id }}</td>
                </tr>
                <tr>
                    <td>user_id: {{ \App\Services\AppointmentService::getUserByMasterId($master->id)?->id }}</td>
                </tr>
                <tr>
                    <td>{{ $master->person->first_name }} {{ $master->person->last_name }}</td>
                </tr>
{{--                <tr>--}}
{{--                    <td>{{ $master->person->birth_date }}</td>--}}
{{--                </tr>--}}
                <tr>
                    <td>
                        <ul style="list-style-type: none; margin: 0px; padding: 0px;">
                            @foreach($master->person->phones as $phone)
                                <li>{{ $phone->number }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td>{{ $master->description }}</td>
                </tr>
                <tr>
                    <td>{{ $master->instagram }}</td>
                </tr>
                <tr>
                    <td>Количество записей: {{ \App\Models\Appointment::where('master_id', $master->id)->count() }}</td>
                </tr>

                <tr>
                    <td>Количество отмен: {{ \App\Models\Appointment::whereNotNull('canceled_at')->where('master_id', $master->id)->count() }}</td>
                </tr>

                <tr>
                    <td>Количество посещений: {{ \App\Models\Appointment::whereNull('canceled_at')->where('master_id', $master->id)->count() }}</td>
                </tr>

                <tr>
                    <td>СУММА: {{ $sum = \App\Models\Appointment::whereNull('canceled_at')->where('master_id', $master->id)->sum('price') }} BYN</td>
                </tr>

                <tr>
                    <td>Всего часов: {{ $hours = \App\Models\Appointment::whereNull('canceled_at')->where('master_id', $master->id)->sum('duration') / 60 }}</td>
                </tr>

                <tr>
                    <td>Сред. стоимость часа: {{ $hours ? $sum / $hours : 0 }}</td>
                </tr>

            </table>

            <a class="btn btn-primary float-end" href="{{ route('admin.masters.edit', $master) }}">edit</a>

            @if($master->appointments->count() == 0)
                <form action="{{ route('admin.masters.destroy', $master) }}" method="post">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger">удалить</button>
                </form>
            @endif

        </div>
    </div>

    <div class="row mt-3">
        <div class="col">
            <div class="comments">
                @include('admin.comments.includes.widget', ['model' => $master, 'title' => 'Комментарий', 'type' => 'admin'])
            </div>
        </div>
    </div>

    <div class="row mt-3">
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
                            {{ $master->appointments()->whereMonth('start_at', $i)->sum('price') }}
                        </td>
                    @endfor
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <a class="btn btn-primary me-3 mb-3" href="https://graceplace.by/admin/appointments/create?master_id={{ $master->id }}">Добавить запись</a>

            @include('admin.appointments.includes.table', ['appointments' => $master->appointments])
        </div>
    </div>
@endsection
