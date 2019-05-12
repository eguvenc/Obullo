<?php

namespace Obullo\Pages\Plugin;

use LogicException;

/**
 * Extension that adds the ability to create "cache busted" asset URLs.
 */
class Asset
{
    /**
     * Path to asset directory.
     * @var string
     */
    public $path;

    /**
     * Enables the filename method.
     * @var boolean
     */
    public $filenameMethod;

    /**
     * Create new Asset instance.
     *
     * @param string  $path
     * @param boolean $filenameMethod
     */
    public function __construct($path, $filenameMethod = false)
    {
        $this->path = rtrim($path, '/');
        $this->filenameMethod = $filenameMethod;
    }

    /**
     * Create "cache busted" asset URL.
     * @param  string $url
     * @return string
     */
    public function __invoke($url)
    {
        $filePath = $this->path . '/' .  ltrim($url, '/');

        if (!file_exists($filePath)) {
            throw new LogicException(
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
        if ($this->filenameMethod) {
            return $directory . $pathInfo['filename'] . '.' . $lastUpdated . '.' . $pathInfo['extension'];
        }
        
        return $directory . $pathInfo['filename'] . '.' . $pathInfo['extension'] . '?v=' . $lastUpdated;
    }
}
