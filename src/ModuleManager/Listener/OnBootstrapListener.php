<?php

namespace Obullo\ModuleManager\Listener;

use Obullo\PageEvent;
use Laminas\ModuleManager\Listener\AbstractListener;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;

/**
 * Autoloader listener
 */
class OnBootstrapListener extends AbstractListener
{
    /**
     * @param  ModuleEvent $e
     * @return void
     */
    public function __invoke(ModuleEvent $e)
    {
        $module = $e->getModule();

        if (! $module instanceof BootstrapListenerInterface
            && ! method_exists($module, 'onBootstrap')
        ) {
            return;
        }
        $moduleManager = $e->getTarget();
        $events        = $moduleManager->getEventManager();
        $sharedEvents  = $events->getSharedManager();
        $sharedEvents->attach('Obullo\Application', PageEvent::EVENT_BOOTSTRAP, [$module, 'onBootstrap']);
    }
}
