<?php

namespace App\Pages;

use Obullo\View\PageView;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestViewModel extends PageView
{
    public function onGet(Request $request)
    {
    	$this->view->view = $this->view;

        return new HtmlResponse($this->render($this->view));
    }

    public function onPageLayout(Request $request)
    {
        return new HtmlResponse($this->layout->getTemplate());
    }

    public function onMethodQuery(Request $request)
    {
    	$this->view->view = $this->view;

        return new HtmlResponse($this->render($this->view));
    }
}