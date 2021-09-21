<?php

namespace lucidtaz\yii2scssphp\tests\unit;

use ScssPhp\ScssPhp\Compiler;

class FixedResultCompiler extends Compiler
{
    public function compile($code, $path = null)
    {
        return 'Fixed Result';
    }
}
