@extends('admin.layouts.app')


@section('content')
    <div class="row">
        <div class="col-6 offset-3">
            @if(isset($compartment))
                <h1>Редактировать ячейку</h1>
            @else
                <h1>Добавить ячейку</h1>
            @endif

            <hr>

{{--            @if($errors->any())--}}
{{--                <div class="row">--}}
{{--                    <div class="col-12">--}}
{{--                        <div class="alert alert-warning alert-dismissible fade show" role="alert">--}}
{{--                            @foreach($errors->all() as $error)--}}
{{--                                <strong>{{ $error }}</strong><br>--}}
{{--                            @endforeach--}}
{{--                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}


            <form action="{{ isset($storageCell) ? route('admin.storage-cells.update', $storageCell) : route('admin.storage-cells.store') }}" method="post" autocomplete="off">
                @csrf
                @method(isset($storageCell) ? 'patch' : 'post')

                <div class="form-group mb-2">
                    <label for="storageCellNumber">Номер</label>
                    <input class="form-control" id="storageCellNumber" type="text" name="number" value="{{ isset($storageCell) ? $storageCell->number : '' }}" required>
                </div>

                <div class="form-group mb-2">
                    <label for="storageCellDescription">Описание</label>
                    <textarea class="form-control"  name="description"  id="storageCellDescription" cols="30" rows="10">{{ isset($storageCell) ? $storageCell->description : '' }}</textarea>
                </div>

                <div class="form-group mb-2">
                    <label for="storageCellSecret">Код</label>
                    <input class="form-control" id="storageCellSecret" type="text" maxlength="3" name="secret" value="{{ isset($storageCell) ? $storageCell->secret : '' }}">
                </div>

                <div class="form-group mb-2">
                    <label for="storageCellCost">Цена за 30 дней</label>
                    <input class="form-control" id="storageCellCost" type="number" step="0.01" name="cost_per_month" value="{{ isset($storageCell) ? $storageCell->cost_per_month : '' }}" required>
                </div>

                <hr>

                <div class="form-group">
                    @if(isset($storageCell))
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    @else
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    @endif
                </div>

            </form>


{{--            @if(isset($compartment) && is_null($compartment->price))--}}
{{--                <form action="{{ route('admin.appointments.update', $compartment) }}" method="post" style="float: right;">--}}
{{--                    @csrf--}}
{{--                    @method('patch')--}}
{{--                    <input type="hidden" name="cancel" value="1">--}}
{{--                    <button class="btn btn-danger" type="submit">Отменить</button>--}}
{{--                </form>--}}
{{--            @endif--}}


{{--            @if(isset($compartment) && is_null($compartment->price) && $compartment->canceled_at)--}}
{{--                <form action="{{ route('admin.appointments.destroy', $appointment) }}" method="post" style="float: right;">--}}
{{--                    @csrf--}}
{{--                    @method('delete')--}}
{{--                    <button type="submit">Удалить</button>--}}
{{--                </form>--}}
{{--            @endif--}}
        </div>
    </div>
@endsection
