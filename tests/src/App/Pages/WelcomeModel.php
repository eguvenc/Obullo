<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class WelcomeModel extends View
{
    public function onGet()
    {
        return new HtmlResponse('Welcome');
    }
}
