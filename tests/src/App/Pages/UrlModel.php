<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class UrlModel extends View
{
    public function onGet()
    {
        return new HtmlResponse($this->render($this->view));
    }
}
