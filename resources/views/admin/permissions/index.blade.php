@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h2>Управление правами</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.permissions.update-all') }}" method="POST">
            @csrf
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>
                        Право на отмену записи
                        <br>
                        <input type="checkbox" id="select-all-cancel">
                    </th>
                    <th>
                        Право на добавление записи
                        <br>
                        <input type="checkbox" id="select-all-add">
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <a href="{{ route('admin.masters.show', $user->master) }}">{{ $user->name }}</a>
                        </td>
                        <td>
                            <input type="checkbox" name="cancel_{{ $user->id }}" class="cancel-checkbox"
                                {{ $user->can('cancel appointment') ? 'checked' : '' }}>
                        </td>
                        <td>
                            <input type="checkbox" name="add_{{ $user->id }}" class="add-checkbox"
                                {{ $user->can('add appointment') ? 'checked' : '' }}>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Обновить права</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Массовая установка для "отмены записи"
        document.getElementById('select-all-cancel').addEventListener('change', function () {
            let checked = this.checked;
            document.querySelectorAll('.cancel-checkbox').forEach(function (checkbox) {
                checkbox.checked = checked;
            });
        });

        // Массовая установка для "добавления записи"
        document.getElementById('select-all-add').addEventListener('change', function () {
            let checked = this.checked;
            document.querySelectorAll('.add-checkbox').forEach(function (checkbox) {
                checkbox.checked = checked;
            });
        });
    </script>
@endsection
