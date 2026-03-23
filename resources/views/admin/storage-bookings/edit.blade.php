@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col-12">
            @if(isset($storageBooking))
                <h1>Редактировать бронь</h1>
                <a href="{{ route('admin.storage-cells.show', $storageBooking->cell) }}">Просмотреть ячейку</a>
            @else
                <h1>Добавить бронь</h1>
            @endif

            <hr>

            @if($errors->any())
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            @foreach($errors->all() as $error)
                                <strong>{{ $error }}</strong><br>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            @endif


        </div>
    </div>

    <div class="row">
        <div class="col-4">
            <form action="{{ isset($storageBooking) ? route('admin.storage-bookings.update', $storageBooking) : route('admin.storage-bookings.store') }}" method="post"
                  autocomplete="off">
                @csrf
                @method(isset($storageBooking) ? 'patch' : 'post')


                <div class="form-group mb-2">
                    <label for="masterId">Master</label>
                    <select id="masterId" name="user_id" class="form-control" required disabled>
                        <option value=""></option>
                        @foreach(\App\Models\User::role('master')->get()->sortBy('name') as $user)
                            <option value="{{ $user->id }}" @selected($user->id == (isset($storageBooking) ? $storageBooking->user_id : request('user_id')))>
                                {{ $user->name }} | {{ $user->master->description }} | {{ $user->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="modelId">Ячейка</label>
                    <select id="modelId" class="form-control" name="model_id" required>
                        <option value=""></option>
                        @foreach(\App\Models\StorageCell::all() as $storageCell)
                            <option
                                value="{{ $storageCell->id }}" @selected($storageCell->id == (isset($storageBooking) ? $storageBooking->model_id : ''))>{{ $storageCell->number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="storageBookingStartAt">Дата начала</label>
                    <input id="storageBookingStartAt" class="form-control" type="date" name="start_at"
                           value="{{ (isset($storageBooking) ? $storageBooking->start_at->format('Y-m-d') : '') }}" required>
                </div>

                <div class="form-group mb-2">
                    <label for="storageBookingDuration">Продолжительность</label>
                    <input id="storageBookingDuration" class="form-control" type="number" step="1" name="duration"
                           value="{{ (isset($storageBooking) ? $storageBooking->duration : '') }}" required>
                </div>

                @if(is_null($storageBooking->finished_at))
                    <div class="form-group mb-2">
                        <button class="btn btn-primary" type="submit">Сохранить</button>
                    </div>
                @endif
            </form>


            @if(isset($storageBooking) && is_null($storageBooking->finished_at))
                <form action="{{ isset($storageBooking) ? route('admin.storage-bookings.update', $storageBooking) : route('admin.storage-bookings.store') }}" method="post"
                      autocomplete="off">
                    @csrf
                    @method(isset($storageBooking) ? 'patch' : 'post')

                    <input type="hidden" name="finished_at" value="{{ now() }}">

                    <button class="btn btn-danger" type="submit">Завершить бронь</button>

                </form>
            @endif

            @if(isset($storageBooking) && $storageBooking->finished_at)
                Бронь завершена: {{ $storageBooking->finished_at->format('d.m.Y H:i') }}
                <form action="{{ route('admin.storage-bookings.update', $storageBooking) }}" method="post" class="d-inline ms-2">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="activate" value="1">
                    <button type="submit" class="btn btn-success btn-sm">Активировать</button>
                </form>
            @endif

            @if(isset($storageBooking))
                <form action="{{ route('admin.storage-bookings.destroy', $storageBooking) }}" method="post" style="float: right;">
                    @csrf
                    @method('delete')
                    <button type="submit" disabled>Удалить</button>
                </form>
            @endif
        </div>

        @if(isset($storageBooking))
            <div class="col-4">
                Комментарии

                <div class="comments">
                    @include('admin.comments.includes.widget', ['model' => $storageBooking, 'title' => 'Комментарии', 'type' => 'admin'])
                </div>
            </div>
        @endif
    </div>

    @if(isset($storageBooking) && session('success'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        </div>
    @endif
@endsection
