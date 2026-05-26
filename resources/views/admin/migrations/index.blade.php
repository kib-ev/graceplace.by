@extends('admin.layouts.app')

@section('content')
    <div class="row mt-3">
        <div class="col-12">
            <h1 class="mb-3">Миграции</h1>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if(session()->has('migrate_exit_code'))
                <div class="alert {{ session('migrate_exit_code') === 0 ? 'alert-success' : 'alert-danger' }}">
                    {{ session('migrate_exit_code') === 0 ? 'Миграции выполнены.' : 'Ошибка запуска миграций.' }}
                </div>
            @endif

            <div class="mb-3">
                <form method="POST" action="{{ route('admin.migrations.run') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Migrate</button>
                </form>
            </div>

            @if(!empty($migrateOutput))
                <div class="card mb-4">
                    <div class="card-header">Вывод migrate</div>
                    <div class="card-body">
                        <pre class="mb-0">{{ $migrateOutput }}</pre>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            Ожидающие миграции ({{ $pendingMigrations->count() }})
                        </div>
                        <div class="card-body">
                            @if($pendingMigrations->isEmpty())
                                <p class="mb-0 text-success">Нет ожидающих миграций.</p>
                            @else
                                <ul class="list-unstyled mb-0">
                                    @foreach($pendingMigrations as $migration)
                                        <li class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                            <code class="small">{{ $migration }}</code>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.migrations.destroy') }}"
                                                onsubmit="return confirm('Удалить файл миграции {{ $migration }}.php?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="migration" value="{{ $migration }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            Выполненные миграции ({{ $executedMigrations->count() }})
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            @if($executedMigrations->isEmpty())
                                <p class="mb-0">Список пуст.</p>
                            @else
                                <ul class="mb-0">
                                    @foreach($executedMigrations as $migration)
                                        <li><code>{{ $migration }}</code></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
