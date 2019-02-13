<?php

namespace lucidtaz\yii2scssphp\tests\unit;

use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Formatter\Compressed;
use lucidtaz\yii2scssphp\ScssAssetConverter;
use PHPUnit\Framework\TestCase;
use Yii;

class DependencyInjectionTest extends TestCase
{
    public function testThatItInstantiatesFromClassName(): void
    {
        $assetConverter = Yii::createObject(ScssAssetConverter::class);
        $this->assertInstanceOf(ScssAssetConverter::class, $assetConverter);
    }

    public function testThatItInstantiatesFromConfigArray(): void
    {
        $assetConverter = Yii::createObject([
            'class' => ScssAssetConverter::class,
        ]);
        $this->assertInstanceOf(ScssAssetConverter::class, $assetConverter);
    }

    public function testThatScalarAttributeCanBeCustomized(): void
    {
        $control = new ScssAssetConverter();
        $this->assertFalse($control->forceConvert, 'Test precondition');

        $assetConverter = Yii::createObject([
            'class' => ScssAssetConverter::class,
            'forceConvert' => true,
        ]);
        $this->assertInstanceOf(ScssAssetConverter::class, $assetConverter);
        /** @var ScssAssetConverter $assetConverter */

        $this->assertTrue($assetConverter->forceConvert, 'Attribute must be taken from DI parameters');
    }

    public function testThatCompilerCanBeCustomizedDirectly(): void
    {
        $input = "#blop { color: black; display: block; }";
        $expectedDefaultOutput = "#blop {\n  color: black;\n  display: block; }\n";
        $expectedCustomizedOutput = "#blop{color:black;display:block}";

        $control = new Compiler();
        $controlCompiled = $control->compile($input);
        $this->assertEquals($expectedDefaultOutput, $controlCompiled,'Test precondition');

        $compiler = new Compiler();
        $compiler->setFormatter(Compressed::class);
        $assetConverter = Yii::createObject([
            'class' => ScssAssetConverter::class,
            'compiler' => $compiler,
        ]);
        $this->assertInstanceOf(ScssAssetConverter::class, $assetConverter);
        /** @var ScssAssetConverter $assetConverter */

        $compiled = $assetConverter->compiler->compile($input);

        $this->assertEquals($expectedCustomizedOutput, $compiled, 'DI formatter customization has effect on compiled output');
    }

    public function testThatCompilerCanBeCustomizedInContainer(): void
    {
        $input = "#blop { color: black; display: block; }";
        $expectedDefaultOutput = "#blop {\n  color: black;\n  display: block; }\n";
        $expectedCustomizedOutput = "#blop{color:black;display:block}";

        $control = new Compiler();
        $controlCompiled = $control->compile($input);
        $this->assertEquals($expectedDefaultOutput, $controlCompiled,'Test precondition');

        Yii::$container->set(Compiler::class, function () {
            $compiler = new Compiler();
            $compiler->setFormatter(Compressed::class);
            return $compiler;
        });
        $assetConverter = Yii::createObject(ScssAssetConverter::class);
        $this->assertInstanceOf(ScssAssetConverter::class, $assetConverter);
        /** @var ScssAssetConverter $assetConverter */

        $compiled = $assetConverter->compiler->compile($input);

        $this->assertEquals($expectedCustomizedOutput, $compiled, 'DI formatter customization has effect on compiled output');

        Yii::$container->clear(Compiler::class);
    }

    public function testThatOverriddenCompilerCanBeBoundInContainer(): void
    {
        $input = "#blop { color: black; display: block; }";
        $expectedDefaultOutput = "#blop {\n  color: black;\n  display: block; }\n";
        $expectedCustomizedOutput = "Fixed Result";

        $control = new Compiler();
        $controlCompiled = $control->compile($input);
        $this->assertEquals($expectedDefaultOutput, $controlCompiled,'Test precondition');

        Yii::$container->set(Compiler::class, FixedResultCompiler::class);
        $assetConverter = Yii::createObject(ScssAssetConverter::class);
        $this->assertInstanceOf(ScssAssetConverter::class, $assetConverter);
        /** @var ScssAssetConverter $assetConverter */

        $compiled = $assetConverter->compiler->compile($input);

        $this->assertEquals($expectedCustomizedOutput, $compiled, 'DI formatter customization has effect on compiled output');

        Yii::$container->clear(Compiler::class);
    }
}
