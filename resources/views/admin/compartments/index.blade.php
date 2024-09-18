@extends('app')


@section('content')
    <div class="row mb-3">
        <div class="col">
            <h1>Ячейки для хранения</h1>

{{--            <hr>--}}
{{--            <a href="{{ route('admin.compartments.create', request()->all()) }}" class="btn btn-primary me-3">Создать</a>--}}

            <!-- ----------------------------------------->

            <hr>

            <table id="appointmentsList" class="table table-bordered mb-5">
                @foreach($compartments as $compartment)
                    <tr>
                        <td>{{ $compartment->name }}</td>

                        <td>
                            <table class="table table-bordered">
                                @foreach($compartment->rents as $rent)
                                    <tr>
                                        <td style="width: 10px; background: {{ now()->isBetween($rent->start_at, $rent->start_at->addDays($rent->duration)->subDay()) ? 'green' : 'red' }} ">
                                            {{ $rent->user_id }}
                                        </td>
                                        <td>
                                            {{ $rent->master?->full_name }}
                                        </td>

                                        <td>
                                            c {{ $rent->start_at->format('d.m.Y') }}
                                            до {{ $rent->start_at->addDays($rent->duration)->subDay()->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>

                        <td style="color: #ccc;">{{ $compartment->description }}</td>


                        <td style="width: 1%;">
                            <a href="{{ route('admin.compartments.edit', $compartment) }}"><i class="fa fa-edit"></i></a>
                        </td>
                    </tr>
                @endforeach

            </table>

        </div>
    </div>

    <div class="row">
        <div class="col-4">
            <h3>Занять ячейку</h3>

            <form id="rentCreate" action="{{ route('admin.rents.store') }}" method="post" autocomplete="off">
                @csrf
                @method('post')

                <input type="hidden" name="model_class" value="{{ \App\Models\Compartment::class }}">

                <div class="form-group mb-2">
                    <label for="modelId">Ячейка</label>
                    <select id="modelId" class="form-control" name="model_id" required>
                        <option value=""></option>
                        @foreach($compartments as $compartment)
                            <option value="{{ $compartment->id }}">{{ $compartment->name }}</option>
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
                    <label for="compartmentStartAt">Дата начала</label>
                    <input id="compartmentStartAt" class="form-control" type="date" name="start_at" required>
                </div>

                <div class="form-group mb-2">
                    <label for="compartmentDuration">Количество дней</label>
                    <select id="compartmentDuration" class="form-control" name="duration" required>
                        <option value=""></option>
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

