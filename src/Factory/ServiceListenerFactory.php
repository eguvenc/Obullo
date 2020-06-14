<?php

namespace Obullo\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ModuleManager\Listener\ServiceListenerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ServiceListenerFactory implements FactoryInterface
{
    /**
     * @var string
     */
    const MISSING_KEY_ERROR = 'Invalid service listener options detected, %s array must contain %s key.';

    /**
     * @var string
     */
    const VALUE_TYPE_ERROR = 'Invalid service listener options detected, %s must be a string, %s given.';

    /**
     * Default app-related service configuration -- can be overridden by modules.
     *
     * @var array
     */
    protected $defaultServiceConfig = [
        'aliases' => [
            'config'                                     => 'Laminas\Config\Config',
            'Config'                                     => 'Laminas\Config\Config',
            'request'                                    => 'Obullo\Http\ServerRequest',
            'Request'                                    => 'Obullo\Http\ServerRequest',
            'router'                                     => 'Obullo\Router\Router',
            'Router'                                     => 'Obullo\Router\Router',
            'RouteListener'                              => 'Obullo\RouteListener',
            'Psr\Http\Message\ServerRequestInterface'    => 'Obullo\Http\ServerRequest',
            'ViewHelperManager'                          => 'Laminas\View\ViewHelperManagerFactory',
            'ViewPhpRenderer'                            => 'Laminas\View\Renderer\PhpRenderer',
            'Laminas\View\Renderer\RendererInterface'    => 'Laminas\View\Renderer\PhpRenderer',
        ],
        'invokables' => [],
        'factories'  => [
            'Application'                    => ApplicationFactory::class,
            'Laminas\Config\Config'          => 'Obullo\Factory\ConfigFactory',
            'Obullo\Http\ServerRequest'      => 'Obullo\Factory\RequestFactory',
            'Obullo\Router\Router'           => 'Obullo\Factory\RouterFactory',
            'Obullo\Router\RouteCollectionInterface' => 'Obullo\Factory\RouteCollectionFactory',
            'Obullo\RouteListener'           => InvokableFactory::class,
            'DispatchListener'               => 'Obullo\Factory\DispatchListenerFactory',
            'DatabaseLogger'                 => 'Obullo\Factory\DatabaseLoggerFactory',
            'SQLDevelopmentLogger'           => 'Obullo\Factory\SQLDevelopmentLoggerFactory',
            'ViewHelperManager'              => 'Obullo\Factory\ViewHelperManagerFactory',
            'Laminas\View\ViewHelperManagerFactory' => 'Obullo\Factory\ViewHelperManagerFactory',
            'Laminas\View\Renderer\PhpRenderer' => 'Obullo\Factory\ViewPhpRendererFactory',
            'Laminas\View\View'              => 'Obullo\Factory\ViewFactory',
            'Laminas\Escaper\Escaper'        => 'Obullo\Factory\EscaperFactory',
        ],
    ];

    /**
     * Create the service listener service
     *
     * Tries to get a service named ServiceListenerInterface from the service
     * locator, otherwise creates a ServiceListener instance, passing it the
     * container instance and the default service configuration, which can be
     * overridden by modules.
     *
     * It looks for the 'service_listener_options' key in the application
     * config and tries to add service/plugin managers as configured. The value
     * of 'service_listener_options' must be a list (array) which contains the
     * following keys:
     *
     * - service_manager: the name of the service manage to create as string
     * - config_key: the name of the configuration key to search for as string
     * - interface: the name of the interface that modules can implement as string
     * - method: the name of the method that modules have to implement as string
     *
     * @param  ServiceLocatorInterface  $serviceLocator
     * @return ServiceListenerInterface
     * @throws ServiceNotCreatedException for invalid ServiceListener service
     * @throws ServiceNotCreatedException For invalid configurations.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configuration   = $container->get('appConfig');

        $serviceListener = $container->has('ServiceListenerInterface')
            ? $container->get('ServiceListenerInterface')
            : new ServiceListener($container);

        if (! $serviceListener instanceof ServiceListenerInterface) {
            throw new ServiceNotCreatedException(
                'The service named ServiceListenerInterface must implement '
                .  ServiceListenerInterface::class
            );
        }

        $serviceListener->setDefaultServiceConfig($this->defaultServiceConfig);

        if (isset($configuration['service_listener_options'])) {
            $this->injectServiceListenerOptions($configuration['service_listener_options'], $serviceListener);
        }

        return $serviceListener;
    }

    /**
     * Validate and inject plugin manager options into the service listener.
     *
     * @param array $options
     * @param ServiceListenerInterface $serviceListener
     * @throws ServiceListenerInterface for invalid $options types
     */
    private function injectServiceListenerOptions($options, ServiceListenerInterface $serviceListener)
    {
        if (! is_array($options)) {
            throw new ServiceNotCreatedException(sprintf(
                'The value of service_listener_options must be an array, %s given.',
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $key => $newServiceManager) {
            $this->validatePluginManagerOptions($newServiceManager, $key);

            $serviceListener->addServiceManager(
                $newServiceManager['service_manager'],
                $newServiceManager['config_key'],
                $newServiceManager['interface'],
                $newServiceManager['method']
            );
        }
    }

    /**
     * Validate the structure and types for plugin manager configuration options.
     *
     * Ensures all required keys are present in the expected types.
     *
     * @param array $options
     * @param string $name Plugin manager service name; used for exception messages
     * @throws ServiceNotCreatedException for any missing configuration options.
     * @throws ServiceNotCreatedException for configuration options of invalid types.
     */
    private function validatePluginManagerOptions($options, $name)
    {
        if (! is_array($options)) {
            throw new ServiceNotCreatedException(sprintf(
                'Plugin manager configuration for "%s" is invalid; must be an array, received "%s"',
                $name,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        if (! isset($options['service_manager'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'service_manager'));
        }

        if (! is_string($options['service_manager'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'service_manager',
                gettype($options['service_manager'])
            ));
        }

        if (! isset($options['config_key'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'config_key'));
        }

        if (! is_string($options['config_key'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'config_key',
                gettype($options['config_key'])
            ));
        }

        if (! isset($options['interface'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'interface'));
        }

        if (! is_string($options['interface'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'interface',
                gettype($options['interface'])
            ));
        }

        if (! isset($options['method'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'method'));
        }

        if (! is_string($options['method'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'method',
                gettype($options['method'])
            ));
        }
    }
}
