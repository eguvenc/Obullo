<?php

namespace Obullo\ModuleManager\Feature;

use Laminas\EventManager\EventInterface;

/**
 * Error handler listener provider interface
 */
interface ErrorHandlerListenerInterface
{
    /**
     * Listen to the error handler event
     *
     * @param EventInterface $e
     * @return void
     */
    public function onErrorHandler(EventInterface $e);
}
