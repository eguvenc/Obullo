<?php

namespace Obullo\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Exception;

/**
 * View helper plugin to fetch asset from asset directory.
 */
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
    public function setPath($path)
    {
        $this->path = rtrim($path, '/');
    }

    /**
     * @param string $url
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke($url)
    {
        $filePath = $this->path . '/' .  ltrim($url, '/');

        if (!file_exists($filePath)) {
            throw new Exception\RuntimeException(
                'Unable to locate the asset "' . $url . '" in the "' . $this->path . '" directory.'
            );
        }
        $lastUpdated = filemtime($filePath);
        $pathInfo = pathinfo($url);

        if ($pathInfo['dirname'] === '.') {
            $directory = '';
        } elseif ($pathInfo['dirname'] === '/') {
            $directory = '/';
        } else {
            $directory = $pathInfo['dirname'] . '/';
        }

        return $directory . $pathInfo['filename'] . '.' . $pathInfo['extension'] . '?v=' . $lastUpdated;
    }
}