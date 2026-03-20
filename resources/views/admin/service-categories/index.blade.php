@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Категории услуг</h3>
            <a href="{{ route('admin.service-categories.create') }}" class="btn btn-primary">Создать</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Сортировка</th>
                        <th>Ключевые слова</th>
                        <th>Мастеров</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $parent)
                        <tr>
                            <td>{{ $parent->id }}</td>
                            <td><strong>{{ $parent->name }}</strong></td>
                            <td>{{ $parent->sort }}</td>
                            <td>
                                @if(!empty($parent->keywords))
                                    <span class="text-muted" title="{{ implode(', ', $parent->keywords) }}">
                                        {{ count($parent->keywords) }} шт.
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.masters.index', ['category_id' => $parent->id]) }}">{{ $parent->masters_count }}</a>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.service-categories.create', ['parent_id' => $parent->id]) }}" class="btn btn-sm btn-outline-secondary" title="Добавить подкатегорию">+</a>
                                <a href="{{ route('admin.service-categories.edit', $parent) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-edit"></i> Редактировать
                                </a>
                                <form action="{{ route('admin.service-categories.destroy', $parent) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить категорию?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @foreach($parent->children as $child)
                            <tr>
                                <td>{{ $child->id }}</td>
                                <td class="ps-4">↳ {{ $child->name }}</td>
                                <td>{{ $child->sort }}</td>
                                <td>
                                    @if(!empty($child->keywords))
                                        <span class="text-muted" title="{{ implode(', ', $child->keywords) }}">
                                            {{ count($child->keywords) }} шт.
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.masters.index', ['category_id' => $child->id]) }}">{{ $child->masters_count }}</a>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.service-categories.edit', $child) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-edit"></i> Редактировать
                                    </a>
                                    <form action="{{ route('admin.service-categories.destroy', $child) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить категорию?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
