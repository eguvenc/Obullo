<?php

namespace Obullo\Factory;

use ReflectionClass;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class LazyPageFactory implements AbstractFactoryInterface
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
        return strstr($requestedName, 'Pages\\') !== false;
    }

    /**
     * These aliases work to substitute class names with Service Manager types that are buried in framework
     * 
     * @var array
     */
    protected $aliases = [
        'Zend\EventManager\EventManager' => 'events',
        'Zend\I18n\Translator\Translator' => 'translator',
        'Zend\I18n\Translator\TranslatorInterface' => 'translator'
    ];

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
        $pageModel = new $requestedName(...$injectedParameters);

        if (property_exists($pageModel, 'container')) {
            $pageModel->setContainer($container);
        }
        if (property_exists($pageModel, 'viewModel') && empty($pageModel->viewModel)) {

            $renderer = $container->get(RendererInterface::class);
            $plugin = $renderer->getHelperPluginManager();
            $callable = $plugin->get('model');
            $callable->setContainer($container);

            // $pageModel->viewModel = new ViewModel;
            // $pageModel->viewModel->setTemplate('Default.phtml'); // We need to understand this is a content model
        }
        return $pageModel;
    }
}