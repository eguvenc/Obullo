<?php

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Laminas\View\View;
use Laminas\View\Model\ViewModel;
use Laminas\Diactoros\Response\HtmlResponse;

class ErrorNotFoundHandler implements MiddlewareInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $module = strstr(__NAMESPACE__, '\\', true);

        $view = $this->container->get(View::class);
        $viewModel = new ViewModel;
        $viewModel->setTemplate($module.'\Pages\Templates\ErrorNotFound');
        $viewModel->setOption('has_parent', true);
        $html = $view->render($viewModel);

        return new HtmlResponse($html, 404);
    }
}
