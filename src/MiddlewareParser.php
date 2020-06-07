<?php

namespace Obullo;

class MiddlewareParser
{
    /**
     * Parse route middlewares
     * 
     * @param  array  $middlewares array
     * @param  string $method      request method
     * @return array
     */
    public static function parse(array $middlewares, string $method)
    {
        $newMiddlewares = array();
        foreach ($middlewares as $middlewareString) {
            if (strpos($middlewareString, '@') > 0) {
                list($middleware, $methodString) = explode('@', $middlewareString);
                $middlewareMethods = explode('|', $methodString);
                $methodName = ucfirst(strtolower($method));
                if (in_array('on'.$methodName, $middlewareMethods)) {
                    $newMiddlewares[] = $middleware;
                }
            } else {
                $newMiddlewares[] = $middlewareString;
            }
        }
        return $newMiddlewares;
    }
}
