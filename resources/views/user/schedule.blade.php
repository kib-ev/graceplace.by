@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Мои записи</h1>

            @if(isset($master))

                <p>Вы вошли как: <b>{{ $master->full_name }}</b></p>

                <table class="table table-bordered">
                    <tr>
                        @for($date = now()->subDays(10); $date->lessThan(now()->addDays(10)); $date->addDay())
                            <td>
                                {{ $date->getTranslatedShortDayName() }}
                            </td>
                        @endfor
                    </tr>
                    <tr>
                        @for($date = now()->subDays(10); $date->lessThan(now()->addDays(10)); $date->addDay())
                            <td>
                                {{ $date->format('d.m') }}
                            </td>
                        @endfor
                    </tr>
                </table>

                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Текущие</button>
                        <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Прошедшие</button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        @foreach($master->appointments->where('date', '>', now())->sortByDesc('date')->groupBy(function ($a) { return $a->date->format('y.m.d'); }) as $appointmentByDate)
                            <table class="table table-bordered">
                                @foreach($appointmentByDate->sortBy('date') as $appointment)
                                    <tr>
                                        <td>
                                            {{ $appointment->date->format('Y-m-d') }}
                                        </td>
                                        <td>
                                            {{ $appointment->date->format('H:i') }} -
                                            {{ $appointment->date->addMinutes($appointment->duration)->format('H:i') }}
                                        </td>
                                        <td>
                                            {{ $appointment->place->name }}
                                        </td>
                                    </tr>

                                @endforeach
                            </table>
                        @endforeach
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        @foreach($master->appointments->where('date', '<', now())->sortBy('date')->groupBy(function ($a) { return $a->date->format('y.m.d'); }) as $appointmentByDate)
                            <table class="table table-bordered">
                                @foreach($appointmentByDate->sortBy('date') as $appointment)

                                    <tr>
                                        <td>
                                            {{ $appointment->date->format('Y-m-d') }}
                                        </td>
                                        <td>
                                            {{ $appointment->date->format('H:i') }} -
                                            {{ $appointment->date->addMinutes($appointment->duration)->format('H:i') }}
                                        </td>
                                        <td>
                                            {{ $appointment->place->name }}
                                        </td>
                                    </tr>

                                @endforeach
                            </table>
                        @endforeach
                    </div>
                </div>





            @endif


        </div>
    </div>
@endsection
