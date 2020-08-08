<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class TestArgsModel extends View
{
    public function onGet(array $get, int $id, int $number = null)
    {
        return new HtmlResponse((string)$id.(string)$number);
    }
}
