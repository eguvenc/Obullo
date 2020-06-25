<?php

declare(strict_types=1);

namespace App\Middleware;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\View\View;
use Laminas\View\Model\ViewModel;

class ErrorResponseGenerator
{
	private $renderer;
	private $config;

    public function __construct(View $renderer, array $config)
    {
        $this->renderer = $renderer;
        $this->config = $config;
    }

    public function __invoke(Throwable $exception, ServerRequestInterface $request, ResponseInterface $response)
    {
        $viewModel = new ViewModel;
        $viewModel->exception = $exception;
        $viewModel->isDevelopmentMode = empty($this->config['view_manager']['display_exceptions']) ? false : true;
        $viewModel->setTemplate('App/Pages/Templates/ErrorsAndExceptions');
        $viewModel->setOption('has_parent', true);
        
        $response = $response->withStatus(500);
        $response->getBody()->write($this->renderer->render($viewModel));
        return $response;
    }
}