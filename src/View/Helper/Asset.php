<?php

namespace Obullo\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Obullo\View\Exception\AssetNotFoundException;

class Asset extends AbstractHelper
{
    /**
     * @var string
     */
    protected $path;

    /**
     * Set base path
     *
     * @param string $path base path
     */
    public function setPath(string $path)
    {
        $this->path = rtrim($path, '/');
    }

    /**
     * Returns to asset path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Create asset url
     *
     * @param  string  $url       url
     * @param  boolean $timestamp timestamp
     * @return
     */
    public function __invoke(string $url, $timestamp = true)
    {
        $filePath = $this->getPath() . '/' .  ltrim($url, '/');

        if (false == file_exists($filePath)) {
            throw new AssetNotFoundException(
                'Unable to locate "' . ltrim($url, '/') . '" in the asset directory.'
            );
        }
        $lastUpdated = filemtime($filePath);
        $pathInfo = pathinfo($url);
        if ($pathInfo['dirname'] === '.') {
            $directory = '';
        } elseif ($pathInfo['dirname'] === '/' || $pathInfo['dirname'] === '\\') {
            $directory = '/';
        } else {
            $directory = $pathInfo['dirname'] . '/';
        }
        $url = $directory . $pathInfo['filename'] . '.' . $pathInfo['extension'];
        if ($timestamp) {
            $url.='?v=' . $lastUpdated;
        }
        return $url;
    }
}
