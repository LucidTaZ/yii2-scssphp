<?php

namespace lucidtaz\yii2scssphp\tests;

class MemoryStorage implements \lucidtaz\yii2scssphp\storage\Storage
{
    private $data = [];
    private $mtimes = [];

    public function exists(string $filename): bool
    {
        return array_key_exists($filename, $this->data);
    }

    public function get(string $filename): string
    {
        return $this->data[$filename];
    }

    public function put(string $filename, string $contents): bool
    {
        $this->data[$filename] = $contents;
        $this->touch($filename, time());
        return true;
    }

    public function remove(string $filename): bool
    {
        if (!$this->exists($filename)) {
            return false;
        }
        unset($this->data[$filename]);
        return true;
    }

    public function touch(string $filename, int $mtime): bool
    {
        $this->mtimes[$filename] = $mtime;
        return true;
    }

    public function getMtime(string $filename): int
    {
        if (array_key_exists($filename, $this->mtimes)) {
            return $this->mtimes[$filename];
        }
        return 0;
    }
}
