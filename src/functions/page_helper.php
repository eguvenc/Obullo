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

    return $middlewares;
}
