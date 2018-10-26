<?php

namespace lucidtaz\yii2scssphp\tests;

use Leafo\ScssPhp\Compiler;

class FixedResultCompiler extends Compiler
{
    public function compile($code, $path = null)
    {
        return 'Fixed Result';
    }
}
