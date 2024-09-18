@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Logs</h1>


            <table class="table table-bordered">

                @foreach(\App\Models\Appointment::withoutCanceled()->orderByDesc('created_at')->get()->groupBy(function ($a) {  return $a->created_at->format('Y/m/d'); }) as $groupDate => $appointmentsByDate)

                    <tr>
                        <td colspan="7" style="background: #d7d5d2;"><b>{{ \Carbon\Carbon::parse($groupDate)->format('d.m.Y') }}</b></td>
                    </tr>

                    @foreach($appointmentsByDate as $appointment)
                        <tr>
                            <td style="width: 1%; white-space: nowrap;">
                                {{ $loop->index + 1 }}
                                <br>
                                <span style="color: #ccc;">id: {{ $appointment->id }}</span>
                            </td>
                            <td>
                                {{ $appointment->created_at->format('H:i') }}
                            </td>
                            <td>

                            </td>
                            <td>
                                @if(isset($appointment->master))
                                    <a href="{{ route('admin.masters.show', $appointment->master) }}">{{ $appointment->master->full_name }}</a>
                                @endif

                                @php
                                    $creator = \App\Models\User::find($appointment->user_id);
                                @endphp

                                @if(isset($appointment->master) && $creator)
                                    <br>
                                    <span style="font-size: 0.8em; color: #ccc; float: right; background-color: {{ $creator->hasRole('admin') ? '#fffc93' : '#fff' }};">{{ $creator->name }}</span>
                                @endif
                            </td>
                            <td>
                                <a target="_blank" href="https://graceplace.by/?date={{ $appointment->date->format('Y-m-d') }}">{{ $appointment->date->format('d.m.Y') }}</a>
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

                @endforeach
{{--                <tr>--}}
{{--                    @for($i = 1; $i <=12; $i++)--}}
{{--                        <td>{{ \Carbon\Carbon::parse('01-'. $i . '-2024')->format('M-Y') }}</td>--}}
{{--                    @endfor--}}
{{--                    <td><b>ВСЕГО</b></td>--}}
{{--                </tr>--}}
{{--                <tr>--}}
{{--                    @for($i = 1; $i <=12; $i++)--}}
{{--                        <td>--}}
{{--                            {{ \App\Models\Appointment::whereMonth('date', $i)->sum('price') }}--}}
{{--                        </td>--}}
{{--                    @endfor--}}
{{--                    <td>--}}
{{--                        {{ \App\Models\Appointment::whereYear('date', '2024')->sum('price') }}--}}
{{--                    </td>--}}
{{--                </tr>--}}
            </table>
        </div>
    </div>
@endsection
