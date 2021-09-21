[![Build Status](https://travis-ci.org/LucidTaZ/yii2-scssphp.svg?branch=master)](https://travis-ci.org/LucidTaZ/yii2-scssphp)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LucidTaZ/yii2-scssphp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LucidTaZ/yii2-scssphp/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/LucidTaZ/yii2-scssphp/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/LucidTaZ/yii2-scssphp/?branch=master)

# Yii2 bindings for SCSS-PHP

This library provides easy integration of
[scssPhp/scssphp](https://github.com/scssphp/scssphp) into
[Yii2](https://github.com/yiisoft/yii2). Scssphp is a native PHP SCSS (SASS)
compiler. This enables you to seamlessly use SCSS while using Yii's method of
asset publication.

## Usage

Configure `web.php` to disable Yii's built-in asset converter and use the new
one:

```php
<?php

$config = [
    // Other configuration...

    'components' => [
        'assetManager' => [
            'converter' => 'lucidtaz\yii2scssphp\ScssAssetConverter',
        ],

        // Other components...
    ],

    // Other configuration...
];
```

If the `AppAsset` is placed in `/assets` and the scss file in
`/assets/source/site.scss`, your `AppAsset.php` could look like:

```php
<?php

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

In other words; you can just use the `.scss` file(s) in the `$css` array.

## Customizing the SCSS parser

The underlying library, yii2-scssphp, can be customized in case more flexibility
is needed. To this end, properties of the `ScssPhp\ScssPhp\Compiler` object can be
overridden in the DI container, as follows:

```php
<?php

$config = [
    // Other configuration...

    'components' => [
        'assetManager' => [
            'converter' => 'lucidtaz\yii2scssphp\ScssAssetConverter',
        ],

        // Other components...
    ],
    'container' => [
        'definitions' => [
            'Leafo\ScssPhp\Compiler' => function () {
                // You can also use a child class here:
                $compiler = new Leafo\ScssPhp\Compiler();

                // Customize the object, for example set a formatter:
                $compiler->setFormatter('Leafo\ScssPhp\Formatter\Compressed');

                return $compiler;
            },
        ],
    ],

    // Other configuration...
];
```

This usage in `config.php` is supported from Yii 2.0.11. Before that you should
use the DI container directly. In that case, please refer to the Yii Dependency
Injection readme.

## Contributing

When contributing code, please make sure the tests keep passing. Additionally
the code is checked by phpstan to detect any statically analyzable issues.

To run these checks, simply execute `composer ci`.

That being said, please do not hesitate to contribute when the tests fail. If
you need assistance in making the build green for your pull request, just let me
know in the PR.
