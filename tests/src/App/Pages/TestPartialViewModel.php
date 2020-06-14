<?php

namespace App\Pages;

use Obullo\View\PageView;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestPartialViewModel extends PageView
{
    public function onGet(Request $request)
    {
        return new HtmlResponse($this->render($this->view));
    }

    public function onQueryMethod(Request $request)
    {
        return $this->model('App\Pages\Templates\HeaderModel');
    }
}