<?php

namespace Obullo\ModuleManager\Listener;

use Obullo\PageEvent;
use Obullo\ModuleManager\Feature\ErrorHandlerListenerInterface;

use Laminas\ModuleManager\Listener\AbstractListener;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;

class OnErrorHandlerListener extends AbstractListener
{
    /**
     * @param  ModuleEvent $e
     * @return void
     */
    public function __invoke(ModuleEvent $e)
    {
        $module = $e->getModule();

        if (! $module instanceof ErrorHandlerListenerInterface
            && ! method_exists($module, 'onErrorHandler')
        ) {
            return;
        }
        $moduleManager = $e->getTarget();
        $events        = $moduleManager->getEventManager();
        $sharedEvents  = $events->getSharedManager();
        $sharedEvents->attach('Obullo\Application', PageEvent::EVENT_ERROR_HANDLER, [$module, 'onErrorHandler']);
    }
}
