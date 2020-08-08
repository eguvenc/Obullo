<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class PluginModel extends View
{
    public function onGet()
    {
        $this->view->setTemplate('App/Pages/Plugin');

        return new HtmlResponse($this->render($this->view));
    }
}
