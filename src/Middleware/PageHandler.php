<?php

namespace Obullo\Middleware;

use Obullo\Router\Router;
use Obullo\Container\ContainerAwareInterface;
use Obullo\Container\ContainerAwareTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

use Throwable;
use Exception;
use ReflectionClass;
use ReflectionMethod;

class PageHandler implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Container
     *
     * @var object
     */
    protected $route;

    /**
     * Constructor
     *
     * @param ContainerInterface $container container
     */
    public function __construct(Router $router)
    {
        $this->route = $router->getMatchedRoute();
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
        $handlerClass = $this->route->getHandler();
        $pageModel = $container->build($handlerClass);
        $pageModel->request = $request;

        $usedTraits = class_uses($pageModel);
        foreach ($usedTraits as $trait) {
            if (strstr($trait, '_Layout')) {
                $layoutName = substr(strrchr($trait, "\\"), 1);
                $layoutName = substr($layoutName, 0, -6);
                $pageModel->layoutModel->setTemplate('_Layout/'.$layoutName);
            }
        }
        $method = $request->getMethod();
        $queryParams = $request->getQueryParams();
        $reflection = new ReflectionClass($pageModel);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $classMethod) {
            if (isset($queryParams[$classMethod->name])) {
                $method = substr($classMethod->name, 2);
            }
        }
        $methodName = 'on'.ucfirst($method);
        $response = $pageModel->$methodName($request);

        return $response;
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
     * Get class from plugin manager
     *
     * @param  string $class class
     * @return object
     */
    public function plugin($class)
    {
        return $this->getContainer()->get('plugin')->get($class);
    }
}
