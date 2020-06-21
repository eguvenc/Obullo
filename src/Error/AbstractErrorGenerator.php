<?php

namespace Obullo\Error;

use Throwable;
use ReflectionClass;
use Obullo\PageEvent;
use Laminas\View\View;
use Laminas\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

abstract class AbstractErrorGenerator
{
    /**
     * @var Obullo\PageEvent
     */
    private $event;

    /**
     * @var Laminas\ServiceManager\ServiceManager
     */
    private $container;

    /**
     * @var boolean
     */
    private $isDevelopmentMode = true;

    /**
     * @var Exception
     */
    private $exception;

    /**
     * Container
     *
     * @param ContainerInterface $container container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->setDevelopmentMode();
    }

    /**
     * Returns to Laminas\ServiceManager\ServiceManager
     *
     * @return object
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set development mode
     */
    public function setDevelopmentMode()
    {
        $config = $this->container->get('config');
        $this->isDevelopmentMode = empty($config['view_manager']['display_exceptions']) ? false : true;
    }

    /**
     * Returns to development mode
     *
     * @return boolean
     */
    public function getDevelopmentMode()
    {
        return $this->isDevelopmentMode;
    }

    /**
     * Returns to module name
     *
     * Returns to 'App' by default for more friendly and testable codes.
     * 
     * @return string
     */
    public function getModuleName()
    {
        $reflection = new ReflectionClass($this);
        $module = strstr($reflection->getNamespaceName(), '\\', true);

        return ($module == false) ? 'App' : $module;
    }

    /**
     * Returns to the view model to render error templates
     *
     * @return object
     */
    public function getViewModel()
    {
        $viewModel = new ViewModel;
        $viewModel->exception = $this->event->getParam('exception');
        $viewModel->isDevelopmentMode = $this->getDevelopmentMode();
        $viewModel->setTemplate($this->event->getParam('error_template'));
        $viewModel->setOption('has_parent', true);

        return $viewModel;
    }

    /**
     * Render html view
     *
     * @param  ViewModel $viewModel view model
     * @return string
     */
    public function render(ViewModel $viewModel)
    {
        $view = $this->container->get(View::class);

        return $view->render($viewModel);
    }

    /**
     * Trigger PageEvent::EVENT_NOT_FOUND
     * 
     * @param  string $error optional error parameter
     * @return void
     */
    public function trigger404Event($error = 'Page Not Found')
    {
        $module = $this->getModuleName();

        $this->event = $this->container->get('Application')->getPageEvent();
        $this->event->setName(PageEvent::EVENT_NOT_FOUND);
        $this->event->setParam('error', $error);
        $this->event->setParam('error_template', $module.'\Pages\Templates\ErrorNotFound');

        $this->container->get('EventManager')->triggerEvent($this->event);
    }

    /**
     * Trigger PageEvent::EVENT_EXCEPTION_ERROR
     * 
     * @param  object $exception optional parameter
     * @return void
     */
    public function triggerErrorEvent(Throwable $exception = null)
    {
        $module = $this->getModuleName();

        $this->event = $this->container->get('Application')->getPageEvent();
        $this->event->setName(PageEvent::EVENT_EXCEPTION_ERROR);
        $this->event->setParam('exception', $exception);
        $this->event->setParam('error_template', $module.'\Pages\Templates\ErrorsAndExceptions');

        $this->container->get('EventManager')->triggerEvent($this->event);
    }

    /**
     * Returns to PageEvent
     *
     * @return object
     */
    public function getPageEvent()
    {
        return $this->event;
    }
}
