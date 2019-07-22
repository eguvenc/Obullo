<?php

namespace Obullo\Middleware;

use Obullo\Router\Router;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

use Throwable;

class PageHandler implements MiddlewareInterface
{
    /**
     * Container
     * @var object
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $container->setService('route', $container->get(Router::class)->getMatchedRoute());
    }

    /**
     * Process
     *
     * @param  ServerRequestInterface  $request request
     * @param  RequestHandlerInterface $handler request handler
     *
     * @return object|exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $container = $this->getContainer();
        try {
            $level = ob_get_level();
            return require ROOT.'/src/'.$container->get('route')->getHandler();
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
    }

    /**
     * Call plugin methods
     *
     * @param  string $method name
     * @param  array  $args   arguments
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->getContainer()->get('plugin')->$method(...$args);
    }

    /**
     * Returns to container
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
