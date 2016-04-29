<?php

namespace lucidtaz\yii2scssphp;

use Leafo\ScssPhp\Compiler;
use lucidtaz\yii2scssphp\storage\FsStorage;
use lucidtaz\yii2scssphp\storage\Storage;
use Yii;
use yii\base\Component;
use yii\web\AssetConverterInterface;

class ScssAssetConverter extends Component implements AssetConverterInterface
{
    /**
     * @var Storage
     */
    public $storage;

    private $compiler;

    public function init()
    {
        parent::init();
        if (!isset($this->storage)) {
            $this->storage = new FsStorage;
        }
        $this->compiler = new Compiler;
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

        if (!$this->storage->exists($inFile)) {
            Yii::error("Input file $inFile not found.", __METHOD__);
            return $asset;
        }

        $css = $this->compiler->compile($this->storage->get($inFile), $inFile);
        $this->storage->put($outFile, $css);

        return $cssAsset;
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
}
