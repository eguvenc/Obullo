<?php

use Obullo\View\Template\Directory;
use Obullo\View\Template\FileExtension;
use Obullo\View\Template\Folder;
use Obullo\View\Template\Folders;
use Obullo\View\Template\Name;
use Obullo\View\Engine;

class TemplateTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $engine = new Engine;
        $this->directory = new Directory;
        $this->fileExtension = new FileExtension;
        $this->folder = new Folder('test', ROOT. '/src');
        $this->folders = new Folders;

        $engine->setDirectory(ROOT. '/src');
        $engine->setFileExtension('phtml');
        $this->name = new Name($engine, 'test');
    }

    public function testDirectory()
    {
        $this->directory->set(ROOT. '/src');
        $this->assertEquals(ROOT. '/src', $this->directory->get());
    }

    public function testFileExtension()
    {
        $this->assertEquals('php', $this->fileExtension->get());
        $this->fileExtension->set('phtml');
        $this->assertEquals('phtml', $this->fileExtension->get());
    }

    public function testFolder()
    {
        $this->assertEquals('test', $this->folder->getName());
        $this->assertEquals(ROOT. '/src', $this->folder->getPath());
        $this->folder->setFallback('fallback');
        $this->assertEquals('fallback', $this->folder->getFallback());
    }

    public function testFolders()
    {
        $this->folders->add('test1', ROOT. '/src/test1');
        $this->folders->add('test2', ROOT. '/src/test2');

        $this->assertTrue($this->folders->exists('test1'));
        $this->assertTrue($this->folders->exists('test2'));

        $folder1 = $this->folders->get('test1');
        $folder2 = $this->folders->get('test2');

        $this->assertEquals('test1', $folder1->getName());
        $this->assertEquals('test2', $folder2->getName());

        $this->folders->remove('test1');
        $this->folders->remove('test2');

        $this->assertFalse($this->folders->exists('test1'));
        $this->assertFalse($this->folders->exists('test2'));
    }

    public function testName()
    {
        $this->assertEquals('test', $this->name->getName());
        $this->assertEquals('test.phtml', $this->name->getFile());
        $this->assertEquals($this->name->getPath(), ROOT. '/src/test.phtml');

        $this->assertInstanceOf('Obullo\View\Engine', $this->name->getEngine());
        $this->assertTrue($this->name->doesPathExist());
    }
}
