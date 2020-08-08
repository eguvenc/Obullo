<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\I18n\Translator\TranslatorInterface;

class SetLocaleModel extends View
{
	public function __construct(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

    public function onGet()
    {
    	return new HtmlResponse($this->translator->getLocale());
    }
}
