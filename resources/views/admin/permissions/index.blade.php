@extends('app')


@section('content')
    <div class="container">
        <h2>Управление правами на отмену записи</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.permissions.update') }}" method="POST">
            @csrf
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Право на отмену записи</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <a href="{{ route('admin.masters.show', $user->master) }}">{{ $user->name }}</a>
                        </td>
                        <td>
                            <input type="checkbox" name="master_{{ $user->id }}"
                                {{ $user->can($permissionName) ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Обновить права</button>
        </form>
    </div>
@endsection
