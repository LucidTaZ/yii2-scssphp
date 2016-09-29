<?php

namespace lucidtaz\yii2scssphp\tests;

use Leafo\ScssPhp\Compiler;
use lucidtaz\yii2scssphp\ScssAssetConverter;
use lucidtaz\yii2scssphp\storage\FsStorage;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;
use Yii;

class ScssAssetConverterTest extends PHPUnit_Framework_TestCase
{
    private $storage;

    public function setUp()
    {
        $this->storage = new MemoryStorage;

        $this->storage->put('base/path/other.css', '');
        $this->storage->put('base/path/asset.scss', "#blop { color: black; }");
        $this->storage->put('base/path/already_converted.scss', "#blop {\n  color: black; }\n");
        $this->storage->put('base/path/already_converted.css', "#blop { color: black; }");
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
        $assetConverter->convert('asset.scss', 'base/path');
        $generatedCss = $this->storage->get('base/path/asset.css');
        $this->assertEquals("#blop {\n  color: black; }\n", $generatedCss);
    }

    public function testConvertSkipsUpToDateResults()
    {
        // This test could also be written by inspecting before and after file contents, to see the file was not overwritten
        $compiler = $this->prophesize(Compiler::class);
        $compiler->compile()
            ->shouldNotBeCalled();
        // NOTE: We should even test that the inFile contents are never read from storage

        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 6); // Newer

        try {
            Yii::$container->set(Compiler::class, $compiler->reveal());

            $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
            $result = $assetConverter->convert('already_converted.scss', 'base/path');
            $this->assertEquals('already_converted.css', $result);
        } finally {
            Yii::$container->clear(Compiler::class);
        }
    }

    public function testConvertRespectsForceConvert()
    {
        // This test could also be written by inspecting before and after file contents, to see the file was overwritten
        $compiler = $this->prophesize(Compiler::class);
        $compiler->compile(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('dummy result');

        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 4); // Older

        try {
            Yii::$container->set(Compiler::class, $compiler->reveal());

            $assetConverter = new ScssAssetConverter(['storage' => $this->storage, 'forceConvert' => true]);
            $result = $assetConverter->convert('already_converted.scss', 'base/path');
            $this->assertEquals('already_converted.css', $result);
            $generatedResult = $this->storage->get('base/path/already_converted.css');
            $this->assertEquals('dummy result', $generatedResult);
        } finally {
            Yii::$container->clear(Compiler::class);
        }
    }

    public function testConvertWorksOnOutdatedResults()
    {
        // This test could also be written by inspecting before and after file contents, to see the file was overwritten
        $compiler = $this->prophesize(Compiler::class);
        $compiler->compile(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('dummy result');

        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 4); // Older

        try {
            Yii::$container->set(Compiler::class, $compiler->reveal());

            $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
            $result = $assetConverter->convert('already_converted.scss', 'base/path');
            $this->assertEquals('already_converted.css', $result);
            $generatedResult = $this->storage->get('base/path/already_converted.css');
            $this->assertEquals('dummy result', $generatedResult);
        } finally {
            Yii::$container->clear(Compiler::class);
        }
    }

    public function testConvertWorksOnUnknownAgeResults()
    {
        // This test could also be written by inspecting before and after file contents, to see the file was overwritten
        // We will simulate a filesystem corruption upon checking the age of the file
        $compiler = $this->prophesize(Compiler::class);
        $compiler->compile(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('dummy result');

        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 4); // Older

        $corruptStorage = new CorruptStorageDecorator($this->storage);
        $corruptStorage->corruptGetMtime = true;

        try {
            Yii::$container->set(Compiler::class, $compiler->reveal());

            $assetConverter = new ScssAssetConverter(['storage' => $corruptStorage]);
            $result = $assetConverter->convert('already_converted.scss', 'base/path');
            $this->assertEquals('already_converted.css', $result);
            $generatedResult = $this->storage->get('base/path/already_converted.css');
            $this->assertEquals('dummy result', $generatedResult);
        } finally {
            Yii::$container->clear(Compiler::class);
        }
    }
}
