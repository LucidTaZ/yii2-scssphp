<?php

namespace lucidtaz\yii2scssphp\tests;

use lucidtaz\yii2scssphp\ScssAssetConverter;

class OverridedConverter extends ScssAssetConverter
{
    /**
     * @var string|null
     */
    public $overridedCssAssetResult;

    protected function getCssAsset(string $filename, string $newExtension): string
    {
        if ($this->overridedCssAssetResult !== null) {
            return $this->overridedCssAssetResult;
        }
        return parent::getCssAsset($filename, $newExtension);
    }
}