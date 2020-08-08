<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class TestPartialViewModel extends View
{
    public function onGet()
    {
        return new HtmlResponse($this->render($this->view));
    }

    public function onQueryMethod()
    {
        return $this->model('App\Pages\Templates\HeaderModel');
    }
}