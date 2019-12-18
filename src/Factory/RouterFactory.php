<?php

namespace Obullo\Factory;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;
use Obullo\Http\ServerRequest;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\I18n\Translator\Translator;
use Obullo\Router\{
    Pattern,
    RouteCollection,
    RequestContext,
    Builder,
    Router
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
        $config = $container->get(Config::class)->toArray();

        $pattern = new Pattern($config['route_types']);
        $context = new RequestContext;
        $context->fromRequest($container->get(ServerRequest::class));
         
        $collection = new RouteCollection($pattern);
        $collection->setContext($context);

        $builder = new Builder($collection);
        $collection = $builder->build($config['routes']);
        
        return new Router($collection);
    }
}