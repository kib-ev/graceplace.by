@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Баланс пользователя</h1>

            <table class="table-bordered table">
                @foreach(\App\Models\User::role('master')->get() as $user)
                    <tr>
                        <td>{{ $user->getFullName() }}</td>
                        <td>{{ $user->real_balance }}</td>
                        <td>{{ $user->bonus_balance }}</td>
                        <td>

                        </td>
                    </tr>
                @endforeach
            </table>

        </div>
    </div>
@endsection
