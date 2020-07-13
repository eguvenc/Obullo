<?php

namespace App\Pages;

use Obullo\Router\Router;
use Obullo\View\View;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestArgsModel extends View
{
    public function onGet(array $get, Request $request, Router $router, int $id, int $number = null)
    {
        return new HtmlResponse((string)$id.(string)$number);
    }
}
