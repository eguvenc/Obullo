<?php

namespace Obullo;

use ReflectionClass;
use ReflectionParameter;
use Obullo\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Obullo\Exception\PageMethodNotExistsException;
use Obullo\Exception\InvalidPageResponseException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

final class Dispatcher
{
    /**
     * @var string model method name
     */
    private $method;

    /**
     * @var model
     */
    private $model;

    /**
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * @var Laminas\ServiceManager\ServiceManager
     */
    private $container;

    /**
     * @var Obullo\Router\Router
     */
    private $router;

    /**
     * Dispatch options
     *
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param array $options options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Set method name
     *
     * @param string $method name
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Returns to method name
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Set router
     *
     * @param Router $router router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Returns to router
     *
     * @return Obullo\Router\Router
     */
    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * Set service manager
     *
     * @param string $container service manager
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Returns to container
     *
     * @return object
     */
    private function getContainer()
    {
        return $this->container;
    }

    /**
     * Set page model
     *
     * @param object $model page handler
     */
    public function setPageModel($model)
    {
        $this->model = $model;
    }

    /**
     * Returns to page model
     *
     * @return object
     */
    public function getPageModel()
    {
        return $this->model;
    }

    /**
     * Set reflection object
     *
     * @param ReflectionClass $reflection reflection class
     */
    public function setReflectionClass(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Returns to reflection object
     *
     * @return object
     */
    public function getReflectionClass() : ReflectionClass
    {
        $reflection = ($this->reflection) ? $this->reflection : new ReflectionClass($this->getPageModel());

        return $reflection;
    }

    /**
     * Execute page handler
     *
     * @return null|response object
     * @throws PageMethodNotExistsException if display_exceptions is not true
     */
    public function dispatch()
    {
        $methodName = $this->getMethod();
        $container  = $this->getContainer();
        $reflection = $this->getReflectionClass();
        $requestedName = $reflection->getName();

        if ($reflection->hasMethod($methodName)) {
            $reflectionParameters = $reflection->getMethod($methodName)->getParameters();
            if (empty($reflectionParameters)) {
                $response = $this->getPageModel()->$methodName();
                $this->isResponse($response, $requestedName);
                return $response;
            }
            $resolver = $this->resolveParameterWithConfigService($container, $requestedName);
            $parameters = array_map($resolver, $reflectionParameters);
            $response = $this->getPageModel()->$methodName(...$parameters);
            $this->isResponse($response, $requestedName);
            return $response;
        }
        if (isset($this->options['partial_view'])) {
            throw new PageMethodNotExistsException(
                sprintf(
                    'The method %s does not exists in %s.',
                    $methodName,
                    $reflection->getName()
                )
            );
        }
        return null; // 404 not found
    }

    /**
     * Check response
     *
     * @param  object  $response response
     * @param  string  $requestedName model name
     * @return void
     * @throws PageNullResponseException is response null
     */
    private function isResponse($response, $requestedName)
    {
        if (null == $response) {
            throw new InvalidPageResponseException(
                sprintf(
                    'Return value of %s must be an instance of Psr\Http\Message\ResponseInterface, null returned',
                    $requestedName
                )
            );
        }
    }

    /**
     * Returns a callback for resolving a parameter to a value, including mapping 'config' arguments.
     *
     * Unlike resolveParameter(), this version will detect `$config` array
     * arguments and have them return the 'config' service.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return callable
     */
    private function resolveParameterWithConfigService(ContainerInterface $container, $requestedName)
    {
        /**
         * @param ReflectionParameter $parameter
         * @return mixed
         * @throws ServiceNotFoundException If type-hinted parameter cannot be
         *   resolved to a service in the container.
         */
        return function (ReflectionParameter $parameter) use ($container, $requestedName) {
            if ($parameter->isArray() && $parameter->getName() === 'config') {
                return $container->get('config');
            }
            return $this->resolveParameter($parameter, $container, $requestedName);
        };
    }

    /**
     * Logic common to all parameter resolution.
     *
     * @param ReflectionParameter $parameter
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return mixed
     * @throws ServiceNotFoundException If type-hinted parameter cannot be
     *   resolved to a service in the container.
     */
    private function resolveParameter(ReflectionParameter $parameter, ContainerInterface $container, $requestedName)
    {
        if ($parameter->isArray()) {
            return [];
        }

        if (! $parameter->getClass()) {
            $name = $parameter->getName();
            $args = $this->getRouter()->getMatchedRoute()->getArguments();

            if (array_key_exists($name, $args)) {
                return $args[$name];
            }
            if (! $parameter->isDefaultValueAvailable()) {
                throw new ServiceNotFoundException(sprintf(
                    'Unable to create service "%s"; unable to resolve parameter "%s" '
                    . 'to a class, interface, or array type',
                    $requestedName,
                    $name
                ));
            }

            return $parameter->getDefaultValue();
        }

        $type = $parameter->getClass()->getName();
        $type = isset($this->aliases[$type]) ? $this->aliases[$type] : $type;

        if ($container->has($type)) {
            return $container->get($type);
        }

        if (! $parameter->isOptional()) {
            throw new ServiceNotFoundException(sprintf(
                'Unable to create service "%s"; unable to resolve parameter "%s" using type hint "%s"',
                $requestedName,
                $parameter->getName(),
                $type
            ));
        }

        // Type not available in container, but the value is optional and has a
        // default defined.
        return $parameter->getDefaultValue();
    }
}
