# Отчёт по оптимизации проекта Grace Place Coworking

## Высокий приоритет

### 1. Удалить тестовые роуты (безопасность)
**Файл:** `routes/web.php`

- **Строки 24-26:** `/gpt` — тестовая страница, доступна всем
- **Строки 313-315:** `POST /test` — возвращает 123
- **Строки 400-402:** `GET /test` — тестовая view

**Рекомендация:** Удалить или защитить `dev`-переменной. Тестовые роуты не должны быть на продакшене.

---

### 2. Mass assignment — заменить `fill($request->all())`
**Риск:** Возможная подмена полей через запрос.

| Файл | Строка |
|------|--------|
| `AppointmentController.php` | 79, 130 |
| `PlaceController.php` | 136 |
| `StorageCellController.php` | 25, 36 |
| `StorageBookingController.php` | 43 |

**Рекомендация:** Использовать только валидированные данные:
```php
$validated = $request->validate([...]);
$model->fill($validated);
```

---

### 3. Ошибка в `direct_link()`
**Файл:** `app/helpers.php`, строка 74

```php
if ($parts > 0) {  // Ошибка: $parts — массив, сравнение некорректно
```

**Исправление:**
```php
if (count($parts) > 0) {
```

---

### 4. Дублирование роутов appointments
**Файл:** `routes/web.php`

- Строки 349-355: `POST /user/appointments`, `POST /user/appointments/{id}/cancel`
- Строки 437-438: `POST /appointments`, `POST /appointments/{id}/cancel` (без префикса)

**Рекомендация:** Удалить дубликаты 436-439, если они не нужны для API/внешних клиентов.

---

## Средний приоритет

### 5. Логика в route closures вместо контроллеров
**Файл:** `routes/web.php`

Сложная логика в `function()`:
- Строки 120-139: appointments-stats
- Строки 142-195: appointments-chart
- Строки 250-254: logs
- Строки 356-373: documents

**Рекомендация:** Вынести в контроллеры для тестирования и переиспользования.

---

### 6. Дублирование route `update-settings`
**Файл:** `routes/web.php`

- Строки 291-306: внутри `admin` (admin.update-settings)
- Строки 337-346: внутри `user` (user.update-settings)

Оба выполняют одинаковое действие — сохраняют `workspace_visibility`. Проверить, нужны ли оба.

---

### 7. N+1 запросы
**Потенциальные места:**
- `AppointmentController` — добавить `->with(['place', 'user'])` при загрузке
- `Place::isAppointment()` — запросы в цикле
- `StatsController` — eager loading для relationships

---

### 8. Пагинация логов
**Файл:** `routes/web.php`, ~250

`Appointment::all()` загружает все записи. Рекомендация: добавить `->paginate(100)` или `->latest()->limit(500)`.

---

### 9. Комментированный код
**Удалить или раскомментировать:**
- `routes/web.php:86-92` — закомментированные schedule routes
- `routes/web.php:322-323` — закомментированные appointment routes
- `App\Models\Appointment.php:55-58` — закомментированный `master()` relation

---

## Низкий приоритет

### 10. Form Request классы
**Файл:** `app/Http/Controllers/`

Валидация в контроллерах. Рекомендация: вынести в `StoreAppointmentRequest`, `UpdatePlaceRequest` и т.д.

---

### 11. Хелпер `is_admin()` уже есть
**Файл:** `app/helpers.php`

`is_admin()` уже есть и используется. Можно заменить `hasAnyRole(['admin', 'manager'])` на `is_admin()` в views для единообразия.

---

### 12. Неиспользуемые view
- `resources/views/gpt.blade.php` — если удалить роут `/gpt`
- `resources/views/test.blade.php` — если удалить роут `/test`

---

### 13. Магические числа
`Appointment::CANCELLATION_TIMEOUT = 24` — можно вынести в `config/appointment.php` для гибкости.

---

## Резюме

| Приоритет | Количество | Действия |
|-----------|------------|----------|
| Высокий   | 4          | Удалить тестовые роуты, исправить mass assignment, баг direct_link, проверить дубли роутов |
| Средний   | 5          | Вынести логику в контроллеры, eager loading, пагинация логов |
| Низкий    | 4          | Form Requests, рефакторинг, чистка кода |
