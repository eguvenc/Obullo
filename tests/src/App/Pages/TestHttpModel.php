<?php

namespace App\Pages;

use Obullo\Router\Router;
use Obullo\View\View;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestHttpModel extends View
{
	public function onPost(array $post)
    {
        return new HtmlResponse($post['test']);
    }

    public function onPut(array $post)
    {
        return new HtmlResponse($post['test']);
    }

    public function onPatch(array $post)
    {
        return new HtmlResponse($post['test']);
    }

	public function onOptions(array $post)
    {
        return new HtmlResponse($post['test']);
    }

    public function onHead(array $get)
    {
        return new HtmlResponse($get['test']);
    }

    public function onGet(array $get)
    {
        return new HtmlResponse($get['test']);
    }

    public function onTrace(array $get)
    {
        return new HtmlResponse($get['test']);
    }

    public function onConnect(array $get)
    {
        return new HtmlResponse($get['test']);
    }

    public function onDelete(array $get)
    {
        return new HtmlResponse($get['test']);
    }

    public function onPropfind(array $get)
    {
        return new HtmlResponse($get['test']);
    }
}