<?php

namespace Obullo\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

use Zend\Stratigility\MiddlewarePipeInterface;

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
     * @param ContainerInterface      $container container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $container->setService('route', $container->get('router')->getMatchedRoute());
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $container = $this->getContainer();
        return require ROOT.'/src/'.$container->get('route')->getHandler();
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
