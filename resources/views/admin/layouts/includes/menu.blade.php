{{--<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">--}}
{{--    <span class="navbar-toggler-icon"></span>--}}
{{--</button>--}}

<div class="d-flex align-items-center d-md-none">
    <!-- Кнопка обновления страницы -->
    <button class="btn btn-outline-secondary me-2" type="button" onclick="location.reload();">
        <i class="fa-solid fa-rotate"></i>
    </button>

    <!-- Кнопка гамбургера -->
    <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
    </button>
</div>

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
        <a class="nav-link" href="{{ route('admin.permissions.index') }}">Права</a>
        <a class="nav-link" href="{{ url('/admin/api') }}">API</a>
        <a class="nav-link" href="{{ route('admin.download.chrome-extension') }}">Скачать плагин</a>
        <a class="nav-link" href="{{ url('/logout') }}">Выйти</a>
    </div>
</div>
