@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Masters</h1>

            <hr>
            <a href="{{ route('admin.masters.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            <table class="table table-bordered mb-5">
                <tr>
                    <td></td>
                    <td>Имя мастера</td>
                    <td>Инста</td>
                    <td>Телефон</td>
                    <td>Услуги</td>
                    <td>Дата <br> регистрации</td>
                    <td>Количество <br> визитов</td>
                    <td>Последний <br> визит</td>


                    <td></td>
                    <td></td>
                </tr>
                @foreach($masters->sortBy('person.first_name') as $master)
                    <tr>
                        <td style="width: 50px;">{{ $loop->index + 1 }}</td>
                        <td style="width: 400px;">
                            <a href="{{ route('admin.masters.show', $master) }}">{{ $master->full_name }}</a>

                            <span style="color: #ccc;">id: {{ $master->id }}</span>

                        </td>

                        <td>
                            @if(isset($master->instagram))
                                <a target="_blank" href="{{ $master->instagram }}">Inst</a>
                            @endif
                        </td>

                        <td style="width: 200px;">{{ $master->person->phones->first()?->number }}</td>

                        <td>{{ $master->description }}</td>

                        <td>
                            {{ $master->created_at->format('d.m.Y') }}
                        </td>

                        <td>
                            {{ \App\Models\Appointment::where('master_id', $master->id)->count() }}
                        </td>

                        <td>
                            @php
                                $lastAppointment = $master->lastAppointment();
                            @endphp

                            @if($lastAppointment && $lastAppointment->date < now())
                                {{ \Carbon\Carbon::now()->startOfDay()->diffInDays($lastAppointment->date) }} д. назад
                            @else
                                <span style="color: greenyellow;">запись</span>
                            @endif


                        </td>



                        <td>{{ $master->person->birth_date }}</td>
                        <td><a href="{{ route('admin.masters.edit', $master) }}">edit</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
