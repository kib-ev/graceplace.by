<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API token and save it to .env file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Генерируем новый токен
        $token = Str::random(64);

        // Читаем текущий .env файл
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (str_contains($envContent, 'API_TOKEN=')) {
            // Обновляем существующий токен
            $envContent = preg_replace(
                '/API_TOKEN=.*/',
                'API_TOKEN=' . $token,
                $envContent
            );
        } else {
            // Добавляем новый токен
            $envContent .= "\nAPI_TOKEN=" . $token;
        }

        // Сохраняем изменения в .env файл
        file_put_contents($envPath, $envContent);

        // Очищаем кэш конфигурации
        $this->call('config:clear');

        $this->info('API Token успешно сгенерирован и сохранен в .env файл');
        $this->info('Новый токен: ' . $token);
    }
}
