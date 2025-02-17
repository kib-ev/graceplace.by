<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h1 class="mb-4">Admin Dashboard</h1>

    <!-- Основные метрики -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Всего пользователей</h5>
                    <p class="card-text fs-2">1,500</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Новых пользователей (неделя)</h5>
                    <p class="card-text fs-2">120</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Активных пользователей (месяц)</h5>
                    <p class="card-text fs-2">750</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Отмененные записи (неделя)</h5>
                    <p class="card-text fs-2">34</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Аналитика бронирований -->
    <h2 class="mt-4">Аналитика бронирований</h2>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Активные бронирования</h5>
                    <p class="card-text fs-2">280</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Просроченные бронирования</h5>
                    <p class="card-text fs-2">5</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Отмененные бронирования</h5>
                    <p class="card-text fs-2">42</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Финансовая статистика -->
    <h2 class="mt-4">Финансовая статистика</h2>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Общий доход (месяц)</h5>
                    <p class="card-text fs-2">350,000 руб.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Доход от мастеров</h5>
                    <p class="card-text fs-2">120,000 руб.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Неоплаченные счета</h5>
                    <p class="card-text fs-2">15,000 руб.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Средний чек</h5>
                    <p class="card-text fs-2">3,500 руб.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Уведомления и задачи -->
    <h2 class="mt-4">Уведомления и задачи</h2>
    <ul class="list-group mb-4">
        <li class="list-group-item d-flex justify-content-between align-items-center">
            5 просроченных бронирований
            <span class="badge bg-danger rounded-pill">Внимание</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            20 неоплаченных счетов
            <span class="badge bg-warning rounded-pill">Важно</span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            10 новых отзывов
            <span class="badge bg-primary rounded-pill">Проверить</span>
        </li>
    </ul>

    <!-- Активность клиентов -->
    <h2 class="mt-4">Анализ клиентской активности</h2>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Новые клиенты (месяц)</h5>
                    <p class="card-text fs-2">180</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Повторные бронирования</h5>
                    <p class="card-text fs-2">220</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Уровень удержания клиентов</h5>
                    <p class="card-text fs-2">75%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Активность мастеров -->
    <h2 class="mt-4">Анализ работы мастеров</h2>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Загруженность мастеров</h5>
                    <p class="card-text">Наиболее загруженные мастера: Иванов И., Петров П.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Рейтинг мастеров</h5>
                    <p class="card-text">Средний рейтинг: 4.5</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Финансовая эффективность мастеров</h5>
                    <p class="card-text">Топ-мастера по доходу: Иванов И. (80,000 руб.)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
