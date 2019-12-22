<?php

use PHPUnit\Framework\TestCase;
use Obullo\View\ViewModel;

class ViewModelTest extends TestCase
{
    public function setUp()
    {
        $this->view = new ViewModel;
    }

    public function testLayoutModel()
    {
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->view);
    }
}
