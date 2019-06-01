<?php

namespace Obullo\Pages;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

use App\Middleware\HttpMethodMiddleware;
use Zend\Stratigility\MiddlewarePipeInterface;

class PageMiddleware implements MiddlewareInterface
{
    /**
     * Response
     * @var null|object
     */
    private $response;

    /**
     * Container
     * @var object
     */
    protected $container;

    /**
     * Constructor
     *
     * @param MiddlewarePipeInterface $pipeline  middleware pipe
     * @param ContainerInterface      $container container
     */
    public function __construct(MiddlewarePipeInterface $pipeline, ContainerInterface $container)
    {
        $this->container = $container;

        $router = $container->get('router');
        $route  = $router->matchRequest();

        if ($route && file_exists(ROOT.'/src/'.$route->getHandler())) {
            $pipeline->pipe(new HttpMethodMiddleware($router));
            foreach ($router->getStack() as $middleware) {
                $pipeline->pipe($container->build($middleware));
            }
            $this->response = require ROOT.'/src/'.$route->getHandler();
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return ($this->response) ? $this->response : $handler->handle($request);
    }

    /**
     * Container proxy
     *
     * @param  string $name requested name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }
}
