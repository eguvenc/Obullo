<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Obullo\Router\Router;
use Zend\Diactoros\Response\TextResponse;

class HttpMethodMiddleware implements MiddlewareInterface
{
	protected $router;

    /**
     * Constructor
     * 
     * @param Router     $router     router
     * @param Translator $translator translator
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
    	$allowedMethods = $this->router->getMatchedRoute()
    		->getMethods();

    	if (! in_array($request->getMethod(), $allowedMethods)) {
		    $message = sprintf(
		        'Only Http %s Methods Allowed',
		        implode(', ', $allowedMethods)
		    );
		    return new TextResponse($message, 405);
		}
		return $handler->handle($request);
    }
}