<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class TestErrorModel extends View
{
    public function onGet()
    {
        throw new \Exception('Test Exception');
    }
}
