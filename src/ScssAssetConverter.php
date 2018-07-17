<?php

namespace lucidtaz\yii2scssphp;

use Leafo\ScssPhp\Compiler;
use lucidtaz\yii2scssphp\storage\FsStorage;
use lucidtaz\yii2scssphp\storage\Storage;
use RuntimeException;
use Yii;
use yii\base\Component;
use yii\web\AssetConverterInterface;

class ScssAssetConverter extends Component implements AssetConverterInterface
{
    /**
     * Formatter types for format of outputs CSS as a string.
     */
    const FORMAT_COMPACT       = '\Leafo\ScssPhp\Formatter\Compact';
    const FORMAT_COMPRESSED    = '\Leafo\ScssPhp\Formatter\Compressed';
    const FORMAT_CRUNCHED      = '\Leafo\ScssPhp\Formatter\Crunched';
    const FORMAT_DEBUG         = '\Leafo\ScssPhp\Formatter\Debug';
    const FORMAT_EXPANDED      = '\Leafo\ScssPhp\Formatter\Expanded';
    const FORMAT_NESTED        = '\Leafo\ScssPhp\Formatter\Nested';

    /**
     * @var Storage
     */
    public $storage;

    /**
     * @var boolean whether the source asset file should be converted even if
     * its result already exists. You may want to set this to be `true` during
     * the development stage to make sure the converted assets are always up-to-
     * date. Do not set this to true on production servers as it will
     * significantly degrade the performance.
     */
    public $forceConvert = false;
    
    /**
     * @var string|\Leafo\ScssPhp\Formatter
     */
    public $formatter = '\Leafo\ScssPhp\Formatter\Nested';
    
    protected $compiler;

    public function init()
    {
        parent::init();
        if (!isset($this->storage)) {
            $this->storage = new FsStorage;
        }
        $this->compiler = Yii::createObject(Compiler::class);
        
        $this->compiler->setFormatter($this->formatter);
    }

    /**
     * Converts a given SCSS asset file into a CSS file.
     * @param string $asset the asset file path, relative to $basePath
     * @param string $basePath the directory the $asset is relative to.
     * @return string the converted asset file path, relative to $basePath.
     */
    public function convert($asset, $basePath)
    {
        $extension = $this->getExtension($asset);
        if ($extension !== 'scss') {
            return $asset;
        }
        $cssAsset = $this->replaceExtension($asset, 'css');

        $inFile = "$basePath/$asset";
        $outFile = "$basePath/$cssAsset";
        
        $this->compiler->setImportPaths(dirname($inFile));

        if (!$this->storage->exists($inFile)) {
            Yii::error("Input file $inFile not found.", __METHOD__);
            return $asset;
        }

        $this->convertAndSaveIfNeeded($inFile, $outFile);

        return $cssAsset;
    }

    protected function getExtension(string $filename): string
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    protected function replaceExtension(string $filename, string $newExtension): string
    {
        $extensionlessFilename = pathinfo($filename, PATHINFO_FILENAME);
        return "$extensionlessFilename.$newExtension";
    }

    protected function convertAndSaveIfNeeded(string $inFile, string $outFile)
    {
        if ($this->shouldConvert($inFile, $outFile)) {
            $css = $this->compiler->compile($this->storage->get($inFile), $inFile);
            $this->storage->put($outFile, $css);
        }
    }

    protected function shouldConvert(string $inFile, string $outFile): bool
    {
        if (!$this->storage->exists($outFile)) {
            return true;
        }
        if ($this->forceConvert) {
            return true;
        }
        try {
            return $this->isOlder($outFile, $inFile);
        } catch (RuntimeException $e) {
            Yii::warning('Encountered RuntimeException message "' . $e->getMessage() . '", going to convert.', __METHOD__);
            return true;
        }
    }

    protected function isOlder(string $fileA, string $fileB): bool
    {
        return $this->storage->getMtime($fileA) < $this->storage->getMtime($fileB);
    }
}
