<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class MigrationController extends Controller
{
    public function index()
    {
        $allMigrations = collect(File::glob(database_path('migrations/*.php')))
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values();

        $executedMigrations = DB::table('migrations')
            ->orderBy('batch')
            ->orderBy('migration')
            ->pluck('migration');

        $executedSet = $executedMigrations->flip();

        $pendingMigrations = $allMigrations
            ->filter(fn (string $migration) => !$executedSet->has($migration))
            ->values();

        return view('admin.migrations.index', [
            'pendingMigrations' => $pendingMigrations,
            'executedMigrations' => $executedMigrations->values(),
            'migrateOutput' => session('migrate_output'),
            'migrateExitCode' => session('migrate_exit_code'),
        ]);
    }

    public function migrate()
    {
        $exitCode = Artisan::call('migrate', ['--force' => true]);

        return redirect()
            ->route('admin.migrations.index')
            ->with('migrate_output', trim(Artisan::output()))
            ->with('migrate_exit_code', $exitCode);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'migration' => ['required', 'string', 'max:255'],
        ]);

        $migration = basename($data['migration'], '.php');

        if (! preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_[a-z0-9_]+$/', $migration)) {
            throw ValidationException::withMessages([
                'migration' => 'Некорректное имя миграции.',
            ]);
        }

        if (DB::table('migrations')->where('migration', $migration)->exists()) {
            return redirect()
                ->route('admin.migrations.index')
                ->withErrors(['migration' => 'Нельзя удалить выполненную миграцию.']);
        }

        $path = database_path('migrations/'.$migration.'.php');

        if (! File::isFile($path)) {
            return redirect()
                ->route('admin.migrations.index')
                ->withErrors(['migration' => 'Файл миграции не найден.']);
        }

        File::delete($path);

        return redirect()
            ->route('admin.migrations.index')
            ->with('success', "Файл миграции {$migration} удалён.");
    }
}
