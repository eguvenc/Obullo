<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\View\View;
use Laminas\View\Model\ViewModel;

class NotFoundResponseGenerator
{
    private $renderer;

    public function __construct(View $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $viewModel = new ViewModel;
        $viewModel->setTemplate('App/Pages/Templates/ErrorNotFound');
        $viewModel->setOption('has_parent', true);
        
        $response = $response->withStatus(404);
        $response->getBody()->write($this->renderer->render($viewModel));
        return $response;
    }
}