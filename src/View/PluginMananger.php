<?php

namespace Obullo\View;

use LogicException;
use Psr\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorInterface;

class PluginManager extends AbstractPluginManager
{
    /**
     * Constructor
     *
     * Merges provided configuration with default configuration.
     *
     * Adds initializers to inject the attached translator, if
     * any, to the currently requested helper.
     *
     * @param null|ConfigInterface|ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        $this->initializers[] = [$this, 'injectTranslator'];

        parent::__construct($configOrContainerInstance, $v3config);
    }

    /**
     * Inject a helper instance with the registered translator
     *
     * @param ContainerInterface|Helper\HelperInterface $first helper instance
     *     under zend-servicemanager v2, ContainerInterface under v3.
     * @param ContainerInterface|Helper\HelperInterface $second
     *     ContainerInterface under zend-servicemanager v3, helper instance
     *     under v2. Ignored regardless.
     */
    public function injectTranslator($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3 usage
            $container = $first;
            $helper = $second;
        }
        // Allow either direct implementation or duck-typing.
        if (! $helper instanceof TranslatorAwareInterface
            && ! method_exists($helper, 'setTranslator')
        ) {
            return;
        }
        if (! $container) {
            // Under zend-navigation v2.5, the navigation PluginManager is
            // always lazy-loaded, which means it never has a parent
            // container.
            return;
        }
        if (method_exists($helper, 'hasTranslator') && $helper->hasTranslator()) {
            return;
        }
        if ($container->has(TranslatorInterface::class)) {
            $helper->setTranslator($container->get(TranslatorInterface::class));
            return;
        }
        if ($container->has('translator')) {
            $helper->setTranslator($container->get('translator'));
            return;
        }
    }

    /**
     * Call page plugins
     *
     * @param  string $plugin     method name
     * @param  array  $parameters method parameters
     * @return mixed
     */
    public function __call(string $plugin, $parameters = array())
    {
        if (false == isset($this->aliases[$plugin]) && false == isset($this->factories[$plugin])) {
            throw new LogicException(
                sprintf("The plugin %s not defined in %s.", $plugin, __CLASS__)
            );
        }
        $callable = $this->get($plugin);
        return $callable(...$parameters);
    }
}