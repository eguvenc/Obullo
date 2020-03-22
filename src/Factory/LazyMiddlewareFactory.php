<?php

namespace Obullo\Factory;

use ReflectionClass;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class LazyMiddlewareFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param Container $container
     * @param $name
     * @param $requestedName
     *
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return strstr($requestedName, 'Middleware\\') !== false;
    }

    /**
     * These aliases work to substitute class names with Service Manager types that are buried in framework
     * 
     * @var array
     */
    protected $aliases = [];

    /**
     * Create service with name
     *
     * @param Container $container
     * @param $requestedName
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $class = new ReflectionClass($requestedName);

        $injectedParameters = array();
        if ($constructor = $class->getConstructor()) {
            if ($params = $constructor->getParameters()) {
                foreach($params as $param) {
                    if ($param->getClass()) {
                        $name = $param->getClass()->getName();
                        if (array_key_exists($name, $this->aliases)) {
                            $name = $this->aliases[$name];
                        }
                        if ($container->has($name)) {
                            $injectedParameters[] = $container->get($name);
                        }
                    }
                }
                $injectedParameters[] = $options;
            }
        }
        return new $requestedName(...$injectedParameters);
    }
}