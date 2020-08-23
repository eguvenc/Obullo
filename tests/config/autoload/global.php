<?php

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'root' =>  dirname(dirname(__DIR__)),
    'view_manager' => [
        'display_exceptions' => true,
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'App\Pages\WelcomeModel',
                    ],
                ],
            ],
            'test' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test',
                    'defaults' => [
                        'controller' => 'App\Pages\TestModel',
                        'middleware' => 'App\Middleware\AuthMiddleware@onPost'
                    ],
                ],
            ],
            'test_args_id' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_args/:id',
                    'defaults' => [
                        'controller' => 'App\Pages\TestArgsModel',
                    ],
                ],
            ],
            'test_args_number' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_args/:id/:number',
                    'defaults' => [
                        'controller' => 'App\Pages\TestArgsModel',
                    ],
                ],
            ],
            'test_error' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_error',
                    'defaults' => [
                        'controller' => 'App\Pages\TestErrorModel',
                    ],
                ],
            ],
            'test_view' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_view',
                    'defaults' => [
                        'controller' => 'App\Pages\TestViewModel',
                    ],
                ],
            ],
            'test_partial_view' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_partial_view',
                    'defaults' => [
                        'controller' => 'App\Pages\TestPartialViewModel',
                    ],
                ],
            ],
            'test_multi' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_multi',
                    'defaults' => [
                        'controller' => 'App\Pages\TestModel',
                        'middleware' => 'App\Middleware\AuthMiddleware@onGet|onPost'
                    ],
                ],
            ],
            'test_plugin' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_plugin',
                    'defaults' => [
                        'controller' => 'App\Pages\PluginModel',
                    ],
                ],
            ],
            'test_url_helper' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_url_helper',
                    'defaults' => [
                        'controller' => 'App\Pages\UrlModel',
                    ],
                ],
            ],
            'test_set_locale' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/test_set_locale',
                    'defaults' => [
                        'controller' => 'App\Pages\SetLocaleModel',
                        'middleware' => 'Obullo\Middleware\SetLocaleMiddleware'
                    ],
                ],
            ],

        ], // end routes
    ], // end router

];
