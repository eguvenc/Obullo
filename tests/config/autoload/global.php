<?php

use Obullo\Router\Types\{
    IntType,
    StrType,
    SlugType,
    AnyType,
    BoolType,
    TranslationType
};
use Laminas\ServiceManager\Factory\InvokableFactory;
use Symfony\Component\Yaml\Yaml;

return [

    'view_manager' => [
        'display_exceptions' => true,
    ],

    'router' => [
        // Available global route types
        'types' => [
            new IntType('<int:id>'),  // \d+
            new StrType('<str:name>'),     // \w+
            new StrType('<str:word>'),     // \w+
            new AnyType('<any:any>'),
            new BoolType('<bool:status>'),
            new IntType('<int:page>'),
            new SlugType('<slug:slug>'),
            new TranslationType('<locale:locale>')
        ],
        'routes' => Yaml::parseFile(dirname(__DIR__).'/routes.yaml'),
        'translatable_routes' => false,
    ],

    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'service_manager' => [
        
        'initializers' => [],
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        //
        'aliases' => [
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        //
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
            // Helper\ServerUrlHelper::class => Helper\ServerUrlHelper::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        //
        'factories'  => [
        ],
        'abstract_factories' => [
            Obullo\Factory\LazyDefaultFactory::class,
        ],
    ],
];
