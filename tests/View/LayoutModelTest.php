<?php

use PHPUnit\Framework\TestCase;
use Obullo\View\LayoutModel;

class LayoutModelTest extends TestCase
{
    public function setUp()
    {
        $this->layout = new LayoutModel;
    }

    public function testLayoutModel()
    {
        $this->assertInstanceOf('Obullo\View\LayoutModelInterface', $this->layout);
    }
}
