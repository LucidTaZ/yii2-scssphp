<?php

namespace lucidtaz\yii2scssphp\tests;

use lucidtaz\yii2scssphp\ScssAssetConverter;
use lucidtaz\yii2scssphp\storage\FsStorage;
use PHPUnit_Framework_TestCase;

class ScssAssetConverterTest extends PHPUnit_Framework_TestCase
{
    private $storage;

    public function setUp()
    {
        $this->storage = new MemoryStorage;
        $this->storage->put('base/path/other.css', '');
        $this->storage->put('base/path/asset.scss', "#blop { color: black; }");
    }

    public function tearDown()
    {
        unset($this->storage);
    }

    public function testInitUsesFilesystem()
    {
        $assetConverter = new ScssAssetConverter;
        $this->assertInstanceOf(FsStorage::class, $assetConverter->storage);
    }

    public function testConvertGivesResult()
    {
        $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
        $result = $assetConverter->convert('asset', 'base/path');
        $this->assertNotEmpty($result);
    }

    public function testConvertLeavesCssAlone()
    {
        $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
        $result = $assetConverter->convert('other.css', 'base/path');
        $this->assertEquals('other.css', $result);
    }

    public function testConvertLeavesNonExistingFileAlone()
    {
        $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
        $result = $assetConverter->convert('nonexisting.scss', 'base/path');
        $this->assertEquals('nonexisting.scss', $result);
    }

    public function testConvertHandlesScss()
    {
        $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
        $result = $assetConverter->convert('asset.scss', 'base/path');
        $this->assertEquals('asset.css', $result);
    }

    public function testConvertActuallyWorks()
    {
        $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
        $result = $assetConverter->convert('asset.scss', 'base/path');
        $generatedCss = $this->storage->get('base/path/asset.css');
        $this->assertEquals("#blop {\n  color: black; }\n", $generatedCss);
    }
}
