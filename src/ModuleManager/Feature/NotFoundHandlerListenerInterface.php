<?php

namespace Obullo\ModuleManager\Feature;

use Laminas\EventManager\EventInterface;

/**
 * Not found handler listener provider interface
 */
interface NotFoundHandlerListenerInterface
{
    /**
     * Listen to the not found handler event
     *
     * @param EventInterface $e
     * @return void
     */
    public function onNotFoundHandler(EventInterface $e);
}
