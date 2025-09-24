@extends('admin.layouts.app')


@section('content')
    <div class="row mb-3">
        <div class="col">
            <h1>Ячейки для хранения</h1>
            <hr>
            <table id="appointmentsList" class="table table-bordered mb-5 tr-td-bg">
                @foreach($storageCells as $storageCell)
                    <tr style="background: {{ $storageCell->bookings->whereNull('finished_at')->count() == 0 ? '#91c791' : '' }} !important;">
                        <td>{{ $storageCell->number }}</td>
                        <td>
                            <table class="table table-bordered">
                                @foreach($storageCell->bookings->whereNull('finished_at') as $storageBooking)
                                    <tr>
                                        <td style="text-align: center; width: 10px; background: {{ $storageBooking->daysLeft() > 0 ? 'green' : 'red' }} ">

                                        </td>
                                        <td style="width: 200px;">
                                            @if($storageBooking->user->master)
                                                <a href="{{ route('admin.masters.show', $storageBooking->user->master->id) }}">{{ $storageBooking->user->master->full_name }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            c {{ $storageBooking->start_at->format('d.m.Y') }}
                                            до {{ $storageBooking->start_at->addDays($storageBooking->duration)->subDay()->format('d.m.Y') }}
                                        </td>
                                        <td>
                                            {{ $storageBooking->daysLeft() }}
                                        </td>
                                        <td>
                                            {{ $storageCell->cost_per_month }}
                                        </td>
                                        <td style="width: 1%;">
                                            <a class="btn btn-sm btn-primary" href="{{ route('admin.storage-bookings.edit', $storageBooking) }}">
                                                продление
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                        <td style="width: 100px;"><input style="width: 100%;" value="{{ $storageCell->secret }}" disabled></td>
                        <td style="color: #ccc; width: 200px;">{{ $storageCell->description }}</td>
                        <td style="width: 1%;">
                            <a href="{{ route('admin.storage-cells.edit', $storageCell) }}"><i class="fa fa-gear"></i></a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <h3>Занять ячейку</h3>
            <form id="storageBookingForm" action="{{ route('admin.storage-bookings.store') }}" method="post" autocomplete="off">
                @csrf
                @method('post')
                <input type="hidden" name="model_class" value="{{ \App\Models\StorageCell::class }}">
                <input type="hidden" name="duration" value="0">
                <div class="form-group mb-2">
                    <label for="modelId">Ячейка</label>
                    <select id="modelId" class="form-control" name="model_id" required>
                        <option value=""></option>
                        @foreach($storageCells as $storageCell)
                            <option value="{{ $storageCell->id }}">{{ $storageCell->number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label for="userId">Пользователь</label>
                    <select id="userId" class="form-control" name="user_id" required>
                        <option value=""></option>
                        @foreach(\App\Models\User::with('master.person')->role('master')->get()->sortBy('master.person.full_name') as $selectUser)
                            <option value="{{ $selectUser->id }}">{{ $selectUser->master->person->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-2">
                    <label for="storageBookingStartAt">Дата начала</label>
                    <input id="storageBookingStartAt" class="form-control" type="date" name="start_at" required>
                </div>
                <div class="form-group mb-2">
                    <button class="btn btn-primary" type="submit">Занять</button>
                </div>
            </form>
        </div>
    </div>
@endsection

