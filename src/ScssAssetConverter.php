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
     * @var Compiler
     */
    private $compiler;

    /**
     * Set the destination folder where to copy the compiled css file.
     * If the value is null, then it will be generated into the sourcePath of the asset bundle.
     *
     * ```
     * 'assetManager' => [
     *     'converter' => [
     *         'class' => \lucidtaz\yii2scssphp\ScssAssetConverter::class,
     *         'distFolder' => 'css',
     *     ],
     * ],
     * ```
     *
     * @var string|null
     */
    public $distFolder;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!isset($this->storage)) {
            $this->storage = new FsStorage;
        }
        $this->compiler = Yii::createObject(Compiler::class);
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
        $outFile = $this->distFolder ? "$basePath/$this->distFolder/$cssAsset" : "$basePath/$cssAsset";
        
        $this->compiler->setImportPaths(dirname($inFile));

        if (!$this->storage->exists($inFile)) {
            Yii::error("Input file $inFile not found.", __METHOD__);
            return $asset;
        }

        $this->convertAndSaveIfNeeded($inFile, $outFile);

        return $this->distFolder ? $this->distFolder . '/' . $cssAsset : $cssAsset;
    }

    private function getExtension(string $filename): string
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    private function replaceExtension(string $filename, string $newExtension): string
    {
        $extensionlessFilename = pathinfo($filename, PATHINFO_FILENAME);
        return "$extensionlessFilename.$newExtension";
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
