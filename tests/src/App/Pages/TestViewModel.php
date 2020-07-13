<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestViewModel extends View
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

    public function onPlugin(Request $request)
    {
        $url = $this->plugin('url');
        $result = $url('/test_view');

        return new HtmlResponse($result);
    }

    public function onModel(Request $request)
    {
        return $this->model('App\Pages\Templates\HeaderModel');
    }
}
