<?php

use Obullo\View\Engine;
use Obullo\View\Template;

class RenderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->engine = new Engine(
        	[
        		'file_extension' => null,
        		'default_directory' => ROOT.'/src',
    	    ]
    	);
    }

    public function testRender()
    {
    	$page = new Template($this->engine);
		$page->header = $page->render('header.phtml');
		$page->footer = $page->render('footer.phtml');
		$page->start();
		?>test<?php
		$page->content = $page->end();
		$html = $page->render('template.phtml');
		$this->assertEquals('<header>header</header>test<footer>footer</footer>', $html);
    }

}