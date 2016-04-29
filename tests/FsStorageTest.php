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
        $this->assertFalse($storage->exists(sys_get_temp_dir() . 'lucidtaz_non_existing'));
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
}
