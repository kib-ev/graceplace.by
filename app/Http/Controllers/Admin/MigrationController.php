<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
}
