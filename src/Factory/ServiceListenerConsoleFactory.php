<?php

namespace Obullo\Factory;

/**
 * Console specific configurations
 */
class ServiceListenerConsoleFactory extends ServiceListenerFactory
{
    /**
     * Default app-related service configuration -- can be overridden by modules.
     *
     * @var array
     */
    protected $defaultServiceConfig = [
        'aliases' => [
            'config'                                     => 'Laminas\Config\Config',
            'Config'                                     => 'Laminas\Config\Config',
            'ViewHelperManager'                          => 'Laminas\View\ViewHelperManagerFactory',
            'ViewPhpRenderer'                            => 'Laminas\View\Renderer\PhpRenderer',
            'Laminas\View\Renderer\RendererInterface'    => 'Laminas\View\Renderer\PhpRenderer',
        ],
        'invokables' => [],
        'factories'  => [
            'Application'                    => ApplicationFactory::class,
            'Laminas\Config\Config'          => 'Obullo\Factory\ConfigFactory',
            'DatabaseLogger'                 => 'Obullo\Factory\DatabaseLoggerFactory',
            'SQLDevelopmentLogger'           => 'Obullo\Factory\SQLDevelopmentLoggerFactory',
            'ViewHelperManager'              => 'Obullo\Factory\ViewHelperManagerFactory',
            'Laminas\View\ViewHelperManagerFactory' => 'Obullo\Factory\ViewHelperManagerFactory',
            'Laminas\View\Renderer\PhpRenderer' => 'Obullo\Factory\ViewPhpRendererFactory',
            'Laminas\View\View'              => 'Obullo\Factory\ViewFactory',
            'Laminas\Escaper\Escaper'        => 'Obullo\Factory\EscaperFactory',
        ],
    ];
}
