<?php

namespace lucidtaz\yii2scssphp\tests;

use lucidtaz\yii2scssphp\storage\FsStorage;
use PHPUnit_Framework_TestCase;

class FsStorageTest extends PHPUnit_Framework_TestCase
{
    private $scratchFilename;

    public function setUp()
    {
        $this->scratchFilename = tempnam(sys_get_temp_dir(), 'lucidtaz_');
    }

    public function tearDown()
    {
        if (file_exists($this->scratchFilename)) {
            unlink($this->scratchFilename);
        }
        $this->scratchFilename = null;
    }

    public function testExists()
    {
        $storage = new FsStorage;

        $this->assertTrue($storage->exists($this->scratchFilename));
        $this->assertFalse($storage->exists(sys_get_temp_dir() . '/lucidtaz_non_existing'));
    }

    public function testGet()
    {
        $storage = new FsStorage;

        file_put_contents($this->scratchFilename, 'contents');
        $this->assertEquals('contents', $storage->get($this->scratchFilename));
    }

    public function testPut()
    {
        $storage = new FsStorage;

        $success = $storage->put($this->scratchFilename, 'contents');
        $this->assertTrue($success);
        $this->assertFileExists($this->scratchFilename);

        $contents = file_get_contents($this->scratchFilename);
        $this->assertEquals('contents', $contents);
    }

    public function testTouch()
    {
        $storage = new FsStorage;

        $mtime = time();
        $success = $storage->touch($this->scratchFilename, $mtime);

        $this->assertTrue($success);
        $this->assertEquals($mtime, filemtime($this->scratchFilename));
    }

    public function testGetMtime()
    {
        $storage = new FsStorage;

        $mtime = '123';
        $success = touch($this->scratchFilename, $mtime);
        $this->assertTrue($success, 'Test precondition');

        $actual = $storage->getMtime($this->scratchFilename);

        $this->assertEquals($mtime, $actual);
    }

    public function testGetMtimeException()
    {
        $storage = new FsStorage;

        $this->setExpectedExceptionRegExp(\RuntimeException::class);
        $storage->getMtime(sys_get_temp_dir() . '/lucidtaz_non_existing');
    }
}
