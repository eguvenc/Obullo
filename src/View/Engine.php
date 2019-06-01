<?php

namespace Obullo\View;

use Obullo\View\Template\Directory;
use Obullo\View\Template\FileExtension;
use Obullo\View\Template\Folders;
use Obullo\View\Template\Name;

/**
 * Template and environment settings storage.
 */
class Engine
{
    /**
     * Configuration options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Default template directory.
     *
     * @var Directory
     */
    protected $directory;

    /**
     * Template file extension.
     *
     * @var FileExtension
     */
    protected $fileExtension;

    /**
     * Collection of template folders.
     *
     * @var Folders
     */
    protected $folders;

    /**
     * Create & configure engine instance
     *
     * @param array $options config
     */
    public function __construct($options = array())
    {
        $this->options = $options;

        $file_extension = isset($options['file_extension']) ? $options['file_extension'] : null;
        $default_directory = isset($options['default_directory']) ? $options['default_directory'] : null;

        $this->directory = new Directory($default_directory);
        $this->fileExtension = new FileExtension($file_extension);
        $this->folders = new Folders;
    }

    /**
     * Set path to templates directory.
     * @param  string|null $directory Pass null to disable the default directory.
     * @return Engine
     */
    public function setDirectory($directory)
    {
        $this->directory->set($directory);

        return $this;
    }

    /**
     * Get path to templates directory.
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory->get();
    }

    /**
     * Returns to configuration options
     *
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Set the template file extension.
     * @param  string|null $fileExtension Pass null to manually set it.
     * @return Engine
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension->set($fileExtension);

        return $this;
    }

    /**
     * Get the template file extension.
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension->get();
    }

    /**
     * Add a new template folder for grouping templates under different namespaces.
     *
     * @param  string  $name
     * @param  string  $directory
     * @param  boolean $fallback
     * @return Engine
     */
    public function addFolder($name, $directory, $fallback = false)
    {
        $this->folders->add($name, $directory, $fallback);

        return $this;
    }

    /**
     * Remove a template folder.
     * @param  string $name
     * @return Engine
     */
    public function removeFolder($name)
    {
        $this->folders->remove($name);

        return $this;
    }

    /**
     * Get collection of all template folders.
     *
     * @return Folders
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * Get a template path.
     *
     * @param  string $name
     * @return string
     */
    public function path($name)
    {
        $name = new Name($this, $name);

        return $name->getPath();
    }

    /**
     * Check if a template exists.
     *
     * @param  string  $name
     * @return boolean
     */
    public function exists($name)
    {
        $name = new Name($this, $name);

        return $name->doesPathExist();
    }
}
