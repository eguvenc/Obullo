<?php

namespace App\Pages;

use Obullo\View\PageView;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class PluginModel extends PageView
{
    public function onGet(Request $request)
    {
		$this->view = new ViewModel;
        $this->view->setTemplate('App/Pages/Plugin');

        return new HtmlResponse($this->render($this->view));
    }
}
