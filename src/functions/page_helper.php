<?php

namespace Obullo\Functions;

/**
 * Parse route middlewares
 *
 * @param  object $router Router
 * @return array
 */
function parseMiddlewares($router)
{
    $middlewares = $router->getMiddlewares();
	$request = $router->getCollection()
		->getContext();

	$newMiddlewares = array();
	foreach ($middlewares as $middlewareString) {
		if (strpos($middlewareString, '@') > 0) {
			list($middleware, $methodString) = explode('@', $middlewareString);
			$middlewareMethods = explode('|', $methodString);
			$method = ucfirst(strtolower($request->getMethod()));
			if (in_array('on'.$method, $middlewareMethods)) {
				$newMiddlewares[] = $middleware;
			}
		} else {
			$newMiddlewares[] = $middlewareString;
		}
	}
    return $newMiddlewares;
}
