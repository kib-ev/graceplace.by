<table class="table table-bordered">
    @foreach($appointments->sortBy('date') as $appointment)
        <tr class="{{ $appointment->canceled_at ? 'canceled' : '' }}">
            <td style="width: 1%; min-width: 30px;">
                {{ $loop->index + 1 }}
            </td>

            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                {{ $appointment->date->format('d.m.Y') }}
            </td>

            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                {{ short_day_name($appointment->date, true) }}
            </td>

            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                @if($appointment->full_day)
                    Полный день
                @elseif(isset($appointment->date))
                    {{ $appointment->date?->format('H:i') }} -
                    {{ $appointment->date->addMinutes($appointment->duration)?->format('H:i') }}
                @endif
            </td>

            <td style="width: 1%; min-width: 30px;">
                @if($appointment->isSelfAdded())
                    <span class="self-added"><i class="fa fa-user"></i></span>
                @endif
            </td>

            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                <a href="{{ route('admin.masters.show', $appointment->master) }}">{{ $appointment->master->full_name }}</a>
            </td>

            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                @if($appointment->place)
                    <a href="{{ route('admin.places.show', $appointment->place) }}">{{ $appointment->place->name }}</a>

                @endif
            </td>

            <td style="width: 1%; min-width: 30px; white-space: nowrap; text-align: right;">
                @if(isset($appointment->price))
                    @if($appointment->price == 0)
                        FREE
                    @else
                        {{ number_format($appointment->price, 2) }} BYN
                    @endif

                @else
                    <span style="color: #dddddd;">{{ number_format($appointment->getExpectedPrice(), 2) }} BYN</span>
                @endif
            </td>

            <td>
                {{ $appointment->description }}
            </td>

            <td style="width: 1%; min-width: 30px; white-space: nowrap;">
                <a href="{{ route('admin.appointments.edit', $appointment) }}"><span class="fa fa-edit"></span></a>
            </td>
        </tr>
    @endforeach
</table>
