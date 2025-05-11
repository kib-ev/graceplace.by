<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">
        {{--                <a class="nav-link active" aria-current="page" href="#">Home</a>--}}
        <a class="nav-link" href="{{ route('admin.appointments.index') }}">Записи</a>
        <a class="nav-link" href="{{ route('admin.masters.index', ['is_active' => 1]) }}">Мастера</a>
        <a class="nav-link" href="{{ route('admin.places.index') }}">Рабочие места</a>
        <a class="nav-link" href="{{ route('admin.storage-cells.index') }}">Локер</a>
        <a class="nav-link" href="{{ route('admin.tickets.index') }}">Заявки</a>
        <a class="nav-link" href="{{ url('/admin/stats') }}">Статистика</a>
        <a class="nav-link" href="{{ url('/admin/logs') }}">Лог</a>
        <a class="nav-link" href="{{ route('admin.permissions.index') }}">Разрешения</a>
        <a class="nav-link" href="{{ url('/logout') }}">Выйти</a>
    </div>
</div>
