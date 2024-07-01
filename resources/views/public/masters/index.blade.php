@extends('app')

@section('content')
    <div class="row">
        <div class="col">
            <table>
                @foreach($masters as $master)
                    <tr>
                        <td>{{ $master->full_name }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
