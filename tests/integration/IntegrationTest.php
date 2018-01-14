<?php

namespace lucidtaz\yii2scssphp\tests\integration;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Base class for tests using PHP's built-in webserver
 * @see https://medium.com/@peter.lafferty/start-phps-built-in-web-server-from-phpunit-9571f38c5045
 * @see https://github.com/peterlafferty/phpunitinbuiltweb
 */
class IntegrationTest extends TestCase
{
    /** @var Process */
    private static $process;

    public static function setUpBeforeClass()
    {
        $testApplicationPath = sys_get_temp_dir() . '/yii2-scssphp-integration-test-root';
        $webroot = $testApplicationPath . '/web';

        self::$process = new Process('php -S localhost:8080 -t ' . escapeshellarg($webroot));
        self::$process->start();
        usleep(100000); // Wait for server to get going
    }

    public static function tearDownAfterClass()
    {
        self::$process->stop();
    }

    public function testWebserver()
    {
        $client = new Client();
        $response = $client->get('http://localhost:8080/');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAssetConversionWorks()
    {
        $client = new Client();
        $indexResponse = $client->get('http://localhost:8080/');
        $this->assertEquals(200, $indexResponse->getStatusCode());
        $index = $indexResponse->getBody()->getContents();

        // Generated assets have a hash in the path...
        $matches = [];
        $assetUrlFound = preg_match(
            '#<link href="(/assets/[0-9a-f]{8}/test.css)" rel="stylesheet">#',
            $index,
            $matches
        );
        $this->assertEquals(1, $assetUrlFound);

        $testCssUrl = $matches[1];
        $response = $client->get("http://localhost:8080$testCssUrl");
        $this->assertEquals(200, $response->getStatusCode(), 'File is converted');

        $expectedContents = "#blop {\n  color: black; }\n";
        $this->assertEquals($expectedContents, $response->getBody()->getContents());
    }
}
