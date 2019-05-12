<?php

namespace Obullo\Pages\Plugin;

use Obullo\Router\Router;

/**
 * Plugin for generate secure url
 */
class Url
{
    protected $router;

    /**
     * Constructor
     *
     * @param Router $router router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Invoke
     *
     * @param  string $url    string
     * @param  array  $params array
     *
     * @return mixed
     */
    public function __invoke(string $url, $params = [])
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme != null) {
            return $url;
        }
        return $this->router->url($url, $params);
    }
}
