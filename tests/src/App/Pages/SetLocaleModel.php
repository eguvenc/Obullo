<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Laminas\I18n\Translator\TranslatorInterface;

class SetLocaleModel extends View
{
    public function onGet(Request $request, TranslatorInterface $translator)
    {
    	return new HtmlResponse($translator->getLocale());
    }
}
