<?php

use Obullo\Router\Types\{
    IntType,
    StrType,
    SlugType,
    AnyType,
    BoolType,
    TranslationType
};
use Symfony\Component\Yaml\Yaml;

return [
    'root' =>  dirname(dirname(__DIR__)),

    'view_manager' => [
        'display_exceptions' => true,
    ],
    'router' => [
        // Available global route types
        'types' => [
            new IntType('<int:id>'),  // \d+
            new IntType('<int:number>'),
            new StrType('<str:name>'),     // \w+
            new StrType('<str:word>'),     // \w+
            new AnyType('<any:any>'),
            new BoolType('<bool:status>'),
            new IntType('<int:page>'),
            new SlugType('<slug:slug>'),
            new TranslationType('<locale:locale>')
        ],
        'routes' => Yaml::parseFile(__DIR__.'/../routes.yaml'),
        'translatable_routes' => false,
    ],
];
