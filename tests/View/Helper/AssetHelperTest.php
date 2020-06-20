<?php

use PHPUnit\Framework\TestCase;
use Obullo\View\Helper\Asset;

class AssetHelperTest extends TestCase
{
    public function setUp()
    {
        $appConfig = require __DIR__ . '/../../config/application.config.php';

        $this->asset = new Asset;
        $this->asset->setPath($appConfig['root'] . '/Resources/');
    }

    public function testAsset()
    {
        $asset = $this->asset;
        $url = $asset('/test.css', false);
        $this->assertEquals('/test.css', $url);
        
        $url = $asset('/test.css');
        $assetFile = $asset->getPath().'/test.css';
        $lastUpdated = filemtime($assetFile);
        $this->assertEquals('/test.css?v='.$lastUpdated, $url);
    }

    public function testAssetNotFoundException()
    {
        $asset = $this->asset;
        try {
            $url = $asset('/dummy.css', false);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('Unable to locate "dummy.css" in the asset directory.', $message);
    }
}
