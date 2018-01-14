<?php

namespace lucidtaz\yii2scssphp\tests\integration\installer;

$projectRoot = dirname(dirname(__DIR__));

require($projectRoot . '/vendor/autoload.php');

$testApplicationPath = sys_get_temp_dir() . '/yii2-scssphp-integration-test-root';
//$testApplicationPath = $projectRoot . '/integration-test-root';

$output = function (string $line) {
    echo $line;
};

$installer = new Installer($projectRoot, $testApplicationPath, $output);
$installer->run();
