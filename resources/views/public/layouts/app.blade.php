<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta http-equiv="Cache-Control" content="no-cache">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- META OG -->
{{--    <meta property="og:title" content="Your Page Title" />--}}
{{--    <meta property="og:description" content="Your Page Description" />--}}
    <meta property="og:image" content="{{ asset('/images/logo.jpg') }}" />
{{--    <meta property="og:url" content="Link to Your Page" />--}}

    <title>Grace Place Minsk</title>

    <!-- Styles -->
    <link href="{{ asset('/build/assets/app-D-sv12UV.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('styles.css') }}?{{ filemtime(public_path('styles.css')) }}">
    @stack('styles')

    <!-- Scripts -->
    {{--    @vite(['resources/sass/app.scss', 'resources/js/app.js'])--}}

    <script src="{{ asset('/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('/build/assets/app-BkDPDVeP.js') }}"></script>

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

        <a class="navbar-brand" href="{{ url('https://graceplace.by') }}"><img class="logo" src="{{ asset('/images/logo.jpg') }}" alt="Grace Place Logo"> Grace Place</a>

        @role('admin')
            @include('admin.layouts.includes.menu')
        @endrole

    </div>
</nav>

<div class="container">

    <div class="row mb-3 mt-3">
        <div class="col-12">
            @if(auth()->user())
                Вы вошли как: <b title="ID: {{ auth()->id() }}">{{ auth()->user()->name }}</b> <a href="/logout">Выйти</a>

                @role('admin')
                    <br>(админ)
                @endrole

            @else
                <a href="{{ route('login') }}">Вход на сайт</a>
            @endif
        </div>
    </div>

{{--    @if(auth()->user() && auth()->user()->hasRole('master') && auth()->user()->getBalance() > 0)--}}
{{--        <div class="row mb-3 mt-3">--}}
{{--            <a data-bs-toggle="collapse" href="#collapseBalance" role="button">--}}
{{--                Баланс--}}
{{--            </a>--}}
{{--            <div class="collapse" id="collapseBalance">--}}
{{--                <div class="card card-body">--}}
{{--                    <table class="table table-sm table-bordered table-responsive mb-0">--}}
{{--                        <tr>--}}
{{--                            <td>Сумма</td>--}}
{{--                            <td style="text-align: right;">{{ number_format(auth()->user()->getBalance(), 2, '.') }} BYN</td>--}}
{{--                        </tr>--}}
{{--                    </table>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    @endif--}}

    @section('master-menu')
        @include('public.layouts.includes.master-menu')
    @show

    @yield('content')

    @stack('modals')
</div>

<!-- Scripts -->
@stack('scripts')

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
