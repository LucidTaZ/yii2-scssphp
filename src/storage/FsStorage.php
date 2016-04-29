<?php

namespace lucidtaz\yii2scssphp\storage;

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
}
