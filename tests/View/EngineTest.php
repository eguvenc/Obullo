<?php

use Obullo\View\Engine;

class EngineTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->engine = new Engine(
        	[
        		'file_extension' => 'phtml',
        		'default_directory' => ROOT.'/var',
    	    ]
    	);
    }

    public function testDirectory()
    {
        $this->engine->setDirectory(ROOT. '/src');
        $this->assertEquals(ROOT. '/src', $this->engine->getDirectory());
    }

    public function testOptions()
    {
    	$options = $this->engine->getOptions();

    	$this->assertEquals('phtml', $options['file_extension']);
    	$this->assertEquals(ROOT.'/var', $options['default_directory']);
    }

    public function testFileExtensions()
    {
        $this->engine->setFileExtension('phtml');
        $this->assertEquals('phtml', $this->engine->getFileExtension());
    }

    public function testFolders()
    {
        $this->engine->addFolder('test1', ROOT. '/src/test1');
        $this->engine->addFolder('test2', ROOT. '/src/test2');

        $folders = $this->engine->getFolders();

        $folder1 = $folders->get('test1');
        $folder2 = $folders->get('test2');

        $this->assertEquals('test1', $folder1->getName());
        $this->assertEquals('test2', $folder2->getName());

        $folders->remove('test1');
        $folders->remove('test2');

        $this->assertFalse($folders->exists('test1'));
        $this->assertFalse($folders->exists('test2'));
    }

    public function testPath()
    {
        $this->engine->setFileExtension(null);
        $this->engine->setDirectory(ROOT. '/src');
        $this->assertEquals($this->engine->path('test1'), ROOT. '/src/test1');
    }

    public function testExists()
    {
        $this->engine->setDirectory(ROOT. '/src');
        $this->engine->setFileExtension('phtml');
        $this->assertTrue($this->engine->exists('test'));
    }


}