[![Build Status](https://travis-ci.org/LucidTaZ/yii2-scssphp.svg?branch=master)](https://travis-ci.org/LucidTaZ/yii2-scssphp)

Yii2 bindings for SCSS-PHP
==========================

This library provides easy integration of
[leafo/scssphp](https://github.com/leafo/scssphp) into
[Yii2](https://github.com/yiisoft/yii2). Scssphp is a native PHP SCSS (SASS)
compiler. This enables you to seamlessly use SCSS while using Yii's method of
asset publication.

USAGE
-----

Configure `web.php` to disable Yii's built-in asset converter and use the new
one:

```php
$config = [
    ...
    'components' => [
        'assetManager' => [
            'converter' => 'lucidtaz\yii2scssphp\ScssAssetConverter',
        ],
        ...
    ],
    ...
];
```

If the `AppAsset` is placed in `/assets` and the scss file in
`/assets/source/site.scss`, your `AppAsset.php` could look like:

```php
namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/source';
    public $css = [
        'site.scss',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```
