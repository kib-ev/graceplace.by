@extends('app')


@section('content')
    <div class="row mb-3">
        <div class="col">
            <h1>Ячейки для хранения</h1>

            {{--            <hr>--}}
            {{--            <a href="{{ route('admin.storage-cells.create', request()->all()) }}" class="btn btn-primary me-3">Создать</a>--}}

            <!-- ----------------------------------------->

            <hr>

            <table id="appointmentsList" class="table table-bordered mb-5">
                @foreach($storageCells as $storageCell)
                    <tr>
                        <td>{{ $storageCell->number }}</td>

                        <td>
                            <table class="table table-bordered">
                                @foreach($storageCell->bookings as $storageBooking)
                                    <tr>
                                        <td style="width: 10px; background: {{ now()->isBetween($storageBooking->start_at, $storageBooking->start_at->addDays($storageBooking->duration)->subDay()) ? 'green' : 'red' }} ">
                                            {{ $storageBooking->user_id }}
                                        </td>
                                        <td style="width: 200px;">
                                            {{ $storageBooking->master?->full_name }}
                                        </td>

                                        <td>
                                            c {{ $storageBooking->start_at->format('d.m.Y') }}
                                            до {{ $storageBooking->start_at->addDays($storageBooking->duration)->subDay()->format('d.m.Y') }}
                                        </td>

                                        <td>
                                            {{ $storageBooking->daysLeft() }}
                                        </td>

                                        <td>
                                            <form id="rentCreate" action="{{ route('admin.storage-bookings.update', $storageBooking) }}" method="post" autocomplete="off">
                                                @csrf
                                                @method('patch')


                                                <input type="hidden" name="duration" value="{{ $storageBooking->duration + 30 }}">

                                                <input type="submit" value="Продлить на 30 дней">

                                            </form>
                                        </td>

                                        <td>
                                            <a href="{{ route('admin.storage-bookings.edit', $storageBooking) }}"><i class="fa fa-edit"></i></a>
                                        </td>

                                    </tr>
                                @endforeach
                            </table>
                        </td>

                        <td style="color: #ccc; width: 200px;">{{ $storageCell->description }}</td>


                        <td style="width: 1%;">
                            <a href="{{ route('admin.storage-cells.edit', $storageCell) }}"><i class="fa fa-edit"></i></a>
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
                    <label for="masterId">Мастер</label>
                    <select id="masterId" class="form-control" name="master_id" required>
                        <option value=""></option>
                        @foreach(\App\Models\Master::all()->sortBy('full_name') as $master)
                            <option value="{{ $master->id }}">{{ $master->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="storageBookingStartAt">Дата начала</label>
                    <input id="storageBookingStartAt" class="form-control" type="date" name="start_at" required>
                </div>

                <div class="form-group mb-2">
                    <label for="storageBookingDuration">Количество дней</label>
                    <select id="storageBookingDuration" class="form-control" name="duration" required>
{{--                        <option value=""></option>--}}
                        <option value="30">30</option>
                    </select>
                </div>

                <div class="form-group mb-2">
                    <button class="btn btn-primary" type="submit">Занять</button>
                </div>

            </form>

        </div>

    </div>
@endsection

