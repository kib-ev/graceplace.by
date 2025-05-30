<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta http-equiv="Cache-Control" content="no-cache">

    <!-- META OG -->
{{--    <meta property="og:title" content="Your Page Title" />--}}
{{--    <meta property="og:description" content="Your Page Description" />--}}
    <meta property="og:image" content="{{ asset('/images/logo.jpg') }}" />
{{--    <meta property="og:url" content="Link to Your Page" />--}}

    <title>GracePlace Minsk</title>


    <link href="{{ asset('/build/assets/app-D-sv12UV.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>

        body {
            background-color: #f2f4f7 !important;
        }


        * {
            touch-action: manipulation;
        }
        table#appointmentsList tr th {
            background: #f3f3f3;
        }

        table tr.canceled td {
            background: #ffe4e4;
        }

        a {
            color: #333;
        }
        input {
            color: #333;
        }

        .logo {
            width: 20px;
            height: 20px;
            margin: 2px;
        }

        .self-added {
            color: #4ab728;
        }
        #places {
            display: flex;
            gap: 3px;
            margin-right: 5px;
        }
        .place {
            min-width: 170px;
        }
        .place .image img {
            border-radius: 4px;
        }

        .place .title {
            height: 60px;
            text-align: center;
            border: 1px solid #c7c7c7;
            margin: 2px 0px;
            background: #a9a9a9;
            color: #fff;
            vertical-align: middle;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 4px;
        }
        .place .time {
            user-select: none;
        }
        .place .hour {
            border: 1px solid #c7c7c7;
            margin-bottom: 2px;
            padding: 1px 5px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;

            white-space: nowrap;
        }
        .place .hour.busy {
            background: #ffdede;
        }
        .place .hour.busy.break {
            background: #ffebeb !important;
        }
        .place .hour.busy .info {
            color: #e5b7b7;
            float: right;
            font-size: 0.9em;
        }
        .place .hour.busy.master {
            background: #b5cfff;
        }
        .place .time.master .hour {
            background: #b5cfff !important;
        }
        .place .time.master .hour .info,
        .place .hour.busy.master .info {
            color: #95aedd;
        }
        .place .hour.free {
            background: #e1fbe1;
        }

        .place .hour .add-app {
            padding: 0px 5px;
        }

        .place .hour .add-app:hover {
            color: gold;
        }

        #appointmentsList .comments .comment .text {
            background: #fbffc5;
        }

        #appointmentsList .comments .comment.cancellation_reason .text {
            background: #f1aeb5 !important;
        }
    </style>

    <script src="{{ asset('/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('/build/assets/app-BkDPDVeP.js') }}"></script>

    <!-- Scripts -->
    {{--    @vite(['resources/sass/app.scss', 'resources/js/app.js'])--}}

    <!-- PWA -->
    <meta name="theme-color" content="#1a202c">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}?v={{ filemtime(public_path('manifest.json')) }}">

@if(in_array(request()->path(), ['/', '/login', 'register']))
        <!-- Meta Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '1892693701135735');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
                       src="https://www.facebook.com/tr?id=1892693701135735&ev=PageView&noscript=1"
            /></noscript>
        <!-- End Meta Pixel Code -->
    @endif
</head>
<body>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">

        <a class="navbar-brand" href="{{ url('https://graceplace.by') }}"><img class="logo" src="{{ asset('/images/logo.jpg') }}" alt="GracePlace Logo"> GracePlace</a>

        @if(auth()->user() && auth()->user()->hasRole('admin'))
            @include('admin.layouts.includes.menu')
        @endif

    </div>
</nav>

