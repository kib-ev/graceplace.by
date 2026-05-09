@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Должники</h1>

            <form method="get" class="mb-3 d-flex align-items-end gap-2">
                <div>
                    <label for="is_active" class="form-label mb-1">Статус</label>
                    <select id="is_active" name="is_active" class="form-select form-select-sm">
                        <option value="">Все</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Активные ({{ $activeCount ?? 0 }})</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Неактивные ({{ $inactiveCount ?? 0 }})</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Фильтр</button>
                @if(request()->has('is_active') && request('is_active') !== '')
                    <a href="{{ route('admin.users.debtors') }}" class="btn btn-sm btn-outline-secondary">Сброс</a>
                @endif
            </form>

            <table class="table table-bordered mb-5 js-persist-highlight-table" data-highlight-key="users-debtors-row-highlight">
                <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Статус</th>
                    <th class="text-end">Записи, BYN</th>
                    <th class="text-end">Локер, BYN</th>
                    <th class="text-end">Итого, BYN</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->id }}</td>
                        <td>
                            @if($user->master)
                                <a href="{{ route('admin.masters.show', $user->master) }}">{{ $user->name }}</a>
                            @else
                                {{ $user->name }}
                            @endif
                        </td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->is_active ? 'Активен' : 'Неактивен' }}</td>
                        @php
                            $appointmentsDebt = (float) ($user->appointments_debt_amount_byn ?? 0);
                            $storageDebt = (float) ($user->storage_debt_amount_byn ?? 0);
                            $totalDebt = $appointmentsDebt + $storageDebt;
                        @endphp
                        <td class="text-end" style="{{ $appointmentsDebt > 0 ? 'background: #dc3545 !important; color: #fff !important;' : '' }}">
                            {{ number_format($appointmentsDebt, 2) }}
                        </td>
                        <td class="text-end" style="{{ $storageDebt > 0 ? 'background: #dc3545 !important; color: #fff !important;' : '' }}">
                            {{ number_format($storageDebt, 2) }}
                        </td>
                        <td class="text-end fw-bold">{{ number_format($totalDebt, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Должников не найдено</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
