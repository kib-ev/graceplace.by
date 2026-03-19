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
                    @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->sort }}</td>
                            <td>
                                @if(!empty($category->keywords))
                                    <span class="text-muted" title="{{ implode(', ', $category->keywords) }}">
                                        {{ count($category->keywords) }} шт.
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $category->masters_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.service-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-edit"></i> Редактировать
                                </a>
                                <form action="{{ route('admin.service-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить категорию? Связи с мастерами будут сняты.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
