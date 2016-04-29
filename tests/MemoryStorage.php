<?php

namespace lucidtaz\yii2scssphp\tests;

class MemoryStorage implements \lucidtaz\yii2scssphp\storage\Storage
{
    private $data = [];

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
        return true;
    }
}
