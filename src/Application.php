<?php

namespace Obullo;

use Psr\Http\Message\ServerRequestInterface as Request;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Laminas\Diactoros\Response;

use Throwable;
use Obullo\Router\Router;
use Obullo\Router\RouteInterface;
use Obullo\Middleware\PageHandlerMiddleware;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Stratigility\MiddlewarePipeInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;

/**
 * Main application class for invoking applications
 *
 * Expects the user will provide a configured ServiceManager, configured with
 * the following services:
 *
 * - EventManager
 * - ModuleManager
 * - Request
 * - Response
 * - RouteListener
 * - Router
 * - DispatchListener
 *
 * The most common workflow is:
 * <code>
 * $application = $container->get('Application');
 * $application->bootstrap(array $listeners);
 * $response = $app->run();
 * $response->send();
 * </code>
 *
 * bootstrap() opts in to the default route, dispatch, and view listeners,
 * sets up the PageEvent, and triggers the bootstrap event. This can be omitted
 * if you wish to setup your own listeners and/or workflow; alternately, you
 * can simply extend the class to override such behavior.
 */
class Application
{
    /**
     * Default application event listeners
     *
     * @var array
     */
    protected $defaultListeners = [
        'RouteListener',
        'DispatchListener',
    ];

    /**
     * @var MiddlewarePipe
     */
    protected $app;

    /**
     * @var app config array
     */
    protected $appConfig;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var PageEvent
     */
    protected $event;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Constructor
     *
     * @param ServiceManager        $serviceManager container
     * @param EventManagerInterface $events         events
     * @param Request               $request        request
     * @param Router                $router         router
     */
    public function __construct(
        ServiceManager $serviceManager,
        EventManagerInterface $events,
        Request $request,
        Router $router
    ) {
        $this->serviceManager = $serviceManager;
        $this->setEventManager($events);
        $this->router = $router;
        $this->request = $request;
        $this->event = new PageEvent;
        $this->app = new MiddlewarePipe;
        $this->appConfig = $serviceManager->get('appConfig');
    }

    /**
     * Retrieve the application configuration
     *
     * @return array|object
     */
    public function getConfig()
    {
        return $this->serviceManager->get('config');
    }

    /**
     * Bootstrap app and attach listeners
     *
     * @param array $listeners List of listeners to attach.
     * @return void
     */
    public function bootstrap(array $listeners = [])
    {
        $this->event->setTarget($this);
        $this->event->setApplication($this);
        $this->event->setRequest($this->request);
        $this->event->setRouter($this->router);
        $this->event->setName(PageEvent::EVENT_ROUTE);
        $this->event->stopPropagation(false); // Clear before triggering

        // setup default listeners
        //
        $listeners = array_unique(array_merge($this->defaultListeners, $listeners));

        foreach ($listeners as $listener) {
            $this->serviceManager->get($listener)->attach($this->events);
        }
        // trigger route event
        //
        $result = $this->events->triggerEvent($this->event);
        $eventRouteResponse = $result->last();

        $moduleName = $this->event->getResolvedModuleName(); // App, Blog, Forum etc..
        $config = $this->getConfig();

        $errorGeneratorClass = 'App\Middleware\ErrorResponseGenerator';
        if (! empty($config['error_handlers'][$moduleName]['error_generator'])) {
            $errorGeneratorClass = $config['error_handlers'][$moduleName]['error_generator'];
        }
        $errorNotFoundHandler = 'App\Middleware\ErrorNotFoundHandler';
        if (! empty($config['error_handlers'][$moduleName]['error_404'])) {
            $errorNotFoundHandler = $config['error_handlers'][$moduleName]['error_404'];
        }
        // set error handler
        //
        $errorHandler = new ErrorHandler(
            function () {
                return new Response;
            },
            new $errorGeneratorClass($this->serviceManager)
        );
        $this->app->pipe($errorHandler);

        // if we have route match
        //
        if ($eventRouteResponse instanceof RouteInterface) {
            foreach ((array)$this->appConfig['middlewares'] as $appMiddleware) {
                $this->app->pipe($this->serviceManager->build($appMiddleware));
            }
            $routeMiddlewares = $this->parseRouteMiddlewares();
            foreach ($routeMiddlewares as $routeMiddleware) {
                $this->app->pipe($this->serviceManager->build($routeMiddleware));
            }
            $this->app->pipe($this->serviceManager->get(PageHandlerMiddleware::class));
        }

        // set 404 not found handler
        //
        $this->app->pipe(new $errorNotFoundHandler($this->serviceManager));

        // set bootstrap event
        //
        $this->event->setName(PageEvent::EVENT_BOOTSTRAP);

        // trigger bootstrap event
        //
        $this->events->triggerEvent($this->event);
        return $this;
    }

    /**
     * Parse route middlewares
     *
     * @param  object $router Router
     * @return array
     */
    private function parseRouteMiddlewares()
    {
        $middlewares = $this->router->getMiddlewares();
        $newMiddlewares = array();
        foreach ($middlewares as $middlewareString) {
            if (strpos($middlewareString, '@') > 0) {
                list($middleware, $methodString) = explode('@', $middlewareString);
                $middlewareMethods = explode('|', $methodString);
                $method = ucfirst(strtolower($this->request->getMethod()));
                if (in_array('on'.$method, $middlewareMethods)) {
                    $newMiddlewares[] = $middleware;
                }
            } else {
                $newMiddlewares[] = $middlewareString;
            }
        }
        return $newMiddlewares;
    }

    /**
     * Retrieve the service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Alias of the service manager
     *
     * @return ServiceManager
     */
    public function getContainer()
    {
        return $this->getServiceManager();
    }

    /**
     * Set the event manager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return Application
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        // To works with shared events we need to set identifiers, otherwise
        // we have to enable all shared events from Obullo\Container\ServiceManagerConfig file
        // using "'shared' => ['EventManager' => true]" option.

        $eventManager->setIdentifiers([
            __CLASS__,
            get_class($this),
        ]);
        $this->events = $eventManager;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Get the Page event instance
     *
     * @return PageEvent
     */
    public function getPageEvent()
    {
        return $this->event;
    }

    /**
     * Run the application
     */
    public function run()
    {
        $server = new RequestHandlerRunner(
            $this->app,
            new SapiEmitter(),
            function () {
                return $this->serviceManager->get('Request');
            },
            static function (Throwable $e) {
                $response = (new ResponseFactory())->createResponse(500);
                $response->getBody()->write(sprintf(
                    'An error occurred: %s',
                    $e->getMessage
                ));
                return $response;
            }
        );
        $server->run();
    }
}
