<?php

namespace Obullo\Factory;

use Obullo\Router\Router;
use Interop\Container\ContainerInterface;
use Obullo\View\Helper as ObulloViewHelper;
use Laminas\View\Helper as LaminasViewHelper;
use Laminas\View\HelperPluginManager;

class ViewHelperManagerFactory
{
    /**
     * Create and return the view helper manager
     *
     * @param  ContainerInterface $container
     * @return HelperPluginManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $options ?: [];
        $options['factories'] = isset($options['factories']) ? $options['factories'] : [];
        $plugins = new HelperPluginManager($container, $options);

        // Override plugin factories
        // 
        $plugins = $this->injectOverrideFactories($plugins, $container);
    
        return $plugins;
    }

    /**
     * Inject override factories into the plugin manager.
     *
     * @param HelperPluginManager $plugins
     * @param ContainerInterface $services
     * @return HelperPluginManager
     */
    private function injectOverrideFactories(HelperPluginManager $plugins, ContainerInterface $services)
    {
        // Configure to override URL view helper
        $urlFactory = $this->createUrlHelperFactory($services);
        $plugins->setFactory(ObulloViewHelper\Url::class, $urlFactory);
        $plugins->setFactory('laminasviewhelperurl', $urlFactory);
        $plugins->setAlias('url', ObulloViewHelper\Url::class);

        // Configure to override asset view helper
        $assetFactory = $this->createAssetHelperFactory($services);
        $plugins->setFactory(ObulloViewHelper\Asset::class, $assetFactory);
        $plugins->setFactory('laminasviewhelperasset', $assetFactory);
        $plugins->setAlias('asset', ObulloViewHelper\Asset::class);

        // Configure model view helper
        $modelFactory = $this->createModelHelperFactory($services);
        $plugins->setFactory(ObulloViewHelper\Model::class, $modelFactory);
        $plugins->setAlias('model', ObulloViewHelper\Model::class);

        // Configure base path helper
        $basePathFactory = $this->createBasePathHelperFactory($services);
        $plugins->setFactory(LaminasViewHelper\BasePath::class, $basePathFactory);
        $plugins->setFactory('laminasviewhelperbasepath', $basePathFactory);

        // Configure doctype view helper
        $doctypeFactory = $this->createDoctypeHelperFactory($services);
        $plugins->setFactory(LaminasViewHelper\Doctype::class, $doctypeFactory);
        $plugins->setFactory('laminasviewhelperdoctype', $doctypeFactory);

        return $plugins;
    }

    /**
     * Create and return a factory for creating a URL helper.
     *
     * Retrieves the application and router from the servicemanager,
     * and the route match from the PageEvent composed by the application,
     * using them to configure the helper.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createUrlHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $url = new ObulloViewHelper\Url;
            $url->setRouter($services->get(Router::class));
            return $url;
        };
    }

    /**
     * Create and return a factory for creating an asset helper.
     *
     * Creates asset helper to manage application assets.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createAssetHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $config = $services->get('config');
            $asset = new ObulloViewHelper\Asset;
            $asset->setPath($config['root'].'/public/');
            return $asset;
        };
    }

    /**
     * Create and return a factory for creating a Model helper.
     *
     * Retrieves the application and router from the servicemanager,
     * and the route match from the PageEvent composed by the application,
     * using them to configure the helper.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createModelHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $model = new ObulloViewHelper\Model;
            $model->setContainer($services);
            return $model;
        };
    }


    /**
     * Create and return a factory for creating a BasePath helper.
     *
     * Uses configuration and request services to configure the helper.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createBasePathHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $config = $services->has('config') ? $services->get('config') : [];
            $helper = new LaminasViewHelper\BasePath;
            if (isset($config['view_manager']) && isset($config['view_manager']['base_path'])) {
                $helper->setBasePath($config['view_manager']['base_path']);
                return $helper;
            }
            return $helper;
        };
    }

    /**
     * Create and return a Doctype helper factory.
     *
     * Other view helpers depend on this to decide which spec to generate their tags
     * based on. This is why it must be set early instead of later in the layout phtml.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createDoctypeHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $config = $services->has('config') ? $services->get('config') : [];
            $config = isset($config['view_manager']) ? $config['view_manager'] : [];
            $helper = new LaminasViewHelper\Doctype;
            if (isset($config['doctype']) && $config['doctype']) {
                $helper->setDoctype($config['doctype']);
            }
            return $helper;
        };
    }
}
