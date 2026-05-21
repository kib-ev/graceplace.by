<?php

namespace App\Services\Deploy;

use Dotenv\Dotenv;
use RuntimeException;

final class FtpDeployEnvLoad
{
    /**
     * @return array{
     *     host: string,
     *     username: string,
     *     password: string,
     *     port: int,
     *     root: string,
     *     ssl: bool,
     *     passive: bool,
     * }
     */
    public static function ftpConfigFromLocalEnv(?string $basePath = null): array
    {
        $basePath = $basePath ?? base_path();
        $envFile = $basePath.DIRECTORY_SEPARATOR.'.env.local';

        if (! is_readable($envFile)) {
            throw new RuntimeException('.env.local file not found or not readable.');
        }

        $vars = self::parseEnvFile($envFile);

        $host = self::value($vars, 'DEPLOY_FTP_HOST');
        $username = self::value($vars, 'DEPLOY_FTP_USERNAME');
        $password = self::value($vars, 'DEPLOY_FTP_PASSWORD');

        if ($host === '' || $username === '' || $password === '') {
            throw new RuntimeException(
                'Set DEPLOY_FTP_HOST, DEPLOY_FTP_USERNAME, and DEPLOY_FTP_PASSWORD in .env.local.'
            );
        }

        $root = self::value($vars, 'DEPLOY_FTP_ROOT', '/');
        $root = '/'.trim(str_replace('\\', '/', $root), '/');
        if ($root === '/') {
            $root = '';
        }

        return [
            'host' => $host,
            'username' => $username,
            'password' => $password,
            'port' => (int) (self::value($vars, 'DEPLOY_FTP_PORT', '21') ?: 21),
            'root' => $root,
            'ssl' => filter_var(self::value($vars, 'DEPLOY_FTP_SSL', 'false'), FILTER_VALIDATE_BOOLEAN),
            'passive' => filter_var(self::value($vars, 'DEPLOY_FTP_PASSIVE', 'true'), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private static function parseEnvFile(string $path): array
    {
        return Dotenv::parse(file_get_contents($path));
    }

    /**
     * @param  array<string, string|null>  $vars
     */
    private static function value(array $vars, string $key, string $default = ''): string
    {
        if (! isset($vars[$key]) || $vars[$key] === null || $vars[$key] === '') {
            return $default;
        }

        return trim((string) $vars[$key], " \t\n\r\0\x0B\"'");
    }
}
