<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- META OG -->
{{--    <meta property="og:title" content="Your Page Title" />--}}
{{--    <meta property="og:description" content="Your Page Description" />--}}
    <meta property="og:image" content="{{ asset('/images/logo.jpg') }}" />
{{--    <meta property="og:url" content="Link to Your Page" />--}}

    <title>GracePlace Minsk</title>


    <link href="{{ asset('/build/assets/app-D-sv12UV.css') }}" rel="stylesheet">

    <style>

        body {
            background-color: #f2f4f7;
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
        }
        .place .hour.busy {
            background: #ffdede;
        }
        .place .hour.busy .info {
            color: #e5b7b7;
            float: right;
        }
        .place .hour.free {
            background: #e1fbe1;
        }
    </style>

    <script src="{{ asset('/build/assets/app-BkDPDVeP.js') }}"></script>

</head>
<body>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">

        <a class="navbar-brand" href="{{ url('https://graceplace.by') }}"><img class="logo" src="{{ asset('/images/logo.jpg') }}" alt="GracePlace Logo"> GracePlace</a>


        @if(str_contains(url()->full(), '/admin/') || auth()->id())
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">

    {{--                <a class="nav-link active" aria-current="page" href="#">Home</a>--}}
                        <a class="nav-link" href="{{ route('admin.appointments.index') }}">Записи</a>
                        <a class="nav-link" href="{{ route('admin.masters.index') }}">Мастера</a>
                        <a class="nav-link" href="{{ route('admin.places.index') }}">Места</a>
                        <a class="nav-link" href="{{ url('/admin/stats') }}">Статистика</a>
                </div>
            </div>

        @else
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link" href="{{ route('login') }}">Вход</a>
                    {{--                <a class="nav-link active" aria-current="page" href="#">Home</a>--}}
{{--                    <a class="nav-link" href="{{ route('admin.appointments.index') }}">Записи</a>--}}
{{--                    <a class="nav-link" href="{{ route('admin.masters.index') }}">Мастера</a>--}}
{{--                    <a class="nav-link" href="{{ route('admin.places.index') }}">Места</a>--}}
{{--                    <a class="nav-link" href="{{ url('/admin/stats') }}">Статистика</a>--}}
                </div>
            </div>
        @endif

    </div>
</nav>

<div class="container">
    @yield('content')
</div>

<footer>
    <div class="container">
        <div class="row">
            <div style="margin-bottom: 200px;"></div>
        </div>
    </div>
</footer>
</body>
</html>
