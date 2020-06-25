<?php

declare(strict_types=1);

namespace Test;

use Obullo\PageEvent;
use Laminas\Diactoros\Response;
use Laminas\ModuleManager\ModuleManager;

class Module
{
    public function getConfig() : array
    {
        return [
            'service_manager' => [],
        ];
    }

    public function onErrorHandler(PageEvent $e)
    {
        if ($e->getResolvedModuleName() == 'Test') {
            throw new \Exception('Fail');
        }
    }

    public function onNotFoundMiddleware(PageEvent $e)
    {
        if ($e->getResolvedModuleName() == 'Test') {
            throw new \Exception('Fail');
        }
    }
}
