<?php

namespace Obullo\View;

use Laminas\View\View;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Renderer\PhpRenderer;

abstract class AbstractPageView
{
    /**
     * Reserved for user view model
     * 
     * @var object
     */
    public $view;

    /**
     * Reserved for reflection class
     * 
     * @var object
     */
    public $reflection;

    /**
     * Reserved for laminas view
     * 
     * @var object
     */
    private $laminasView;

    /**
     * Reserved for php renderer
     * 
     * @var object
     */
    private $viewPhpRenderer;

    /**
     * Set view
     * 
     * @param View $view laminas view
     */
    public function setView(View $view)
    {
        $this->laminasView = $view;
    }

    /**
     * Returns to view
     * 
     * @return object laminas view
     */
    public function getView()
    {
        return $this->laminasView;
    }

    /**
     * Set php renderer
     * 
     * @param PhpRenderer $viewPhpRenderer php renderer
     */
    public function setViewPhpRenderer(PhpRenderer $viewPhpRenderer)
    {
        $this->viewPhpRenderer = $viewPhpRenderer;
    }

    /**
     * Returns to php renderer
     * 
     * @return object
     */
    public function getViewPhpRenderer()
    {
        return $this->viewPhpRenderer;
    }

    /**
     * Returns to reflection class
     * 
     * @return object
     */
    public function getReflectionClass()
    {
        return $this->reflection;
    }

    /**
     * A short function to reach view manager plugins
     * 
     * @param  string $name plugin name
     * @return callable
     */
    public function plugin(string $name)
    {
        $plugin = $this->getViewPhpRenderer()->getHelperPluginManager();

        return $plugin->get($name);
    }

    /**
     * Render partial view
     *
     * @param  string $name fully qualified partial view class name
     * @return mixed
     */
    public function model(string $name)
    {
        return $this->plugin('model')($name);
    }

    /**
     * Render view
     *
     * @param  ModelInterface $model model
     * @return string
     */
    abstract public function render(ModelInterface $model);
}