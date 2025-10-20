@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h1>Выручка по часам</h1>

        <form method="get" class="mb-3" action="{{ route('admin.revenue.hours') }}" style="display:inline-flex; gap:10px; align-items:center;">
            <input type="date" name="date_from" value="{{ ($dateFrom ?? now())->toDateString() }}">
            <span>—</span>
            <input type="date" name="date_to" value="{{ ($dateTo ?? now())->toDateString() }}">
            <label>от</label>
            <input type="number" min="0" max="24" name="start_hour" value="{{ $startHour }}" style="width:80px;">
            <label>до</label>
            <input type="number" min="1" max="24" name="end_hour" value="{{ $endHour }}" style="width:80px;">
            <button type="submit" class="btn btn-sm btn-primary">Применить</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th style="white-space:nowrap;">Кабинет</th>
                        @foreach($hours as $h)
                            <th style="text-align:right; white-space:nowrap;">{{ sprintf('%02d:00-%02d:00', $h, ($h+1) % 24) }}</th>
                        @endforeach
                        <th style="text-align:right; white-space:nowrap;">Итого</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($places as $place)
                        <tr>
                            <th style="white-space:nowrap;">{{ $place->name }}</th>
                            @foreach($hours as $h)
                                <td style="text-align:right; white-space:nowrap;">
                                    {{ number_format($grid[$place->id][$h] ?? 0, 2, '.') }}
                                </td>
                            @endforeach
                            <th style="text-align:right; white-space:nowrap;">{{ number_format($totalsByPlace[$place->id] ?? 0, 2, '.') }}</th>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Итого</th>
                        @foreach($hours as $h)
                            <th style="text-align:right;">{{ number_format($totalsByHour[$h] ?? 0, 2, '.') }}</th>
                        @endforeach
                        <th style="text-align:right;">{{ number_format(array_sum($totalsByPlace), 2, '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection


