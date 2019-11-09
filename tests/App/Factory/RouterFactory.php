<?php

namespace App\Factory;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\I18n\Translator\Translator;
use Obullo\Router\{
    Pattern,
    RouteCollection,
    RequestContext,
    Builder,
    Router
};
use Obullo\Router\Types\{
    StrType,
    IntType,
    TranslationType
};
class RouterFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $pattern = new Pattern([
            new IntType('<int:id>'),
            new IntType('<int:page>'),
            new StrType('<str:name>'),
            new TranslationType('<locale:locale>'),
        ]);
        $context = new RequestContext;
        $context->fromRequest($container->get('request'));

        $collection = new RouteCollection($pattern);
        $collection->setContext($context);

        $builder = new Builder($collection);
        $routes  = [
            '/test' => [
                'handler'=> 'TestModel',
            ],
            'plugin/test/' => [
                'handler'=> 'plugin_test.phtml',
            ],
        ];
        $collection = $builder->build($routes);
        return new Router($collection);
    }
}
