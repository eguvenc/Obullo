<?php

namespace App\Pages\Templates;

use Obullo\View\PartialView;
use Laminas\Diactoros\Response\HtmlResponse;

class HeaderModel extends PartialView
{
    public function onGet()
    {
        return new HtmlResponse($this->render($this->view));
    }
}
