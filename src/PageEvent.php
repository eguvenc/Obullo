<?php

namespace Obullo;

use Laminas\EventManager\Event;
use Laminas\Router\RouteStackInterface;
use Laminas\Router\RouteMatch;
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
     * @var object Application
     */
    protected $application;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var object Router
     */
    protected $router;

    /**
     * @var object RouteMatch
     */
    protected $routeMatch;

    /**
     * @var string resolved module name
     */
    protected $moduleName;

    /**
     * @var object Response
     */
    protected $response;

    /**
     * @var array model
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
    public function getRouter() : RouteStackInterface
    {
        return $this->router;
    }

    /**
     * Set router
     *
     * @param RouteStackInterface $router
     * @return MvcEvent
     */
    public function setRouter(RouteStackInterface $router)
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
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * Set RouteMatch
     *
     * @param RouteMatch matched route
     * @return PageEvent
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->setParam('route-match', $routeMatch);
        $this->routeMatch = $routeMatch;
        return $this;
    }

    /**
     * Get the currently registered controller name
     *
     * @return string
     */
    public function getController()
    {
        return $this->getParam('controller');
    }

    /**
     * Set controller name
     *
     * @param  string $name
     * @return MvcEvent
     */
    public function setController($name)
    {
        $this->setParam('controller', $name);
        return $this;
    }

    /**
     * Set resolved http module name
     *
     * @param string $handler class name
     */
    public function setResolvedModuleName()
    {
        $moduleName = explode('\\', $this->getController());  // App, Blog, Forum etc..
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
     * @param string $name page handler
     * @param $model object
     */
    public function setPageModel(string $name, $model)
    {
        $this->pageModel[$name] = $model;
        return $this;
    }

    /**
     * Returns to requested page model
     *
     * @param  string $name full class name of page model
     * @return object|false
     */
    public function getPageModel(string $name)
    {
        return isset($this->pageModel[$name]) ? $this->pageModel[$name] : false;
    }
}
