<?php

declare(strict_types=1);

namespace Test\Middleware;

use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Obullo\Error\AbstractErrorGenerator;

class ErrorResponseGenerator extends AbstractErrorGenerator
{
    public function __invoke(Throwable $exception, ServerRequestInterface $request, ResponseInterface $response)
    {
        $response = $response->withStatus(500);

        $this->triggerErrorEvent($exception);
        $html = $this->render($this->getViewModel());
        
        $response->getBody()->write($html);
        return $response;
    }
}
