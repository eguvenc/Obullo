<?php

declare(strict_types=1);

namespace Test\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Obullo\Error\AbstractErrorGenerator;
use Laminas\Diactoros\Response\HtmlResponse;

class ErrorNotFoundHandler extends AbstractErrorGenerator implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->trigger404Event();
        $html = $this->render($this->getViewModel());

        return new HtmlResponse($html, 404);
    }
}
