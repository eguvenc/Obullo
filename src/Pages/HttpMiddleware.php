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
     * Container
     * @var object
     */
    protected $container;

    /**
     * Page found
     * @var boolean
     */
    private $pageFound;

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
            $this->pageFound = true;
            $container->setService('route', $route);
            $pipeline->pipe(new HttpMethodMiddleware($router));
            foreach ($router->getStack() as $middleware) {  // Assign route middlewares
                $pipeline->pipe($container->build($middleware));
            }
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        /**
         * We should process the pages here.
         *
         * Middlewares must initialize before the process.
         */
        if ($this->pageFound) {
            $container = $this->getContainer();
            return require ROOT.'/src/'.$container->get('route')->getHandler();
        }
        return $handler->handle($request);
    }

    /**
     * Returns to containar
     *
     * @return object
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
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
