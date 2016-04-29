<?php

namespace lucidtaz\yii2scssphp\storage;

interface Storage
{
    public function exists(string $filename): bool;
    public function get(string $filename): string;
    public function put(string $filename, string $contents): bool;
}
