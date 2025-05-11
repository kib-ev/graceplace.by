@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Logs</h1>

            <table class="table table-bordered">
                @foreach(\App\Models\Appointment::withoutCanceled()->orderByDesc('created_at')->with(['user.master.person.phones', 'place'])->get()->groupBy(function ($a) {  return $a->created_at->format('Y/m/d'); }) as $groupDate => $appointmentsByDate)
                    <tr>
                        <td colspan="8" style="background: #d7d5d2;"><b>{{ \Carbon\Carbon::parse($groupDate)->format('d.m.Y') }}</b></td>
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
                                @if($appointment->user->master)
                                    <a href="{{ route('admin.masters.show', $appointment->user->master) }}">{{ $appointment->user->master->full_name }}</a>
                                @endif

                                @if($appointment->is_created_by_user)
                                    <span style="padding: 5px 10px; font-size: 0.8em; color: #ccc; float: right; background-color: #fff;">user</span>
                                @else
                                    <span style="padding: 5px 10px; font-size: 0.8em; color: #ccc; float: right; background-color: #fffc93;">admin</span>
                                @endif
                            </td>
                            <td>
                                <a target="_blank" href="https://graceplace.by/?date={{ $appointment->start_at->format('Y-m-d') }}">{{ $appointment->start_at->format('d.m.Y') }}</a>
                            </td>
                            <td>
                                {{ $appointment->start_at->format('H:i') }} -
                                {{ $appointment->start_at->addMinutes($appointment->duration)->format('H:i') }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($appointment->start_at)->diffAsCarbonInterval($appointment->created_at)->forHumans() }}
                            </td>
                            <td>
                                {{ $appointment->place->name }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </table>
        </div>
    </div>
@endsection
