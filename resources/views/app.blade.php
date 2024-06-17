<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <nav>
        <ul>
            <li><a href="{{ route('public.masters.index') }}">masters</a></li>
            <li><a href="{{ route('public.places.index') }}">places</a></li>
            <li><a href="{{ route('public.appointments.index') }}">appointments</a></li>
        </ul>
    </nav>
    <div class="container">
        @yield('container')
    </div>
</body>
</html>
