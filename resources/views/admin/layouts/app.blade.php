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

         .comments__list {
             margin-top: 5px;
         }
        .comments__list .comment__item {
            background: lightgoldenrodyellow;
            padding: 5px 5px 1px;
            border: 1px solid #e1da8e;
            border-radius: 5px;
            margin-bottom: 2px;
        }

        .comments__list .comment__date {
            font-size: 10px;
            color: #333;
        }
        .comments__list .comment__author {
            font-size: 10px;
            color: #333;
        }
        .comments__list .comment__delete button[type=submit]{
            font-size: 10px;
            border: none;
            background-color: inherit;
        }


    </style>

    @yield('style')

    <script src="{{ asset('/js/jquery-3.7.1.min.js') }}"></script>

    <script src="{{ asset('/build/assets/app-BkDPDVeP.js') }}"></script>

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
    @yield('content')
</div>

<footer>
    <div class="container">
        <div class="row">
            <div style="margin-bottom: 200px;"></div>
        </div>
    </div>
</footer>

@yield('scripts')

</body>
</html>
