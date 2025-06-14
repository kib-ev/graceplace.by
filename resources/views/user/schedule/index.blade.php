@extends('public.layouts.app')

@section('content')
<div class="container">
    <!-- Add CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Debug output -->
            <div class="card mb-3 d-none">
                <div class="card-header">Debug: Existing Intervals</div>
                <div class="card-body">
                    @php
                        $startDate = \Carbon\Carbon::now()->startOfDay();
                        $endDate = \Carbon\Carbon::now()->addDays(100)->endOfDay();
                        $schedules = \App\Models\UserSchedule::getScheduleForDateRange(auth()->id(), $startDate, $endDate);
                        echo "<pre>";
                        print_r($schedules);
                        echo "</pre>";
                    @endphp
                </div>
            </div>
            <!-- End debug output -->

            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Управление расписанием</h3>
                </div>
                <div class="card-body">
                    <div id="schedule-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div id="calendar-strip" class="d-flex overflow-hidden flex-grow-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loading-overlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<style>
#schedule-container {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

#calendar-strip {
    scroll-behavior: smooth;
    cursor: default;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    touch-action: pan-x pinch-zoom;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

#calendar-strip .header {
    cursor: pointer;
}

#calendar-strip.dragging {
    cursor: grabbing;
    scroll-behavior: auto;
}

#calendar-strip.dragging .header {
    cursor: grabbing;
}

.day-card {
    min-width: 100px;
    border: 1px solid #dee2e6;
    margin-right: 2px;
    cursor: pointer;
}

.day-card .header {
    background-color: #f8f9fa;
    padding: 5px;
    border-bottom: 1px solid #dee2e6;
}

.day-card.weekend .header {
    background-color: #e9ecef;
}

.day-card.current .header {
    background-color: #e3f2fd;
}

.day-card.weekend {
    background-color: #f8f9fa;
}

.day-card.current {
    border-color: #007bff;
}

.time-slot {
    padding: 5px;
    border-bottom: 1px solid #eee;
    font-size: 0.9em;
    cursor: pointer;
    transition: background-color 0.2s;
}

.time-slot:hover {
    background-color: rgba(40, 167, 69, 0.1);
}

.time-slot.selected {
    background-color: #28a745;
    color: white;
}

.time-slot.selecting {
    background-color: rgba(40, 167, 69, 0.5);
    color: white;
}

.time-slot.deselecting {
    background-color: rgba(220, 53, 69, 0.5);
    color: white;
}

#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
</style>

@endsection

