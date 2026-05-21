<?php

namespace App\Console\Commands;

use App\Services\Deploy\FtpDeployEnvLoad;
use App\Services\FtpDeployService;
use Illuminate\Console\Command;
use Throwable;

class FtpDeployCommand extends Command
{
    protected $signature = 'deploy
                            {--file=* : File path relative to project root (repeatable)}';

    protected $description = 'Upload files to remote hosting via FTP';

    public function handle(): int
    {
        $files = $this->option('file');

        if ($files === null || $files === [] || (count($files) === 1 && $files[0] === null)) {
            $this->error('Specify at least one file: php artisan deploy --file=.env --file=app/helpers.php');

            return self::FAILURE;
        }

        try {
            $config = FtpDeployEnvLoad::ftpConfigFromLocalEnv();
            $deployer = new FtpDeployService(base_path(), $config);

            $this->info("Connecting to {$config['host']}:{$config['port']}…");
            $deployer->connect();

            foreach ($files as $file) {
                $deployer->upload($file);
                $this->line("  <info>✓</info> {$file}");
            }

            $deployer->disconnect();
            $this->newLine();
            $this->info('Done: uploaded '.count($files).' file(s).');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
