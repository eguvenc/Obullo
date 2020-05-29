<?php

namespace Obullo;

use Obullo\Router\Router;
use Obullo\Router\RouteInterface as Route;
use Laminas\EventManager\Event;
use Psr\Http\Message\RequestInterface as Request;
use Laminas\View\Model\ModelInterface as Model;
use Laminas\View\Model\ViewModel;

class PageEvent extends Event
{
    /**
     * Page events triggered by eventmanager
     */
    const EVENT_BOOTSTRAP   = 'bootstrap';
    const EVENT_ROUTE       = 'route.match';
    const EVENT_PAGE_VIEW   = 'page.view';
    const EVENT_PARTIAL_VIEW = 'partial.view';

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * @var Model
     */
    protected $viewModel;

    /**
     * @var Resolved module name
     */
    protected $moduleName;

    /**
     * Set application instance
     *
     * @param  ApplicationInterface $application
     * @return PageEvent
     */
    public function setApplication($application)
    {
        $this->setParam('application', $application);
        $this->application = $application;
        return $this;
    }

    /**
     * Get application instance
     *
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get router
     *
     * @return RouteStackInterface
     */
    public function getRouter() : Router
    {
        return $this->router;
    }

    /**
     * Set router
     *
     * @param Router $router
     * @return PageEvent
     */
    public function setRouter(Router $router)
    {
        $this->setParam('router', $router);
        $this->router = $router;
        return $this;
    }

    /**
     * Get route match
     *
     * @return null|RouteMatch
     */
    public function getMatchedRoute()
    {
        return $this->route;
    }

    /**
     * Set matched route
     *
     * @param Route
     * @return PageEvent
     */
    public function setMatchedRoute(Route $route)
    {
        $this->setParam('route-match', $route->getArguments());
        $this->route = $route;
        return $this;
    }

    /**
     * Set handler class
     * 
     * @param string $handler class name
     */
    public function setHandler($handler)
    {
        $this->setParam('handler', $handler);
        $this->handler = $handler;
        return $this;
    }

    /**
     * Returns to handler
     * 
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }
    
    /**
     * Set resolved http module name
     * 
     * @param string $handler class name
     */
    public function setResolvedModuleName()
    {
        $moduleName = explode('\\', $this->handler);  // App, Blog, Forum etc..
        $this->setParam('module-name', $moduleName[0]);
        $this->moduleName = $moduleName[0];
        return $this;
    }

    /**
     * Returns to resolved module name
     * 
     * @return string
     */
    public function getResolvedModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set request
     *
     * @param Request $request
     * @return PageEvent
     */
    public function setRequest(Request $request)
    {
        $this->setParam('request', $request);
        $this->request = $request;
        return $this;
    }

    /**
     * Get response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response
     *
     * @param Response $response
     * @return PageEvent
     */
    public function setResponse(Response $response)
    {
        $this->setParam('response', $response);
        $this->response = $response;
        return $this;
    }

    /**
     * Set the view model
     *
     * @param  Model $viewModel
     * @return PageEvent
     */
    public function setViewModel(Model $viewModel)
    {
        $this->viewModel = $viewModel;
        return $this;
    }

    /**
     * Get the view model
     *
     * @return Model
     */
    public function getViewModel()
    {
        if (null === $this->viewModel) {
            $this->setViewModel(new ViewModel());
        }
        return $this->viewModel;
    }

    /**
     * Get result
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set result
     *
     * @param mixed $result
     * @return PageEvent
     */
    public function setResult($result)
    {
        $this->setParam('__RESULT__', $result);
        $this->result = $result;
        return $this;
    }

    /**
     * Does the event represent an error response?
     *
     * @return bool
     */
    public function isError()
    {
        return (bool) $this->getParam('error', false);
    }

    /**
     * Set the error message (indicating error in handling request)
     *
     * @param  string $message
     * @return PageEvent
     */
    public function setError($message)
    {
        $this->setParam('error', $message);
        return $this;
    }

    /**
     * Retrieve the error message, if any
     *
     * @return string
     */
    public function getError()
    {
        return $this->getParam('error', '');
    }
}
