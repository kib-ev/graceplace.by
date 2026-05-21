<?php

namespace App\Services;

use RuntimeException;

final class FtpDeployService
{
    /** @var resource|false */
    private $connection = false;

    private readonly array $config;

    /**
     * @param  array{
     *     host: string,
     *     username: string,
     *     password: string,
     *     port: int,
     *     root: string,
     *     ssl: bool,
     *     passive: bool,
     * }  $config
     */
    public function __construct(
        private readonly string $basePath,
        array $config,
    ) {
        if (! extension_loaded('ftp')) {
            throw new RuntimeException('PHP ext-ftp extension is not installed.');
        }

        $this->config = $config;
    }

    public function connect(): void
    {
        $host = $this->config['host'];
        $port = $this->config['port'];

        $this->connection = $this->config['ssl']
            ? @ftp_ssl_connect($host, $port, 30)
            : @ftp_connect($host, $port, 30);

        if ($this->connection === false) {
            throw new RuntimeException("Could not connect to FTP {$host}:{$port}.");
        }

        if (! @ftp_login($this->connection, $this->config['username'], $this->config['password'])) {
            throw new RuntimeException('FTP authentication failed.');
        }

        if ($this->config['passive'] && ! @ftp_pasv($this->connection, true)) {
            throw new RuntimeException('Could not enable FTP passive mode.');
        }
    }

    public function upload(string $relativePath): void
    {
        $relativePath = $this->normalizeRelativePath($relativePath);
        $localPath = $this->basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (! is_file($localPath)) {
            throw new RuntimeException("Local file not found: {$relativePath}");
        }

        $remotePath = $this->remotePath($relativePath);
        $remoteDir = dirname($remotePath);

        if ($remoteDir !== '.' && $remoteDir !== '/') {
            $this->ensureRemoteDirectory($remoteDir);
        }

        if (! @ftp_put($this->connection, $remotePath, $localPath, FTP_BINARY)) {
            throw new RuntimeException("Failed to upload file to FTP: {$remotePath}");
        }
    }

    public function disconnect(): void
    {
        if ($this->connection !== false) {
            @ftp_close($this->connection);
            $this->connection = false;
        }
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        if ($path === '' || str_contains($path, '..')) {
            throw new RuntimeException("Invalid path: {$path}");
        }

        return ltrim($path, '/');
    }

    private function remotePath(string $relativePath): string
    {
        $root = $this->config['root'];

        return ($root !== '' ? $root.'/' : '/').$relativePath;
    }

    private function ensureRemoteDirectory(string $remoteDir): void
    {
        $segments = array_filter(explode('/', trim(str_replace('\\', '/', $remoteDir), '/')));
        $path = '';

        foreach ($segments as $segment) {
            $path .= '/'.$segment;
            @ftp_mkdir($this->connection, $path);
        }
    }
}
