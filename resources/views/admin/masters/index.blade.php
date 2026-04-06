@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Список мастеров</h1>

            <div class="form">
                <form id="searchMaster" method="get" autocomplete="off">
                    @if(request('category_id'))
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                    @endif
                    <div class="form-group" style="width: 300px; display: inline-block;">
                        <input class="form-control"  type="text" name="search" value="{{ request('search') }}" placeholder="Имя, Фамилия, ID диркет">
                    </div>
                    <input class="btn btn-primary" type="submit" value="Найти">

                    @if(request('search'))
                        <a class="btn btn-danger" href="{{ route('admin.masters.index', array_filter(['category_id' => request('category_id'), 'is_active' => request('is_active')])) }}" >X</a>
                    @endif
                </form>
            </div>

            <hr>
            <a href="{{ route('admin.masters.create') }}" class="btn btn-primary">Создать</a>
            <hr>

            @if(isset($currentCategory))
                <div class="alert alert-info py-2 mb-2">
                    Категория: <strong>{{ $currentCategory->name }}</strong>
                    <a href="{{ route('admin.masters.index', array_filter(['is_active' => request('is_active'), 'search' => request('search')])) }}" class="btn btn-sm btn-outline-secondary ms-2">Показать всех</a>
                </div>
            @endif

            @php $filterParams = array_filter(['category_id' => request('category_id'), 'is_active' => request('is_active'), 'search' => request('search')]); @endphp
            <a href="{{ route('admin.masters.index', array_merge($filterParams, ['is_active' => 1])) }}" class="{{ request('is_active') === '1' ? 'fw-bold' : '' }}">
                Активные ({{ $activeCount }})
            </a>
            <a href="{{ route('admin.masters.index', array_merge($filterParams, ['is_active' => 0])) }}" class="{{ request('is_active') === '0' ? 'fw-bold' : '' }}" style="margin-left: 10px;">
                Неактивные ({{ $inactiveCount }})
            </a>

            <div style="margin-top: 10px;">
                @php
                    $baseParams = array_filter(['category_id' => request('category_id'), 'is_active' => request('is_active'), 'search' => request('search')]);
                    $debtorsCount = request('is_active') === '0' ? $debtorsInactiveCount : (request('is_active') === '1' ? $debtorsActiveCount : $debtorsActiveCount + $debtorsInactiveCount);
                @endphp
                <a href="{{ route('admin.masters.index', $baseParams) }}" class="{{ !request('debtors') ? 'fw-bold' : '' }}">
                    Все
                </a>
                <a href="{{ route('admin.masters.index', array_merge($baseParams, ['debtors' => 1])) }}" class="{{ request('debtors') == '1' ? 'fw-bold' : '' }}" style="margin-left: 10px;">
                    Должники ({{ $debtorsCount }})
                </a>
            </div>

            <table class="table table-bordered mb-5">
                <tr>
                    <td style="width: 50px;"></td>
                    <td style="width: 80px;"></td>
                    <td style="padding: 2px;"></td>
                    <td style="width: 440px;">Имя мастера</td>
                    <td style="width: 140px;">Телефон</td>
                    <td>Инста</td>
                    <td>Директ</td>
                    <td>Услуги</td>
                    <td>Дата <br> регистрации</td>
                    <td>Записи</td>
                    <td>Последний <br> визит</td>
                    <td></td>
                </tr>
                @foreach($masters as $master)
                    @if($master->user)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>
                            @if($master->avatar)
                                <img src="{{ Illuminate\Support\Facades\Storage::url($master->avatar) }}" class="img-fluid rounded mb-3" alt="{{ $master->full_name }}">
                            @endif
                        </td>

                        <td title="ЕРИП" style="width: 2px; padding: 2px; background: {{ $master->user->getSetting('payment_link.place') && $master->user->getSetting('payment_link.storage') ? '#4ab728' : '#ff2318' }}"></td>

                        <td title="master_id: {{ $master->id }} | user_id: {{ $master->user_id }}">
                            <a href="{{ route('admin.masters.show', $master) }}">{{ $master->full_name }}</a>
                            @if(is_null($master->patronymic))
                                <span style="color: red;">(отчество)</span>
                            @endif

                            @if(($master->debt_amount_byn ?? 0) > 0)
                                <div class="bg-danger text-white p-2">Задолженность: {{ number_format($master->debt_amount_byn, 2) }} </div>
                            @endif

                            @php $adminComments = $master->comments->where('type', 'admin'); @endphp
                            @if($adminComments->count() > 0)
                                <div class="master-comments-block">
                                    <span class="master-comments-toggle" style="cursor: pointer; text-decoration: underline; color: #aaaaaa; font-size: 0.9em;" data-target="master-comments-{{ $master->id }}">
                                        Комментарии ({{ $adminComments->count() }})
                                    </span>
                                    <div id="master-comments-{{ $master->id }}" class="master-comments-content" style="display: none; margin-top: 8px;">
                                        @include('admin.comments.includes.widget', ['model' => $master, 'title' => '', 'type' => 'admin', 'showForm' => false, 'showControl' => false])
                                    </div>
                                </div>
                            @endif
                        </td>

                        <td>
                            <ul style="list-style-type: none; margin-bottom: 0px; padding: 0px;">
                                <li>{{ $master->user->phone }}</li>
                            </ul>
                        </td>

                        <td>
                            @if(isset($master->instagram) && $master->instagram !== '')
                                <a target="_blank" href="{{ $master->instagram }}">Inst</a>
                            @endif
                        </td>

                        <td>
                            @if(isset($master->direct))
                                <span class="float-end">
                                    <a href="{{ $master->direct }}" target="_blank">direct</a>
                                </span>
                            @endif
                        </td>

                        <td>{{ $master->description }}</td>


                        <td>
                            {{ $master->created_at->format('d.m.Y') }}
                        </td>

                        <td style="white-space: nowrap;">
                            {{ $totalCount = (int)($master->appointments_total_count ?? 0) }} /
                            {{ $visitCount = (int)($master->appointments_visit_count ?? 0) }} /
                            {{ $cancelCount = (int)($master->appointments_cancel_count ?? 0) }}
                            @if($visitCount > 0 && $master->appointments_avg_duration)
                                <br>{{ number_format((float)$master->appointments_avg_duration / 60, 2) }} ч
                            @endif

                            <br>
                            @if($totalCount > 10)
                                @php
                                    $lateCancelCount = (int)($master->late_cancel_count ?? 0);
                                    $lateCancelPercent = $totalCount > 0 ? ($lateCancelCount / $totalCount * 100) : 0;
                                @endphp
                                <span class="{{ $lateCancelCount > 10 ? 'bg-danger text-white' : '' }}">{{ number_format($lateCancelPercent) }} %</span>
                            @endif
                            <br>
                        </td>

                        <td style="white-space: nowrap;">
                            @php
                                $lastAt = $master->last_appointment_at ?? null;
                            @endphp
                            @if($lastAt && \Carbon\Carbon::parse($lastAt) < now())
                                {{ \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($lastAt)) }} д. назад
                            @elseif($lastAt && \Carbon\Carbon::parse($lastAt) >= now())
                                <span style="color: greenyellow;">запись</span>
                            @else
                                <span style="color: orangered;">нет</span>
                            @endif
                        </td>

                        <td><a href="{{ route('admin.masters.edit', $master) }}">edit</a></td>
                    </tr>
                    @else
                        <tr>
                            <td colspan="11">
                                {{ $master->full_name }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </table>

        </div>
    </div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.master-comments-toggle').forEach(function(el) {
    el.addEventListener('click', function() {
        var target = document.getElementById(this.getAttribute('data-target'));
        if (target) {
            target.style.display = target.style.display === 'none' ? 'block' : 'none';
        }
    });
});
</script>
@endsection
