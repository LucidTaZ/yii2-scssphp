<?php

namespace lucidtaz\yii2scssphp\tests;

use lucidtaz\yii2scssphp\storage\FsStorage;
use PHPUnit_Framework_TestCase;

class FsStorageTest extends PHPUnit_Framework_TestCase
{
    public function testExists()
    {
        $storage = new FsStorage;
        $filename = tempnam(sys_get_temp_dir(), 'lucidtaz_');
        $this->assertTrue($storage->exists($filename));
        unlink($filename);
    }

    public function testGet()
    {
        $storage = new FsStorage;
        $filename = tempnam(sys_get_temp_dir(), 'lucidtaz_');
        file_put_contents($filename, 'contents');
        $this->assertEquals('contents', $storage->get($filename));
        unlink($filename);
    }

    public function testPut()
    {
        $storage = new FsStorage;
        $filename = tempnam(sys_get_temp_dir(), 'lucidtaz_');
        $success = $storage->put($filename, 'contents');
        $this->assertTrue($success);
        $contents = file_get_contents($filename);
        $this->assertEquals('contents', $contents);
        unlink($filename);
    }
}