<div class="container">

    <div class="row mb-3 mt-3">
        <div class="col-12">
            @if(auth()->user())
                Вы вошли как: <b title="ID: {{ auth()->id() }}">{{ auth()->user()->name }}</b> <a href="/logout">Выйти</a>

                @if(auth()->user()->hasRole(['admin']))
                    <br>(админ)
                @endif

            @else
                <a href="{{ route('login') }}">Вход на сайт</a>
            @endif
        </div>
    </div>



    @if(auth()->user() && auth()->user()->hasRole('master') && auth()->user()->getBalance() > 0)
        <div class="row mb-3 mt-3">
            <a data-bs-toggle="collapse" href="#collapseBalance" role="button">
                Баланс
            </a>
            <div class="collapse" id="collapseBalance">
                <div class="card card-body">
                    <table class="table table-sm table-bordered table-responsive mb-0">
                        <tr>
                            <td>Сумма</td>
                            <td style="text-align: right;">{{ number_format(auth()->user()->getBalance(), 2, '.') }} BYN</td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>
    @endif

    @if(auth()->user() && auth()->user()->hasRole('master'))
        @php
            $masterAppointments = auth()->user()->appointments()->whereNull('canceled_at')->where('start_at', '>=', now())->get();
        @endphp

        <div class="row mb-3 mt-3">
            <div class="col-12">
                <a data-bs-toggle="collapse" href="#collapseAppointments" role="button">
                    Мои записи
                </a>
                <div class="collapse" id="collapseAppointments">
                    <div class="card card-body overflow-scroll">

                        @if($masterAppointments->count() > 0)
                            <table class="table table-sm table-bordered table-responsive mb-0">
                                <tr>
                                    <th class="bg-secondary text-white" colspan="3" >Дата и время</th>
                                    <th class="bg-secondary text-white">Рабочее место</th>
                                    <th class="bg-secondary text-white">Сумма</th>
                                    <th class="bg-secondary text-white">Отмена</th>
                                </tr>
                                @foreach($masterAppointments->load('place')->sortBy('start_at')->groupBy(function ($a) { return $a->start_at->isoFormat('D MMM'); }) as $masterDate => $masterAppointmentByDate)

                                    @foreach($masterAppointmentByDate as $nextAppointment)
                                        <tr data-index="{{ $loop->index }}" class="appointment-info {{ $masterAppointmentByDate->count() == 1 ? 'js_app_'.$nextAppointment->id : '' }}">

                                            @if($loop->index == 0)
                                                <td class="bg-white text-nowrap" style="width: 1%;" rowspan="{{ $masterAppointmentByDate->count() }}">
                                                    {{ $masterDate }}
                                                </td>
                                                <td class="bg-white text-nowrap" style="width: 1%;" rowspan="{{ $masterAppointmentByDate->count() }}">
                                                    <span>{{ mb_strtoupper($nextAppointment->start_at->isoFormat('dd')) }}</span>
                                                </td>
                                            @endif

                                            <td class="bg-white js_app_{{ $nextAppointment->id }}" style="display: none;">
                                                ID: <span class="js_appointment-id">{{ $nextAppointment->id }}</span>
                                            </td>

                                            <td class="bg-white js_app_{{ $nextAppointment->id }}" style="display: none;">
                                                <span class="js_appointment-date">{{ $nextAppointment->start_at->isoFormat('D MMM') }}</span>
                                            </td>

                                            <td class="bg-white text-nowrap js_app_{{ $nextAppointment->id }}" style="width: 1%;background: {{ $nextAppointment->is_full_day ? '#e0f9d8' : 'none' }};">

                                                <span class="js_appointment-time">
                                                    @if($nextAppointment->is_full_day)
                                                        Полный день
                                                    @else
                                                        {{ $nextAppointment->start_at->format('H:i') }} - {{ $nextAppointment->end_at->format('H:i') }}
                                                    @endif
                                                </span>

                                            </td>

                                            <td class="bg-white js_app_{{ $nextAppointment->id }} text-nowrap">
                                                <span class="js_appointment-place">{{ $nextAppointment->place->name }}</span>
                                            </td>

                                            <td class="bg-white text-nowrap text-end">
                                                {{ number_format((new \App\Services\AppointmentService())->calculateAppointmentCost($nextAppointment), 2) }} BYN
                                            </td>

                                            <td class="bg-white text-nowrap js_app_{{ $nextAppointment->id }}">
                                                @if(auth()->user() && auth()->user()->can('cancel appointment') && $nextAppointment->canBeCancelledByUser())
                                                    <a class="btn btn-sm btn-danger js_cancel-appointment" style="line-height: 13px;">
                                                        Отменить
                                                    </a>
                                                @else
                                                    <a target="_blank" href="https://ig.me/m/beautycoworkingminsk">Через Direct</a>
                                                @endif
                                            </td>


                                        </tr>

                                    @endforeach
                                @endforeach
                            </table>
                        @else
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <td>У вас нет предстоящих записей.</td>
                                </tr>
                            </table>
                        @endif

                        <p class="mt-1 mb-0">Отмена записей менее чем за {{ \App\Models\Appointment::CANCELLATION_TIMEOUT }} {{ trans_choice('час|часа|часов', \App\Models\Appointment::CANCELLATION_TIMEOUT) }}
                            производится <a target="_blank" href="https://ig.me/m/beautycoworkingminsk">через Direct</a>.</p>

                        <!-- Modal -->
                        <div class="modal fade" id="modalCancelAppointment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Отменить запись</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Вы собираетесь отменить запись:

                                        <table class="table-bordered table table-sm">
                                            <tr style="display: none;">
                                                <td>ID</td>
                                                <td><span class="js_appointment-id"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Дата</td>
                                                <td><span class="js_appointment-date"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Время</td>
                                                <td><span class="js_appointment-time"></span></td>
                                            </tr>
                                            <tr>
                                                <td>Рабочее место</td>
                                                <td><span class="js_appointment-place"></span></td>
                                            </tr>
                                        </table>

                                        <div class="form-group">
                                            <label for="">Укажите, пожалуйста, причину отмены:</label>
                                            <textarea class="form-control js_appointment-cancel-reason" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                        <button id="sendCancelAppointmentData" type="button" class="btn btn-danger">Да, отменить</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @php
            $masterEndedAppointments = auth()->user()->appointments()->where('price', '>', 0)->whereNull('canceled_at')->where('start_at', '<', now())->where('start_at', '>=', '2025-01-01 00:00')->where('start_at', '>=', now()->subDays(30))->get();
        @endphp

        @if(count($masterEndedAppointments) > 0)
            <div class="row mb-3 mt-3">
                <div class="col-12">
                    <a data-bs-toggle="collapse" href="#collapseDocs" role="button">
                        Акты
                    </a>
                    <div class="collapse" id="collapseDocs">
                        <div class="card card-body overflow-scroll">

                            <table class="table table-sm table-bordered table-responsive mb-0">
                                <tr>
                                    <th class="bg-secondary text-white" colspan="2" >Дата и время</th>
                                    <th class="bg-secondary text-white">Рабочее место</th>
                                    <th class="bg-secondary text-white">Сумма</th>
                                    <th class="bg-secondary text-white"></th>
                                </tr>
                                @foreach($masterEndedAppointments as $nextAppointment)
                                    <tr class="appointment-info">

                                        <td class="text-nowrap" style="width: 1%;">
                                            {{ $nextAppointment->start_at->format('d.m.Y') }}
                                        </td>

                                        <td class="text-nowrap js_app_{{ $nextAppointment->id }}" style="display: none;">
                                            ID: <span class="js_appointment-id">{{ $nextAppointment->id }}</span>
                                        </td>

                                        <td class="text-nowrap js_app_{{ $nextAppointment->id }}" style="display: none;">
                                            <span class="js_appointment-date">{{ $nextAppointment->start_at->isoFormat('D MMM') }}</span>
                                        </td>

                                        <td class="text-nowrap js_app_{{ $nextAppointment->id }}" style="width: 1%;background: {{ $nextAppointment->is_full_day ? '#e0f9d8' : 'none' }};">

                                        <span class="text-nowrap js_appointment-time">
                                            @if($nextAppointment->is_full_day)
                                                Полный день
                                            @else
                                                {{ $nextAppointment->start_at->format('H:i') }} - {{ $nextAppointment->end_at->format('H:i') }}
                                            @endif
                                        </span>

                                        </td>

                                        <td class="text-nowrap js_app_{{ $nextAppointment->id }}">
                                            <span class="js_appointment-place">{{ $nextAppointment->place->name }}</span>
                                        </td>

                                        <td class="text-nowrap text-end">
                                            {{ number_format((new \App\Services\AppointmentService())->calculateAppointmentCost($nextAppointment), 2) }} BYN
                                        </td>

                                        <td>
                                            <a target="_blank" href="/user/documents/{{ $nextAppointment->id }}?download">Скачать</a>
                                        </td>

                                    </tr>

                                @endforeach
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif


    {{-- Storage Cell --}}
    @if(auth()->user())

        @php
            $bookings = \App\Models\StorageBooking::whereNull('finished_at')->where('user_id', auth()->id())->get();
        @endphp

        @if(count($bookings))
            <div class="row mb-3 mt-3">
                <div class="col">
                    <a data-bs-toggle="collapse" href="#collapseStorageCells" role="button">Локер</a>

                    @if($bookings->first()->daysLeft() <= 0)
                        <span class="bg-danger text-white p-1 px-2"> <b>ПРОСРОЧЕНО: {{ abs($bookings->first()->daysLeft()) }} {{ trans_choice('день|дня|дней', $bookings->first()->daysLeft()) }}</b></span>
                    @endif

                    <div class="collapse" id="collapseStorageCells">
                        <div class="card card-body">

                            @foreach($bookings as $booking)
                                @if($loop->index > 0)
                                    <div class="mt-4"></div>
                                @endif

                                <table class="table table-bordered table-sm mb-0">
                                    <tr>
                                        <td>Номер ячейки</td>
                                        <td>{{ $booking->cell->number }}</td>
                                    </tr>

                                    <tr>
                                        <td style="width: 1%; white-space: nowrap;">Статус</td>
                                        <td>
                                            @if($bookings->first()->daysLeft() <= 0)
                                                <span style="color: red;">Требуется оплата</span>
                                            @else
                                                <span style="color: green;">Оплачено</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="width: 1%; white-space: nowrap;">Дата начала</td>
                                        <td>{{ $booking->start_at->format('d-m-Y') }}</td>
                                    </tr>

                                    <tr>
                                        <td style="width: 1%; white-space: nowrap;">Дата окончания</td>
                                        <td>
                                            {{ $booking->start_at->addDays($booking->duration)->format('d-m-Y') }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="width: 1%; white-space: nowrap;">Осталось</td>
                                        <td>
                                            @php
                                                $leftLockerDays = now()->diffInDays($booking->start_at->addDays($booking->duration), false);
                                            @endphp

{{--                                            @if($leftLockerDays > 0)--}}
                                                {{ $leftLockerDays }} {{ trans_choice('день|дня|дней', $leftLockerDays) }}
{{--                                            @else--}}
{{--                                                0 дней--}}
{{--                                            @endif--}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="width: 1%; white-space: nowrap;">Стоимость продления</td>
                                        <td>
                                            {{ $bookings->first()->cell->cost_per_month }} BYN / 30 дней
                                        </td>
                                    </tr>

                                    @if(isset($bookings->first()->cell->secret))
                                        <tr>
                                            <td style="width: 1%; white-space: nowrap;">Код</td>
                                            <td>
                                                {{ $bookings->first()->cell->secret }}
                                            </td>
                                        </tr>
                                    @endif


                                </table>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif


    {{-- Settings --}}
    @if(auth()->user() && auth()->user()->hasRole('master'))
        <div class="row mb-3 mt-3">
            <div class="col">

                <a data-bs-toggle="collapse" href="#collapseSettings" role="button">
                    Настройки
                </a>

                <div class="collapse" id="collapseSettings">
                    <div class="card card-body">

                        <form action="{{ route('user.update-settings') }}" method="POST">
                            @csrf
                            <h3>Показывать рабочие места:</h3>
                            @foreach(\App\Models\Place::where('is_hidden', false)->orderBy('sort')->get() as $workspace)
                                <div>
                                    <input id="workspace_{{ $workspace->id }}" type="checkbox" name="workspace_visibility[]" value="{{ $workspace->id }}"
                                        {{ in_array($workspace->id, auth()->user()->getSetting('workspace_visibility', [])) ? 'checked' : '' }}>
                                    <label for="workspace_{{ $workspace->id }}">{{ $workspace->name }}</label>
                                </div>
                            @endforeach
                            <button id="saveSettings" type="submit">Сохранить</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endif

    @yield('content')
</div>

<footer>
    <div class="container">
        <div class="row">
            <div style="margin-bottom: 200px;"></div>
        </div>
    </div>
</footer>

<script src="{{ asset('/sw.js') }}"></script>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js?v={{ filemtime(public_path("sw.js")) }}')
            .then(() => console.log("Service Worker registered"))
            .catch(e => console.error("SW registration failed", e));
    }
</script>

</body>
</html>
