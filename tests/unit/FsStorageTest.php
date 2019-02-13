<?php

namespace lucidtaz\yii2scssphp\tests\unit;

use lucidtaz\yii2scssphp\storage\FsStorage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FsStorageTest extends TestCase
{
    private $scratchFilename;

    public function setUp(): void
    {
        $this->scratchFilename = tempnam(sys_get_temp_dir(), 'lucidtaz_');
    }

    public function tearDown(): void
    {
        if (file_exists($this->scratchFilename)) {
            unlink($this->scratchFilename);
        }
        $this->scratchFilename = null;
    }

    public function testExists(): void
    {
        $storage = new FsStorage;

        $this->assertTrue($storage->exists($this->scratchFilename));
        $this->assertFalse($storage->exists(sys_get_temp_dir() . '/lucidtaz_non_existing'));
    }

    public function testGet(): void
    {
        $storage = new FsStorage;

        file_put_contents($this->scratchFilename, 'contents');
        $this->assertEquals('contents', $storage->get($this->scratchFilename));
    }

    public function testPut(): void
    {
        $storage = new FsStorage;

        $success = $storage->put($this->scratchFilename, 'contents');
        $this->assertTrue($success);
        $this->assertFileExists($this->scratchFilename);

        $contents = file_get_contents($this->scratchFilename);
        $this->assertEquals('contents', $contents);
    }

    public function testRemove(): void
    {
        $storage = new FsStorage;

        $storage->put($this->scratchFilename, 'contents');
        $this->assertFileExists($this->scratchFilename, 'Test preparation');

        $success = $storage->remove($this->scratchFilename);
        $this->assertTrue($success);

        $this->assertFileNotExists($this->scratchFilename);

        $secondCallSuccess = $storage->remove($this->scratchFilename);
        $this->assertFalse($secondCallSuccess);
    }

    public function testTouch(): void
    {
        $storage = new FsStorage;

        $mtime = time();
        $success = $storage->touch($this->scratchFilename, $mtime);

        $this->assertTrue($success);
        $this->assertEquals($mtime, filemtime($this->scratchFilename));
    }

    public function testGetMtime(): void
    {
        $storage = new FsStorage;

        $mtime = 123;
        $success = touch($this->scratchFilename, $mtime);
        $this->assertTrue($success, 'Test precondition');

        $actual = $storage->getMtime($this->scratchFilename);

        $this->assertEquals($mtime, $actual);
    }

    public function testGetMtimeException(): void
    {
        $storage = new FsStorage;

        $this->expectException(RuntimeException::class);
        $storage->getMtime(sys_get_temp_dir() . '/lucidtaz_non_existing');
    }
}
