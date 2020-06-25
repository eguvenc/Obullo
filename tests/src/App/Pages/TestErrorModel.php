<?php

namespace App\Pages;

use Obullo\Router\Router;
use Obullo\View\PageView;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestErrorModel extends PageView
{
    public function onGet(Request $request, Router $router)
    {
        throw new \Exception('Test Exception');
    }
}
