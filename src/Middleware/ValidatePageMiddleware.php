<?php

namespace Obullo\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Obullo\Router\Router;
use Zend\Diactoros\Response\TextResponse;

class ValidatePageMiddleware implements MiddlewareInterface
{
    protected $route;

    /**
     * Constructor
     *
     * @param Router $router router
     */
    public function __construct(Router $router)
    {
        $this->route = $router->getMatchedRoute();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $handler = $this->route->getHandler();

        if (! file_exists(ROOT.'/src/'.$handler)) {
            return new TextResponse(
                sprintf(
                    'The page "%s" does not exists.',
                    $handler,
                    405
                )
            );
        }
    }
}
