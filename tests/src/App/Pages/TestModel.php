<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class TestModel extends View
{
    public function onGet()
    {
        $this->view->setTemplate('App/Pages/Test');

        return new HtmlResponse($this->render($this->view));
    }

	public function onQueryMethod(array $get)
    {
        return new HtmlResponse($get['test']);
    }
}
