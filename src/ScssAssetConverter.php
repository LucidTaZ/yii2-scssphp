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
    const FORMAT_COMPACT       = \Leafo\ScssPhp\Formatter\Compact::class;
    const FORMAT_COMPRESSED    = \Leafo\ScssPhp\Formatter\Compressed::class;
    const FORMAT_CRUNCHED      = \Leafo\ScssPhp\Formatter\Crunched::class;
    const FORMAT_DEBUG         = \Leafo\ScssPhp\Formatter\Debug::class;
    const FORMAT_EXPANDED      = \Leafo\ScssPhp\Formatter\Expanded::class;
    const FORMAT_NESTED        = \Leafo\ScssPhp\Formatter\Nested::class;

    /**
     * Source maps constant from Leafo\ScssPhp\Compiler
     */
    const SOURCE_MAP_NONE   = Compiler::SOURCE_MAP_NONE;
    const SOURCE_MAP_INLINE = Compiler::SOURCE_MAP_INLINE;
    const SOURCE_MAP_FILE   = Compiler::SOURCE_MAP_FILE;

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
    public $formatter = \Leafo\ScssPhp\Formatter\Nested::class;

    /**
     * @var integer Enable/disable source maps
     * self::SOURCE_MAP_NONE    - disable source maps generator
     * self::SOURCE_MAP_INLINE  - source maps generate inside complied css file
     * self::SOURCE_MAP_FILE    - generate source maps file
     * 
     * e.g:
     * new ScssAssetConverter([
     *      'sourceMap' => ScssAssetConverter::SOURCE_MAP_INLINE,
     *      'sourceMapOptions' => [
     *          'sourceMapBasepath' => '\',
     *          'sourceRoot' => '\',
     *      ],
     * ]);
     */
    public $sourceMap = self::SOURCE_MAP_NONE;
    /**
     * @var array Source maps options
     */
    public $sourceMapOptions = [];
    
    /**
     * @var Compiler SCSSPHP Compiler object which does the actual work
     */
    private $compiler;

    public function init()
    {
        parent::init();
        if (!isset($this->storage)) {
            $this->storage = new FsStorage;
        }
        
        /** @var Compiler $compiler */
        $compiler = Yii::createObject(Compiler::class);
        $compiler->setFormatter((string) $this->formatter);

        $compiler->setSourceMap($this->sourceMap);
        $compiler->setSourceMapOptions($this->sourceMapOptions);

        $this->compiler = $compiler;
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
        $cssAsset = $this->getCssAsset($asset, 'css');

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

    private function getExtension(string $filename): string
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Get the relative path and filename of the asset
     * @param string $filename e.g. path/asset.css
     * @param string $newExtension e.g. scss
     * @return string e.g. path/asset.scss
     */
    protected function getCssAsset(string $filename, string $newExtension): string
    {
        $extensionlessFilename = pathinfo($filename, PATHINFO_FILENAME);
        $filenamePosition = strrpos($filename, $extensionlessFilename);
        $relativePath = substr($filename, 0, $filenamePosition);
        return "$relativePath$extensionlessFilename.$newExtension";
    }

    private function convertAndSaveIfNeeded(string $inFile, string $outFile)
    {
        if ($this->shouldConvert($inFile, $outFile)) {
            $css = $this->compiler->compile($this->storage->get($inFile), $inFile);
            $this->storage->put($outFile, $css);
        }
    }

    private function shouldConvert(string $inFile, string $outFile): bool
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

    private function isOlder(string $fileA, string $fileB): bool
    {
        return $this->storage->getMtime($fileA) < $this->storage->getMtime($fileB);
    }
}
