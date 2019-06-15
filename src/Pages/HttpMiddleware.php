<?php

namespace Obullo\Pages;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

use App\Middleware\HttpMethodMiddleware;
use Zend\Stratigility\MiddlewarePipeInterface;

class HttpMiddleware implements MiddlewareInterface
{
    /**
     * Route
     * @var null|object
     */
    private $route;

    /**
     * Container
     * @var object
     */
    protected $container;

    /**
     * Page found
     * @var boolean
     */
    protected $pageFound;

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
        $this->route = $router->matchRequest();

        if ($this->route && file_exists(ROOT.'/src/'.$this->route->getHandler())) {
            $this->pageFound = true;
            $pipeline->pipe(new HttpMethodMiddleware($router));
            foreach ($router->getStack() as $middleware) {  // Assign route middlewares
                $pipeline->pipe($container->build($middleware));
            }
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {   
        if ($this->pageFound) {
            $route = $this->route;
            $container = $this->container;
            return require ROOT.'/src/'.$this->route->getHandler();
        }
        return $handler->handle($request);
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
