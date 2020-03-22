<?php

namespace Obullo\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Obullo\Router\Router;

class Url extends AbstractHelper
{
    /**
     * @var object
     */
    protected $router;
    
    /**
     * Set router
     *
     * @param Router $router router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }
    
    /**
     * Generate url
     *
     * @param  string $url    url
     * @param  array  $params parameters
     * @param  string $locale locale
     *
     * @return string url
     */
    public function __invoke(string $url, $params = [], $locale = null)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme != null) {
            return $url;
        }
        return $this->router->url($url, $params, $locale);
    }
}
