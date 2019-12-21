<?php

namespace Tests\Pages;

use Obullo\View\ViewTrait;
use Zend\View\Model\ViewModel;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class PluginModel
{
    use ViewTrait;

    public function __construct()
    {
        $this->view = new ViewModel;
        $this->view->setTemplate('Pages/Plugin');
    }

    public function onGet(Request $request)
    {
        return new HtmlResponse($this->render($this->view));
    }
}
