<?php

namespace lucidtaz\yii2scssphp\tests;

use lucidtaz\yii2scssphp\storage\Storage;
use RuntimeException;

class CorruptStorageDecorator implements Storage
{
    private $storage;

    /**
     * @var bool Set to true to throw a RuntimeException on every call
     */
    public $corruptExists = false;

    /**
     * @var bool Set to true to throw a RuntimeException on every call
     */
    public $corruptGet = false;

    /**
     * @var bool Set to true to throw a RuntimeException on every call
     */
    public $corruptPut = false;

    /**
     * @var bool Set to true to throw a RuntimeException on every call
     */
    public $corruptRemove = false;

    /**
     * @var bool Set to true to throw a RuntimeException on every call
     */
    public $corruptTouch = false;

    /**
     * @var bool Set to true to throw a RuntimeException on every call
     */
    public $corruptGetMtime = false;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    private function doThrow()
    {
        throw new RuntimeException('Intentional exception to simulate corruption');
    }

    public function exists(string $filename): bool
    {
        if ($this->corruptExists) {
            $this->doThrow();
        }
        return $this->storage->exists($filename);
    }

    public function get(string $filename): string
    {
        if ($this->corruptGet) {
            $this->doThrow();
        }
        return $this->storage->get($filename);
    }

    public function put(string $filename, string $contents): bool
    {
        if ($this->corruptPut) {
            $this->doThrow();
        }
        return $this->storage->put($filename, $contents);
    }

    public function remove(string $filename): bool
    {
        if ($this->corruptRemove) {
            $this->doThrow();
        }
        return $this->storage->remove($filename);
    }

    public function touch(string $filename, int $mtime): bool
    {
        if ($this->corruptTouch) {
            $this->doThrow();
        }
        return $this->storage->touch($filename, $mtime);
    }

    public function getMtime(string $filename): int
    {
        if ($this->corruptGetMtime) {
            $this->doThrow();
        }
        return $this->storage->getMtime($filename);
    }
}
