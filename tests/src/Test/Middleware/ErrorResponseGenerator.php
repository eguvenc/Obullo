<?php

namespace Test\Middleware;

use Throwable;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Laminas\View\View;
use Laminas\View\Model\ViewModel;

class ErrorResponseGenerator
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Throwable $exception, ServerRequestInterface $request, ResponseInterface $response)
    {
        $module = strstr(__NAMESPACE__, '\\', true);

        $config = $this->container->get('config');
        $isDevelopmentMode = empty($config['view_manager']['display_exceptions']) ? false : true;
        $response = $response->withStatus(500);

        $view = $this->container->get(View::class);
        $viewModel = new ViewModel;
        $viewModel->exception = $exception;
        $viewModel->isDevelopmentMode = $isDevelopmentMode;
        $viewModel->setTemplate($module.'\Pages\Templates\ErrorsAndExceptions');
        $viewModel->setOption('has_parent', true);
        $html = $view->render($viewModel);
        
        $response->getBody()->write($html);
        return $response;
    }
}
