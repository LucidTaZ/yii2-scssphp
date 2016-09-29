<?php

namespace lucidtaz\yii2scssphp\storage;

use RuntimeException;

class FsStorage implements Storage
{
    public function exists(string $filename): bool
    {
        return file_exists($filename);
    }

    public function get(string $filename): string
    {
        return file_get_contents($filename);
    }

    public function put(string $filename, string $contents): bool
    {
        return file_put_contents($filename, $contents) !== false;
    }

    public function touch(string $filename, int $mtime): bool
    {
        return touch($filename, $mtime);
    }

    public function getMtime(string $filename): int
    {
        $mtime = @filemtime($filename);
        if ($mtime === false) {
            throw new RuntimeException('Could not determine mtime for ' . $filename);
            // ... or should we adhere to PHP's int|false convention?
        }
        return $mtime;
    }
}
