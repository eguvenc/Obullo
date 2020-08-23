<?php

namespace App\Pages;

use Obullo\View\View;
use Laminas\Diactoros\Response\HtmlResponse;

class TestViewModel extends View
{
    public function onGet()
    {
        $this->view->view = $this->view;

        return new HtmlResponse($this->render($this->view));
    }

    public function onPageLayout()
    {
        $this->layout->setTemplate('App/Pages/Templates/TestLayout');

        return new HtmlResponse($this->layout->getTemplate());
    }

    public function onMethodQuery()
    {
        $this->view->view = $this->view;

        return new HtmlResponse($this->render($this->view));
    }
    
    public function onModel()
    {
        return $this->model('App\Pages\Templates\HeaderModel');
    }
}