@push('scripts')
<script>
// Конфигурация календаря
const CONFIG = {
    DAYS_TO_SHOW: 100,           // Количество дней для отображения
    START_TIME: 7 * 60,          // Начальное время в минутах (6:00)
    SLOT_DURATION: 60,           // Продолжительность слота в минутах
    SLOTS_COUNT: 17,         // Количество временных слотов (17 часов * 2 получаса)
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('Schedule initialization started');
    const calendarStrip = document.getElementById('calendar-strip');
    let isMouseDown = false;
    let isDeselecting = false;
    let startSlot = null;

    // Переменные для drag-to-scroll
    let isDragging = false;
    let startX;
    let scrollLeft;
    let touchStartX;

    // Обработчики для drag-to-scroll (мышь)
    calendarStrip.addEventListener('mousedown', (e) => {
        // Проверяем, что клик был по шапке
        if (e.target.closest('.header')) {
            isDragging = true;
            calendarStrip.classList.add('dragging');
            startX = e.pageX - calendarStrip.offsetLeft;
            scrollLeft = calendarStrip.scrollLeft;
            
            // Предотвращаем выделение текста при перетаскивании
            e.preventDefault();
        }
    });

    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;

        const x = e.pageX - calendarStrip.offsetLeft;
        const walk = (x - startX);
        calendarStrip.scrollLeft = scrollLeft - walk;
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
        calendarStrip.classList.remove('dragging');
    });

    // Обработчики для touch-событий
    calendarStrip.addEventListener('touchstart', (e) => {
        if (e.target.closest('.header')) {
            isDragging = true;
            calendarStrip.classList.add('dragging');
            touchStartX = e.touches[0].pageX - calendarStrip.offsetLeft;
            scrollLeft = calendarStrip.scrollLeft;
        }
    }, { passive: true });

    calendarStrip.addEventListener('touchmove', (e) => {
        if (!isDragging) return;

        const x = e.touches[0].pageX - calendarStrip.offsetLeft;
        const walk = (x - touchStartX);
        calendarStrip.scrollLeft = scrollLeft - walk;
        
        // Предотвращаем прокрутку страницы при свайпе календаря
        if (Math.abs(walk) > 5) {
            e.preventDefault();
        }
    }, { passive: false });

    calendarStrip.addEventListener('touchend', () => {
        isDragging = false;
        calendarStrip.classList.remove('dragging');
    });

    // Обработчики для временных слотов на мобильных устройствах
    let touchTimeout;
    
    calendarStrip.addEventListener('touchstart', (e) => {
        if (e.target.classList.contains('time-slot')) {
            touchTimeout = setTimeout(() => {
                handleTimeSlotMouseDown(e);
            }, 200);
        }
    }, { passive: true });

    calendarStrip.addEventListener('touchend', (e) => {
        clearTimeout(touchTimeout);
        if (e.target.classList.contains('time-slot')) {
            handleTimeSlotMouseUp(e);
        }
    });

    calendarStrip.addEventListener('touchmove', (e) => {
        clearTimeout(touchTimeout);
        if (e.target.classList.contains('time-slot')) {
            const touch = e.touches[0];
            const target = document.elementFromPoint(touch.clientX, touch.clientY);
            if (target && target.classList.contains('time-slot')) {
                const mouseEvent = new MouseEvent('mouseover', {
                    clientX: touch.clientX,
                    clientY: touch.clientY,
                    bubbles: true
                });
                target.dispatchEvent(mouseEvent);
            }
        }
    }, { passive: true });

    // Отключаем стандартное поведение drag&drop
    calendarStrip.addEventListener('dragstart', (e) => {
        if (e.target.closest('.header')) {
            e.preventDefault();
        }
    });

    console.log('Calendar elements found:', {
        calendarStrip: !!calendarStrip
    });

    // Форматирование времени в формат H:i
    function formatTime(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
    }

    // Генерация дней
    function generateDays() {
        console.log('Generating days');
        const today = new Date();
        for (let i = 0; i < CONFIG.DAYS_TO_SHOW; i++) {
            const date = new Date(today);
            date.setDate(today.getDate() + i);

            const dayCard = document.createElement('div');
            dayCard.className = 'day-card';
            if (i === 0) dayCard.classList.add('current');
            if (date.getDay() === 0 || date.getDay() === 6) dayCard.classList.add('weekend');

            const dayName = date.toLocaleDateString('ru-RU', { weekday: 'short' });
            const dayNumber = date.getDate();
            const month = date.toLocaleDateString('ru-RU', { month: 'short' });

            dayCard.innerHTML = `
                <div class="header text-center">
                    <div class="font-weight-bold">${dayName}</div>
                    <div>${dayNumber} ${month}</div>
                </div>
                <div class="time-slots mt-2"></div>
            `;

            const timeSlotsContainer = dayCard.querySelector('.time-slots');
            for (let slot = 0; slot < CONFIG.SLOTS_COUNT; slot++) {
                const minutes = CONFIG.START_TIME + (slot * CONFIG.SLOT_DURATION);
                const timeSlot = document.createElement('div');
                timeSlot.className = 'time-slot';
                timeSlot.textContent = formatTime(minutes);
                timeSlot.dataset.date = date.toISOString().split('T')[0];
                timeSlot.dataset.time = formatTime(minutes);
                timeSlotsContainer.appendChild(timeSlot);
            }

            calendarStrip.appendChild(dayCard);
        }
    }

    // Загрузка существующих интервалов
    async function loadIntervals() {
        console.log('Loading intervals started');
        showLoading();
        try {
            const response = await fetch('{{ route("user.schedule.all-intervals") }}');
            const data = await response.json();
            console.log('Loaded intervals:', data);

            // Сначала очистим все выделения
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected', 'selecting', 'deselecting');
            });

            if (data.success) {
                Object.entries(data.data).forEach(([date, intervals]) => {
                    console.log(`Processing intervals for date ${date}:`, intervals);
                    intervals.forEach(interval => {
                        const startMinutes = parseInt(interval.start_time.split(':')[0]) * 60 + parseInt(interval.start_time.split(':')[1]);
                        const endMinutes = parseInt(interval.end_time.split(':')[0]) * 60 + parseInt(interval.end_time.split(':')[1]);

                        for (let minutes = startMinutes; minutes < endMinutes; minutes++) {
                            const slot = document.querySelector(
                                `.time-slot[data-date="${date}"][data-time="${formatTime(minutes)}"]`
                            );
                            if (slot) {
                                slot.classList.add('selected');
                            }
                        }
                    });
                });
            }
        } catch (error) {
            console.error('Error loading intervals:', error);
        }
        hideLoading();
    }

    // Функция для проверки и обновления CSRF-токена
    async function refreshCsrfToken() {
        try {
            const response = await fetch('{{ route("user.schedule.csrf") }}');
            if (response.ok) {
                const data = await response.json();
                if (data.token) {
                    document.querySelector('meta[name="csrf-token"]').content = data.token;
                    return true;
                }
            }
            return false;
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
            return false;
        }
    }

    // Функция для проверки ответа на ошибки аутентификации
    async function handleResponse(response) {
        if (response.redirected) {
            window.location.href = response.url;
            return null;
        }

        if (!response.ok) {
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("text/html") !== -1) {
                // Если получили HTML вместо JSON, вероятно, это страница логина
                window.location.reload();
                return null;
            }

            if (contentType && contentType.indexOf("application/json") !== -1) {
                const data = await response.json();
                if (data.errors) {
                    throw new Error(Object.values(data.errors).flat().join('\n'));
                } else if (data.message) {
                    throw new Error(data.message);
                }
            }
            throw new Error('Server error');
        }

        return await response.json();
    }

    // Сохранение интервала
    async function saveInterval(date, startTime, endTime) {
        showLoading();
        try {
            const response = await fetch('{{ route("user.schedule.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date,
                    start_time: startTime,
                    end_time: endTime
                })
            });

            const data = await handleResponse(response);
            if (!data) return false;

            if (data.success) {
                // Перезагрузим все интервалы, чтобы отобразить объединенные
                await loadIntervals();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error saving interval:', error);
            alert(error.message || 'Failed to save schedule. Please try again.');
            return false;
        } finally {
            hideLoading();
        }
    }

    // Удаление интервала
    async function deleteInterval(date, startTime, endTime) {
        showLoading();
        try {
            const response = await fetch('{{ route("user.schedule.delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date,
                    start_time: startTime,
                    end_time: endTime
                })
            });

            const data = await handleResponse(response);
            if (!data) return false;

            if (data.success) {
                // Перезагрузим все интервалы после удаления
                await loadIntervals();
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error deleting interval:', error);
            alert(error.message || 'Failed to delete schedule. Please try again.');
            return false;
        } finally {
            hideLoading();
        }
    }

    // Обработчики событий для временных слотов
    function handleTimeSlotMouseDown(e) {
        if (e.button !== 0) return; // только левая кнопка мыши

        isMouseDown = true;
        startSlot = e.target;
        isDeselecting = e.target.classList.contains('selected');

        if (isDeselecting) {
            e.target.classList.remove('selected');
            e.target.classList.add('deselecting');
        } else {
            e.target.classList.add('selecting');
        }
    }

    function handleTimeSlotMouseEnter(e) {
        if (!isMouseDown || !startSlot) return;

        const currentSlot = e.target;
        if (currentSlot.dataset.date !== startSlot.dataset.date) return;

        const startTime = startSlot.dataset.time;
        const currentTime = currentSlot.dataset.time;

        const startMinutes = parseInt(startTime.split(':')[0]) * 60 + parseInt(startTime.split(':')[1]);
        const currentMinutes = parseInt(currentTime.split(':')[0]) * 60 + parseInt(currentTime.split(':')[1]);

        const minMinutes = Math.min(startMinutes, currentMinutes);
        const maxMinutes = Math.max(startMinutes, currentMinutes);

        document.querySelectorAll('.time-slot').forEach(slot => {
            const slotMinutes = parseInt(slot.dataset.time.split(':')[0]) * 60 + parseInt(slot.dataset.time.split(':')[1]);
            const slotDate = slot.dataset.date;

            if (slotDate === startSlot.dataset.date && slotMinutes >= minMinutes && slotMinutes <= maxMinutes) {
                slot.classList.remove('selected', 'selecting', 'deselecting');
                slot.classList.add(isDeselecting ? 'deselecting' : 'selecting');
            }
        });
    }

    async function handleTimeSlotMouseUp() {
        if (!isMouseDown || !startSlot) return;

        const selectedSlots = document.querySelectorAll('.selecting');
        const deselectedSlots = document.querySelectorAll('.deselecting');

        if (selectedSlots.length > 0) {
            const date = startSlot.dataset.date;
            const slots = Array.from(selectedSlots);
            const times = slots.map(slot => slot.dataset.time);
            const startTime = times[0];
            const lastTime = times[times.length - 1];
            const [lastHour, lastMinute] = lastTime.split(':').map(Number);
            const endTime = formatTime((lastHour * 60 + lastMinute) + CONFIG.SLOT_DURATION);

            const saved = await saveInterval(date, startTime, endTime);
            if (saved) {
                slots.forEach(slot => {
                    slot.classList.remove('selecting');
                    slot.classList.add('selected');
                });
            } else {
                slots.forEach(slot => {
                    slot.classList.remove('selecting');
                });
            }
        }

        if (deselectedSlots.length > 0) {
            const date = startSlot.dataset.date;
            const slots = Array.from(deselectedSlots);
            const times = slots.map(slot => slot.dataset.time);
            const startTime = times[0];
            const lastTime = times[times.length - 1];
            const [lastHour, lastMinute] = lastTime.split(':').map(Number);
            const endTime = formatTime((lastHour * 60 + lastMinute) + CONFIG.SLOT_DURATION);

            const deleted = await deleteInterval(date, startTime, endTime);
            if (deleted) {
                slots.forEach(slot => {
                    slot.classList.remove('deselecting', 'selected');
                });
            } else {
                slots.forEach(slot => {
                    slot.classList.remove('deselecting');
                    slot.classList.add('selected');
                });
            }
        }

        isMouseDown = false;
        startSlot = null;
    }

    // Функции для отображения/скрытия индикатора загрузки
    function showLoading() {
        document.getElementById('loading-overlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loading-overlay').style.display = 'none';
    }

    // Инициализация
    generateDays();
    loadIntervals();

    // Добавление обработчиков событий
    document.addEventListener('mouseup', handleTimeSlotMouseUp);
    document.addEventListener('mouseleave', handleTimeSlotMouseUp);

    calendarStrip.addEventListener('mouseover', e => {
        if (e.target.classList.contains('time-slot')) {
            handleTimeSlotMouseEnter(e);
        }
    });

    console.log('Schedule initialization completed');
});
</script>
@endpush
