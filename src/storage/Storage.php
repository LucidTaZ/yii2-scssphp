<?php

namespace lucidtaz\yii2scssphp\storage;

interface Storage
{
    public function exists(string $filename): bool;
    public function get(string $filename): string;
    public function put(string $filename, string $contents): bool;
    public function remove(string $filename): bool;
    public function touch(string $filename, int $mtime): bool;
    public function getMtime(string $filename): int;
}
