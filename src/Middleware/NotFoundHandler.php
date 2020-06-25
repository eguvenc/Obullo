<?php

declare(strict_types=1);

namespace Obullo\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundHandler implements MiddlewareInterface
{
    /**
     * @var callable[]
     */
    private $listeners = [];

    /**
     * @var callable Routine that will generate the error response.
     */
    private $responseGenerator;

    /**
     * @var callable
     */
    private $responseFactory;

    /**
     * @param callable $responseFactory A factory capable of returning an
     *     empty ResponseInterface instance to update and return when returning
     *     an error response.
     * @param callable $responseGenerator Callback that will generate the final
     *     error response.
     */
    public function __construct(callable $responseFactory, callable $responseGenerator)
    {
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
        $this->responseGenerator = $responseGenerator;
    }

    /**
     * Attach a not found listener.
     *
     * Each listener receives the following two arguments:
     *
     * - ServerRequestInterface $request
     * - ResponseInterface $response
     *
     * These instances are all immutable, and the return values of
     * listeners are ignored; use listeners for reporting purposes
     * only.
     */
    public function attachListener(callable $listener) : void
    {
        if (in_array($listener, $this->listeners, true)) {
            return;
        }

        $this->listeners[] = $listener;
    }

    /**
     * Middleware to handle not found error.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $generator = $this->responseGenerator;
        $response = $generator($request, ($this->responseFactory)());
        $this->triggerListeners($request, $response);

        return $response;
    }

    /**
     * Trigger all error listeners.
     */
    private function triggerListeners(ServerRequestInterface $request, ResponseInterface $response) : void
    {
        foreach ($this->listeners as $listener) {
            $listener($request, $response);
        }
    }
}
