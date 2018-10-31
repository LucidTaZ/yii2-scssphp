<?php

namespace lucidtaz\yii2scssphp\tests\integration;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    /** @var Client */
    private $client;

    protected function setUp()
    {
        parent::setUp();
        $this->client = new Client([
            'base_uri' => 'http://localhost:8080',
            'exceptions' => false,
        ]);
    }

    public function testThatItServesIndex(): string
    {
        $response = $this->client->get('/');
        $this->assertEquals(200, $response->getStatusCode());

        return $response->getBody()->getContents();
    }

    /**
     * @depends testThatItServesIndex
     */
    public function testThatAssetConversionWorks(string $indexHtml): string
    {
        // Generated assets have a hash in the path...
        $matches = [];
        $assetUrlFound = preg_match(
            '#<link href="(/assets/[0-9a-f]{8}/test.css)" rel="stylesheet">#',
            $indexHtml,
            $matches
        );
        $this->assertEquals(1, $assetUrlFound);

        $testCssUrl = $matches[1];

        $response = $this->client->get($testCssUrl);
        $this->assertEquals(200, $response->getStatusCode(), 'File is converted');

        return $response->getBody()->getContents();
    }

    /**
     * @depends testThatAssetConversionWorks
     */
    public function testThatItUsesCustomizedConfiguration(string $css)
    {
        // Verify that the customization done in config/web.php works.
        // Integration test is set up with crunched formatter
        $expectedContents = '#blop{color:black}';
        $this->assertEquals($expectedContents, $css);
    }
}
