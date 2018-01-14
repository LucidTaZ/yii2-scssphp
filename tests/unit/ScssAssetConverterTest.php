<?php

namespace lucidtaz\yii2scssphp\tests\unit;

use lucidtaz\yii2scssphp\ScssAssetConverter;
use lucidtaz\yii2scssphp\storage\FsStorage;
use PHPUnit\Framework\TestCase;

/**
 * Main test class
 *
 * The testing logic is built as follows: the system should be tested as
 * straightforwardly as possible, preferably without mocking.
 *
 * Filesystem access is virtual is most cases: we inject a "MemoryStorage" into
 * the converter, instead of the default. This means that the library works as
 * it normally would, only we inspect the MemoryStorage object to see the result
 * of the calls.
 *
 * This has a number of advantages, one of which is that the storage is always
 * in a known state before the test begins. Another one is that we don't pollute
 * the storage with generated files, so we don't have to worry about cleaning
 * up.
 *
 * In some cases however it's not possible to fully isolate the storage access,
 * for example if the underlying libraries use the filesystem directly. That's
 * why there is also a second form of testing logic: actually using .css and
 * .scss files on the filesystem. These tests should have preparation and
 * cleanup code to make sure everything stays sane.
 */
class ScssAssetConverterTest extends TestCase
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

    public function testConvertHandlesImport()
    {
        // Unfortunately we cannot currently test this using the mocked
        // filesystem, since leafo/scss directly accesses the filesystem. If we
        // want to mock it away, we should extend the compiler and override all
        // filesystem access, but it's not trivial.
        // For now we actually test directly on the filesystem, in the
        // tests/files/ directory.

        $baseDir = __DIR__ . '/files';
        $sourceFilename = 'import_base.scss';
        $targetFilename = 'import_base.css';
        $targetFile = "$baseDir/$targetFilename";

        $storage = new FsStorage();
        if ($storage->exists($targetFile)) {
            $storage->remove($targetFile);
        }

        $assetConverter = new ScssAssetConverter(['storage' => $storage]);
        $assetConverter->convert($sourceFilename, $baseDir);
        $generatedCss = $storage->get($targetFile);
        $this->assertEquals("#blop {\n  color: blue; }\n\n#bla {\n  color: red; }\n", $generatedCss);

        // Cleanup generated file
        $storage->remove($targetFile);
    }

    public function testConvertSkipsUpToDateResults()
    {
        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 6); // Newer

        $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
        $result = $assetConverter->convert('already_converted.scss', 'base/path');
        $this->assertEquals('already_converted.css', $result);

        $currentModificationTime = $this->storage->getMtime('base/path/already_converted.css');
        $this->assertEquals(6, $currentModificationTime, 'File modification time should not change');
    }

    public function testConvertRespectsForceConvert()
    {
        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 6); // Newer

        $assetConverter = new ScssAssetConverter(['storage' => $this->storage, 'forceConvert' => true]);
        $result = $assetConverter->convert('already_converted.scss', 'base/path');
        $this->assertEquals('already_converted.css', $result);

        $currentModificationTime = $this->storage->getMtime('base/path/already_converted.css');
        $this->assertGreaterThan(6, $currentModificationTime, 'The modification time has increased');
    }

    public function testConvertWorksOnOutdatedResults()
    {
        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 4); // Older

        $assetConverter = new ScssAssetConverter(['storage' => $this->storage]);
        $result = $assetConverter->convert('already_converted.scss', 'base/path');
        $this->assertEquals('already_converted.css', $result);

        $currentModificationTime = $this->storage->getMtime('base/path/already_converted.css');
        $this->assertGreaterThan(4, $currentModificationTime, 'The modification time has increased');
    }

    public function testConvertWorksOnUnknownAgeResults()
    {
        $this->storage->touch('base/path/already_converted.scss', 5);
        $this->storage->touch('base/path/already_converted.css', 4); // Older

        $corruptStorage = new CorruptStorageDecorator($this->storage);
        $corruptStorage->corruptGetMtime = true;

        $assetConverter = new ScssAssetConverter(['storage' => $corruptStorage]);
        $result = $assetConverter->convert('already_converted.scss', 'base/path');
        $this->assertEquals('already_converted.css', $result);

        $currentModificationTime = $this->storage->getMtime('base/path/already_converted.css');
        $this->assertGreaterThan(4, $currentModificationTime, 'The modification time has increased');
    }
}
