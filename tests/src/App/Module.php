<?php

namespace App;

use Obullo\PageEvent;
use App\Middleware\ErrorNotFoundHandler;
use App\Middleware\ErrorResponseGenerator;
use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function getConfig() : array
    {
        return [
            'service_manager' => [],
            'error_handlers' => [
                __NAMESPACE__ => [
                    'error_404' => ErrorNotFoundHandler::class,
                    'error_generator' => ErrorResponseGenerator::class,
                ],
            ]
        ];
    }

    public function init(ModuleManager $moduleManager)
    {
        $events = $moduleManager->getEventManager();
        $sharedEvents = $events->getSharedManager();

        $sharedEvents->attach('Obullo\Application', 'test.init', function ($e) {
            return $e->getName();
        });
    }

    public function onBootstrap(PageEvent $e)
    {
        $application = $e->getApplication();
        $events = $application->getEventManager();

        $events->attach('test.onBootstrap', function ($e) {
            return $e->getName();
        });
    }
}
