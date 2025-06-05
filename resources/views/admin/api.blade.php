@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <h1>Тестирование API</h1>

        <h3>Создать бронирование</h3>
        <form id="form-create-appointment">
            <div class="mb-2">
                <input type="number" name="user_id" placeholder="user_id" class="form-control" required>
            </div>
            <div class="mb-2">
                <input type="number" name="place_id" placeholder="place_id" class="form-control" required>
            </div>
            <div class="mb-2">
                <input type="datetime-local" name="start_at" placeholder="start_at" class="form-control" required>
            </div>
            <div class="mb-2">
                <input type="number" name="duration" placeholder="duration (минут)" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Создать</button>
        </form>
        <pre id="result-create-appointment"></pre>

        <hr>
        <h3>Получить свободные интервалы</h3>
        <form id="form-free-slots">
            <div class="mb-2">
                <input type="number" name="place_id" placeholder="place_id" class="form-control" required>
            </div>
            <div class="mb-2">
                <input type="date" name="date" class="form-control">
            </div>
            <div class="mb-2">
                <input type="number" name="duration" placeholder="duration (минут)" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Получить</button>
        </form>
        <pre id="result-free-slots"></pre>

        <hr>
        <h3>Список всех рабочих мест</h3>
        <button id="btn-places-list" class="btn btn-secondary mb-2">Получить список</button>
        <pre id="result-places-list"></pre>

        <hr>
        <h3>Список всех мастеров</h3>
        <button id="btn-masters-list" class="btn btn-secondary mb-2">Получить список</button>
        <pre id="result-masters-list"></pre>
    </div>
@endsection

@section('scripts')
<script>
function apiUrl(path) {
    return window.location.origin + '/api' + path;
}

document.getElementById('form-create-appointment').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = Object.fromEntries(new FormData(form));
    const res = await fetch(apiUrl('/appointments'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    document.getElementById('result-create-appointment').textContent = await res.text();
};

document.getElementById('form-free-slots').onsubmit = async function(e) {
    e.preventDefault();
    const form = e.target;
    const place_id = form.place_id.value;
    const params = new URLSearchParams();
    if(form.date.value) params.append('date', form.date.value);
    if(form.duration.value) params.append('duration', form.duration.value);
    const res = await fetch(apiUrl(`/places/${place_id}/free-slots?` + params.toString()));
    document.getElementById('result-free-slots').textContent = await res.text();
};

document.getElementById('btn-places-list').onclick = async function() {
    const res = await fetch(apiUrl('/places-list'));
    document.getElementById('result-places-list').textContent = await res.text();
};

document.getElementById('btn-masters-list').onclick = async function() {
    const res = await fetch(apiUrl('/masters-list'));
    document.getElementById('result-masters-list').textContent = await res.text();
};
</script>
@endsection
