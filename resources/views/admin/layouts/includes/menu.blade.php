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
        <a class="nav-link" href="{{ route('admin.masters.index', ['is_active' => 1]) }}">
            Мастера
            @if(isset($mastersWithAppointmentsDebtCount) && $mastersWithAppointmentsDebtCount > 0)
                <span class="badge bg-danger">{{ $mastersWithAppointmentsDebtCount }}</span>
            @endif
        </a>
        <a class="nav-link" href="{{ route('admin.storage-cells.index') }}">
            Локер
            @if(isset($mastersWithStorageBookingDebtCount) && $mastersWithStorageBookingDebtCount > 0)
                <span class="badge bg-danger">{{ $mastersWithStorageBookingDebtCount }}</span>
            @endif
        </a>
        <a class="nav-link" href="{{ route('admin.mandatory-notices.index') }}">Уведомления</a>
        @role('admin')
        <a class="nav-link" href="{{ route('admin.places.index') }}">Рабочие места</a>
        @endrole
        <a class="nav-link" href="{{ route('admin.tickets.index') }}">Заявки</a>
        @role('admin')
        <a class="nav-link" href="{{ url('/admin/stats') }}">Статистика</a>
        <a class="nav-link" href="{{ route('admin.permissions.index') }}">Права</a>
        <a class="nav-link" href="{{ route('admin.service-categories.index') }}">Категории</a>
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="systemMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Система
            </a>
            <ul class="dropdown-menu" aria-labelledby="systemMenuDropdown">
                <li><a class="dropdown-item" href="{{ route('admin.erip-imports.index') }}">ЕРИП</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.migrations.index') }}">Миграции</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Пользователи</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/api') }}">API</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/logs') }}">Лог</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.plugin.index') }}">Плагин</a></li>
            </ul>
        </div>
        @endrole
        <a class="nav-link" href="{{ url('/logout') }}">Выйти</a>
    </div>
</div>
