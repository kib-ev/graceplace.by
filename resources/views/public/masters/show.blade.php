@extends('app')


@section('content')
    <div class="row">
        <div class="col">
            <h1>Master</h1>
            <a href="{{ route('admin.masters.index') }}">Masters</a>
            <hr>
            <img src="{{ $master->image_path }}">
        </div>
    </div>
@endsection
