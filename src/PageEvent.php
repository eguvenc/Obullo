<?php

namespace Obullo;

use Obullo\Router\Router;
use Obullo\Router\RouteInterface as Route;
use Laminas\EventManager\Event;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class PageEvent extends Event
{
    /**
     * Page events triggered by eventmanager
     */
    const EVENT_BOOTSTRAP       = 'bootstrap';
    const EVENT_ROUTE           = 'route.match';
    const EVENT_MIDDLEWARES     = 'middlewares';
    const EVENT_ERROR_HANDLER   = 'error.handler';
    const EVENT_NOT_FOUND_HANDLER = 'notFound.handler';
    const EVENT_DISPATCH_PAGE   = 'dispatch.page';
    const EVENT_DISPATCH_PARTIAL_PAGE = 'dispatch.partial';

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var Resolved module name
     */
    protected $moduleName;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Model
     */
    protected $pageModel = array();

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
     * Set page model
     * 
     * @param string $handler page handler
     * @param $model object
     */
    public function setPageModel(string $handler, $model)
    {
        $this->pageModel[$handler] = $model;
        return $this;
    }

    /**
     * Returns to requested page model
     * 
     * @param  string $handler full class name of page model
     * @return object|false
     */
    public function getPageModel(string $handler)
    {
        return isset($this->pageModel[$handler]) ? $this->pageModel[$handler] : false;
    }
}