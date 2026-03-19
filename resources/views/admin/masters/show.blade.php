@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Мастер - {{ $master->full_name }}</h1>
            <div class=""><span style="background: #fff; color: #ccc; padding: 3px 8px; font-size: 12px;">master_id: {{ $master->id }}; user_id: {{ $master->user_id }}</span></div>
            <hr>

            @php $debtAmount = $master->getDebtAmount(); @endphp
            @if($debtAmount > 0)
                <div class="bg-danger text-white p-3 mb-3">
                    <span style="font-size: 1.4em;">Задолженность: {{ number_format($debtAmount, 2) }}</span>
                    <table class="table table-sm table-bordered text-white mb-0 mt-2" style="width: auto;">
                        <thead>
                            <tr>
                                <th class="pe-3">Дата</th>
                                <th class="pe-3">Место</th>
                                <th class="text-end pe-3">Долг, BYN</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($master->getDebtAppointments() as $da)
                                @php $penaltyReq = $da->paymentRequirements->firstWhere(fn($r) => $r->isPenalty()); @endphp
                                <tr>
                                    <td class="pe-3">{{ $da->start_at->format('d.m.Y H:i') }}</td>
                                    <td class="pe-3">{{ $da->place->name ?? '—' }}</td>
                                    <td class="text-end pe-3">
                                        @if($penaltyReq)
                                            <span class="badge bg-warning text-dark me-1">{{ $penaltyReq->getPenaltyLabel() }}</span>
                                        @endif
                                        {{ number_format($da->paymentRequirements->sum('remaining_amount'), 2) }}
                                    </td>
                                    <td><a href="{{ route('admin.appointments.edit', $da) }}" class="text-black"><i class="fa fa-edit"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#nav-info" type="button" role="tab">Информация</button>
                    <button id="nav-appointments-tab" data-bs-target="#nav-appointments" class="nav-link"  data-bs-toggle="tab" type="button" role="tab">Записи ({{ count($master->user->appointments) }})</button>
                    <button id="nav-stats-tab" data-bs-target="#nav-stats" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Статистика</button>
                    <button id="nav-comments-tab" data-bs-target="#nav-comments" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Комментарии ({{ $master->comments()->count() }})</button>
                    <button id="nav-payment-tab" data-bs-target="#nav-payment" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Ссылки ЕРИП ({{ !empty($master->user->getSetting('payment_link.place')) + !empty($master->user->getSetting('payment_link.storage'))  }})</button>

                    @if(count($master->user->storageBookings))
                        <button id="nav-storage-tab" data-bs-target="#nav-storage" class="nav-link" data-bs-toggle="tab" type="button" role="tab">Локер ({{ count($master->user->storageBookings) }})</button>
                    @endif

                    <button id="nav-permissions-tab" data-bs-target="#nav-permissions" class="nav-link"  data-bs-toggle="tab" type="button" role="tab">Права ({{ $master->user->getAllPermissions()->count() }}/2)</button>

                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div id="nav-info" class="tab-pane fade show active" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        <table class="table table-bordered">
                            <tr>
                                <td>
                                    <ul style="list-style-type: none; margin: 0px; padding: 0px;">
                                        <li>{{ $master->phone }}</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {{ $master->description }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Категории услуг:</strong>
                                    <form action="{{ route('admin.masters.service-categories.update', $master) }}" method="post" class="mt-2">
                                        @csrf
                                        <div class="row">
                                            @foreach($serviceCategories as $category)
                                                @php $isRecommended = in_array($category->id, $recommendedCategoryIds ?? []); @endphp
                                                <div class="col-md-6 col-lg-4 mb-1">
                                                    <div class="form-check {{ $isRecommended ? 'text-success' : '' }}" title="{{ $isRecommended ? 'Рекомендовано по описанию' : '' }}">
                                                        <input class="form-check-input" type="checkbox" name="service_category_ids[]" value="{{ $category->id }}" id="cat-{{ $category->id }}"
                                                            {{ $master->serviceCategories->contains($category) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="cat-{{ $category->id }}">
                                                            {{ $category->name }}
                                                            @if($isRecommended)<span class="badge bg-success bg-opacity-25 ms-1">рекомендуется</span>@endif
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="btn btn-primary btn-sm mt-2" type="submit">Сохранить</button>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <td>{{ $master->instagram }}</td>
                            </tr>

                            <tr>
                                <td>
                                    @if($master->direct)
                                        <a target="_blank" href="{{ $master->direct }}">Написать в Direct</a>
                                    @else

                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td>Количество записей: {{ $totalCount }}</td>
                            </tr>

                            <tr>
                                <td>
                                    Количество отмен: {{ $cancelCount }}
                                    / {{ $totalCount ? number_format($cancelCount / $totalCount * 100, 0) : '0' }} %
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    Количество посещений: {{ $visitCount }}
                                    / {{ $totalCount ? number_format($visitCount / $totalCount * 100, 0) : '0' }} %
                                </td>
                            </tr>

                            <tr>
                                <td>СУММА: {{ $sum = $sumExpected }} BYN</td>
                            </tr>
                            <tr>
                                <td>ОПЛАЧЕНО: {{ $sumPaid }} BYN</td>
                            </tr>

                            <tr>
                                <td>Всего часов: {{ $hours = $totalMinutes / 60 }}</td>
                            </tr>

                            <tr>
                                <td>Сред. стоимость часа: {{ $hours ? $sum / $hours : 0 }}</td>
                            </tr>

                            <tr>
                                <td>

                                    <span style="background: #f7f7cd; padding: 5px 10px;">Ваш логин: {{ $master->user->phone }} пароль: graceplace{{ $master->id }}</span>

                                    <span style="float: right"><a href="{{ url('/admin/users/' . $master->user->id . '/login') }}"><i class="fa fa-sign-in"></i></a></span>
                                </td>
                            </tr>

                            <tr>
                                <td style="background: lightgoldenrodyellow">
                                    <input class="form-control" type="text" value="{{ $master->phone }} {{ $master->full_name }}" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td style="background: lightgoldenrodyellow">
                                    <input class="form-control" type="text" value="{{ $master->user->email }}" readonly>
                                </td>
                            </tr>


                            <tr>
                                <td>
                                    {{ json_encode($master->user->getSetting('workspace_visibility')) }}
                                </td>
                            </tr>

                        </table>

                        <div class="text-end">
                            <a class="btn btn-primary" href="{{ route('admin.masters.edit', $master) }}">Изменить</a>
                        </div>

                        @if($master->user->appointments->count() == 0)
                            <form action="{{ route('admin.masters.destroy', $master) }}" method="post">
                                @method('delete')
                                @csrf
                                <button class="btn btn-danger">удалить</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div id="nav-payment" class="tab-pane fade" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        @if(isset($master))
                            <form class="mb-4" action="{{ route('admin.update-settings') }}" method="post" autocomplete="off">
                                @csrf
                                @method('post')

                                <input type="hidden" name="user_id" value="{{ $master->user_id }}">

                                <!-- Оплата МЕСТА -->
                                <label>
                                    Ссылка для оплаты МЕСТА [10{{ str_pad($master->id, 3, '0', STR_PAD_LEFT) }}]:
                                </label>
                                @if($master->user->getSetting('payment_link.place'))
                                    <span style="padding: 0 5px; background: {{ str_contains($master->user->getSetting('payment_link.place'), 10 . str_pad($master->id, 3, '0', STR_PAD_LEFT)) ? '#c1edc1' : 'none' }}">
            {{ substr($master->user->getSetting('payment_link.place'), 63, 14) }}
        </span>
                                @endif
                                <input class="form-control" type="text" value="Оплата аренды рабочего места. Публичная оферта от 01.01.2025 г." readonly>

                                <div class="d-flex align-items-center mb-3">
                                    <input class="form-control d-inline-block mr-2" style="flex: 1;" type="text" name="payment_link_place" id="payment_link_input" value="{{ $master->user->getSetting('payment_link.place') }}">
                                    <button class="btn btn-secondary d-inline-block mr-2" type="button" onclick="openQrScanner('payment_link_input')">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>

                                <!-- Оплата ЛОКЕРА -->
                                <label>
                                    Ссылка для оплаты ЛОКЕРА [20{{ str_pad($master->id, 3, '0', STR_PAD_LEFT) }}]:
                                </label>
                                @if($master->user->getSetting('payment_link.storage'))
                                    <span style="padding: 0 5px; background: {{ str_contains($master->user->getSetting('payment_link.storage'), 20 . str_pad($master->id, 3, '0', STR_PAD_LEFT)) ? '#c1edc1' : 'none' }}">
            {{ substr($master->user->getSetting('payment_link.storage'), 63, 14) }}
        </span>
                                @endif
                                <input class="form-control" type="text" value="Оплата аренды локера. Публичная оферта от 01.01.2025 г." readonly>

                                <div class="d-flex align-items-center mb-3">
                                    <input class="form-control d-inline-block mr-2" style="flex: 1;" type="text" name="payment_link_storage" id="locker_link_input" value="{{ $master->user->getSetting('payment_link.storage') }}">
                                    <button class="btn btn-secondary d-inline-block mr-2" type="button" onclick="openQrScanner('locker_link_input')">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                </div>

                                <button class="btn btn-primary" type="submit">Сохранить</button>
                            </form>


                        @endif
                    </div>
                </div>

                <div id="nav-stats" class="tab-pane fade"  role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        @php
                            $currentYear = now()->year;
                            $displayYears = array_reverse(range($currentYear - 2, $currentYear));
                        @endphp

                        @foreach($displayYears as $yr)
                            <h6>{{ $yr }}</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <th></th>
                                    @for($i = 1; $i <=12; $i++)
                                        <th>{{ \Carbon\Carbon::parse('01-'. $i . '-' . $yr)->format('M') }}</th>
                                    @endfor
                                </tr>
                                <tr>
                                    <td><b>Сумма оплат</b></td>
                                    @for($i = 1; $i <=12; $i++)
                                        <td class="text-end">{{ number_format($expectedByMonth[$yr][$i] ?? 0, 2) }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    <td><b>Часов аренды</b></td>
                                    @for($i = 1; $i <=12; $i++)
                                        <td class="text-end">{{ number_format(($durationByMonth[$yr][$i] ?? 0) / 60, 2) }}</td>
                                    @endfor
                                </tr>
                            </table>
                            <br>
                        @endforeach

                        <h6>Текущий год по площадкам ({{ $currentYear }})</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th></th>
                                @for($i = 1; $i <=12; $i++)
                                    <th>{{ \Carbon\Carbon::parse('01-'. $i . '-' . $currentYear)->format('M') }}</th>
                                @endfor
                            </tr>
                            @foreach($master->user->appointments->unique('place_id') as $uniqueAppointment)
                                <tr>
                                    <th rowspan="2">{{ $uniqueAppointment->place->name }}</th>
                                    @for($i = 1; $i <=12; $i++)
                                        <td class="text-end">{{ ($placeDuration[$uniqueAppointment->place_id][$i] ?? 0) / 60 }}</td>
                                    @endfor
                                </tr>
                                <tr>
                                    @for($i = 1; $i <=12; $i++)
                                        <td class="text-end">{{ number_format($placeExpected[$uniqueAppointment->place_id][$i] ?? 0, 2) }}</td>
                                    @endfor
                                </tr>
                            @endforeach
                        </table>

                    </div>
                </div>

                <div id="nav-comments" class="tab-pane fade" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        <div class="comments">
                            @include('admin.comments.includes.widget', ['model' => $master, 'title' => 'Комментарий', 'type' => 'admin'])
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="nav-appointments" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        <a class="btn btn-primary me-3 mb-3" href="https://graceplace.by/admin/appointments/create?master_id={{ $master->id }}">Добавить запись</a>
                        @include('admin.appointments.includes.table', ['appointments' => $master->user->appointments])
                    </div>
                </div>

                @if(count($master->user->storageBookings))
                    <div id="nav-storage" class="tab-pane fade" role="tabpanel" tabindex="0">
                        <div class="tab bg-light p-3">
                            <div class="storageBookings">
                                <table class="table table-bordered">
                                    @foreach($master->user->storageBookings as $storageBooking)
                                        <tr>
                                            <td><a href="{{ route('admin.storage-bookings.edit', $storageBooking) }}">{{ $storageBooking->cell->number }}</a></td>
                                        </tr>
                                        <tr>
                                            <td>Осталось дней: {{ $storageBooking->daysLeft() }}</td>
                                        </tr>
                                        <tr>
                                           <td>
                                               <div class="comments">
                                                   @include('admin.comments.includes.widget', ['model' => $storageBooking, 'title' => 'Комментарий', 'type' => 'admin'])
                                               </div>
                                           </td>
                                        </tr>
                                        <tr>
                                            <td>Код: {{ $storageBooking->cell->secret }}</td>
                                        </tr>
                                    @endforeach
                                </table>

                            </div>
                        </div>
                    </div>
                @endif

                <div class="tab-pane fade" id="nav-permissions" role="tabpanel" tabindex="0">
                    <div class="tab bg-light p-3">
                        <h3>Управление правами пользователя</h3>
                        <form action="{{ route('admin.permissions.update', $master->user) }}" method="post">
                            @csrf
                            <div class="form-group">
                                <input id="add_{{ $master->user->id }}" type="checkbox" name="add_{{ $master->user->id }}" class="cancel-checkbox" {{ $master->user->can('add appointment') ? 'checked' : '' }}>
                                <label for="add_{{ $master->user->id }}">Добавление записи</label>
                            </div>

                            <div class="form-group">
                                <input id="cancel_{{ $master->user->id }}" type="checkbox" name="cancel_{{ $master->user->id }}" class="cancel-checkbox" {{ $master->user->can('cancel appointment') ? 'checked' : '' }}>
                                <label for="cancel_{{ $master->user->id }}">Отмена записи</label>
                            </div>

                            <button class="btn btn-primary mt-3" type="submit">Сохранить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let scannerModal = null;
        let html5QrCode = null;

        async function openQrScanner(inputId) {
            if (scannerModal) return;

            scannerModal = document.createElement('div');
            scannerModal.style = `
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7);
      display: flex; justify-content: center; align-items: center;
      z-index: 10000;
    `;
            scannerModal.innerHTML = `
      <div style="background: white; padding: 20px; border-radius: 8px; text-align: center;">
        <div id="qr-reader" style="width: 300px"></div>
        <br/>
        <button id="closeBtn" style="margin-top: 10px;">Закрыть</button>
      </div>
    `;
            document.body.appendChild(scannerModal);
            document.getElementById('closeBtn').onclick = closeQrScanner;

            html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: 250
                },
                (decodedText, decodedResult) => {
                    const input = document.getElementById(inputId);
                    input.value = decodedText;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    setTimeout(closeQrScanner, 300);
                },
                (errorMessage) => {
                    // ignore errors
                }
            );
        }

        function closeQrScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    html5QrCode = null;
                });
            }
            if (scannerModal) {
                scannerModal.remove();
                scannerModal = null;
            }
        }
    </script>

@endsection
