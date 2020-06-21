<?php

namespace Obullo\Test\PHPUnit\Pages;

use Laminas\Console\Console;
use Laminas\EventManager\StaticEventManager;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Http\ServerRequest;
use Obullo\Container\ServiceManagerConfig;
use Obullo\PageEvent;
use Laminas\Stdlib\Exception\LogicException;
use Laminas\Stdlib\ResponseInterface;
use Laminas\Test\PHPUnit\TestCaseTrait;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

abstract class AbstractPageTestCase extends TestCase
{
    use TestCaseTrait;

    /**
     * @var \Laminas\Mvc\ApplicationInterface
     */
    protected $application;

    /**
     * @var \Laminas\ServiceManager\ServiceManager
     */
    protected $container;

    /**
     * @var array
     */
    protected $applicationConfig;

    /**
     * Flag to use console router or not
     * @var bool
     */
    protected $useConsoleRequest = false;

    /**
     * Flag console used before tests
     * @var bool
     */
    protected $usedConsoleBackup;

    /**
     * Trace error when exception is throwed in application
     * @var bool
     */
    protected $traceError = true;

    /**
     * Reset the application for isolation
     *
     * @internal
     */
    protected function setUpCompat()
    {
        $this->usedConsoleBackup = Console::isConsole();
        $this->reset();
    }

    /**
     * Restore params
     *
     * @internal
     */
    protected function tearDownCompat()
    {
        Console::overrideIsConsole($this->usedConsoleBackup);

        // Prevent memory leak
        $this->reset();
    }

    /**
     * Create a failure message.
     *
     * If $traceError is true, appends exception details, if any.
     *
     * @param string $message
     * @return string
     */
    protected function createFailureMessage($message)
    {
        if (true !== $this->traceError) {
            return $message;
        }

        $exception = $this->getApplication()->getPageEvent()->getParam('exception');
        if (! $exception instanceof \Throwable && ! $exception instanceof \Exception) {
            return $message;
        }

        $messages = [];
        do {
            $messages[] = sprintf(
                "Exception '%s' with message '%s' in %s:%d",
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
        } while ($exception = $exception->getPrevious());

        return sprintf("%s\n\nExceptions raised:\n%s\n", $message, implode("\n\n", $messages));
    }

    /**
     * Get the trace error flag
     * @return bool
     */
    public function getTraceError()
    {
        return $this->traceError;
    }

    /**
     * Set the trace error flag
     * @param  bool                       $traceError
     * @return AbstractControllerTestCase
     */
    public function setTraceError($traceError)
    {
        $this->traceError = $traceError;

        return $this;
    }

    /**
     * Get the usage of the console router or not
     * @return bool $boolean
     */
    public function getUseConsoleRequest()
    {
        return $this->useConsoleRequest;
    }

    /**
     * Set the usage of the console router or not
     * @param  bool                       $boolean
     * @return AbstractControllerTestCase
     */
    public function setUseConsoleRequest($boolean)
    {
        $this->useConsoleRequest = (bool) $boolean;

        return $this;
    }

    /**
     * Get the application config
     * @return array the application config
     */
    public function getApplicationConfig()
    {
        return $this->applicationConfig;
    }

    /**
     * Set the application config
     * @param  array                      $applicationConfig
     * @return AbstractControllerTestCase
     * @throws LogicException
     */
    public function setApplicationConfig($applicationConfig)
    {
        if (null !== $this->application && null !== $this->applicationConfig) {
            throw new LogicException(
                'Application config can not be set, the application is already built'
            );
        }

        // do not cache module config on testing environment
        if (isset($applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        $this->applicationConfig = $applicationConfig;

        return $this;
    }

    /**
     * Get the service manager
     * @return ServiceManager
     */
    public function getContainer()
    {
        if ($this->container) {
            return $this->container;
        }
        $appConfig = $this->applicationConfig;

        $smConfig = isset($appConfig['service_manager']) ? $appConfig['service_manager'] : [];
        $smConfig = new ServiceManagerConfig($smConfig);

        // setup service manager
        $this->container = new ServiceManager();
        $smConfig->configureServiceManager($this->container);
        $this->container->setService('appConfig', $appConfig);
        $this->container->setAllowOverride(true);

        return $this->container;
    }

    /**
     * Get the application object
     * @return Obullo Application
     */
    public function getApplication()
    {
        if ($this->application) {
            return $this->application;
        }
        Console::overrideIsConsole($this->getUseConsoleRequest());
        $serviceManager = $this->getContainer();

        // load modules -- which will provide services, configuration, and more
        $serviceManager->get('ModuleManager')->loadModules();

        // bootstrap and run application
        $this->application = $serviceManager->get('Application');
        $this->application->bootstrap();

        return $this->application;
    }

    /**
     * Get the service manager of the application object
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getApplicationServiceLocator()
    {
        return $this->getApplication()->getServiceManager();
    }

    /**
     * Get the application request object
     * @return \Laminas\Stdlib\RequestInterface
     */
    public function getRequest()
    {
        return $this->getApplication()->getRequest();
    }

    /**
     * Get the application response object
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->getApplication()->getMvcEvent()->getResponse();
    }

    /**
     * Set the request URL
     *
     * @param  string                     $url
     * @param  string|null                $method
     * @param  array|null                 $params
     * @return AbstractControllerTestCase
     */
    public function url($url, $method = ServerRequest::METHOD_GET, $params = [], $isXmlHttpRequest = false)
    {
        if ($this->useConsoleRequest) {
            preg_match_all('/(--\S+[= ]"[^\s"]*\s*[^\s"]*")|(\S+)/', $url, $matches);
            $params = str_replace([' "', '"'], ['=', ''], $matches[0]);
            $request = new ServerRequest(
                $serverParams = [],
                $uploadedFiles = [],
                new Uri($url),
                $method,
                $body = 'php://input',
                $headers = [],
                $cookies = [],
                $queryParams = $params,
                $parsedBody = [],
                $protocol = '1.1'
            );
            $this->getContainer()->setService('Request', $request);
            return $this;
        }
        switch ($method) {
            case ServerRequest::METHOD_POST:
                $request = new ServerRequest(
                    $serverParams = [],
                    $uploadedFiles = [],
                    new Uri($url),
                    $method,
                    $body = 'php://input',
                    $headers = [],
                    $cookies = [],
                    $queryParams = [],
                    $parsedBody = $params,
                    $protocol = '1.1'
                );
                break;
            case ServerRequest::METHOD_GET:
            case ServerRequest::METHOD_DELETE:
                $request = new ServerRequest(
                    $serverParams = [],
                    $uploadedFiles = [],
                    new Uri($url),
                    $method,
                    $body = 'php://input',
                    $headers = [],
                    $cookies = [],
                    $queryParams = $params,
                    $parsedBody = [],
                    $protocol = '1.1'
                );
                break;
            case ServerRequest::METHOD_PUT:
            case ServerRequest::METHOD_PATCH:
                $request = new ServerRequest(
                    $serverParams = [],
                    $uploadedFiles = [],
                    new Uri($url),
                    $method,
                    $body = 'php://memory',
                    $headers = ['Content-Type'  => 'application/json'],
                    $cookies = [],
                    $queryParams = $params,
                    $parsedBody = $params,
                    $protocol = '1.1'
                );
                $request->getBody()->write(json_encode($params));
                break;
            default:
                trigger_error(
                    'Additional params is only supported by GET, POST, PUT and PATCH HTTP method',
                    E_USER_NOTICE
                );
        }
        if ($isXmlHttpRequest) {
            $request = $request->withAddedHeader('X_REQUESTED_WITH', 'XMLHttpRequest');
        }
        $this->getContainer()->setService('Request', $request);

        return $this;
    }

    /**
     * Dispatch the MVC with a URL
     * Accept a HTTP (simulate a customer action) or console route.
     *
     * The URL provided set the request URI in the request object.
     *
     * @param  string      $url
     * @param  string|null $method
     * @param  array|null  $params
     * @throws \Exception
     */
    public function dispatch($url, $method = null, $params = [], $isXmlHttpRequest = false)
    {
        $this->url($url, $method, $params, $isXmlHttpRequest);
        $this->getApplication()->runWithoutEmit();
    }

    /**
     * Reset the request
     *
     * @return AbstractControllerTestCase
     */
    public function reset($keepPersistence = false)
    {
        // force to re-create all components
        $this->application = null;

        // reset server data
        if (! $keepPersistence) {
            // Do not create a global session variable if it doesn't already
            // exist. Otherwise calling this function could mark tests risky,
            // as it changes global state.
            if (array_key_exists('_SESSION', $GLOBALS)) {
                $_SESSION = [];
            }
            $_COOKIE  = [];
        }

        $_GET     = [];
        $_POST    = [];

        // reset singleton
        if (class_exists(StaticEventManager::class)) {
            StaticEventManager::resetInstance();
        }

        return $this;
    }

    /**
     * Trigger an application event
     *
     * @param  string                                $eventName
     * @return \Laminas\EventManager\ResponseCollection
     */
    public function triggerApplicationEvent($eventName)
    {
        $events = $this->getApplication()->getEventManager();
        $event  = $this->getApplication()->getPageEvent();

        if ($eventName != PageEvent::EVENT_ROUTE && $eventName != PageEvent::EVENT_DISPATCH_PAGE) {
            return $events->trigger($eventName, $event);
        }

        $shortCircuit = function ($r) use ($event) {
            if ($r instanceof ResponseInterface) {
                return true;
            }

            if ($event->getError()) {
                return true;
            }

            return false;
        };

        $event->setName($eventName);
        return $events->triggerEventUntil($shortCircuit, $event);
    }

    /**
     * Assert modules were loaded with the module manager
     *
     * @param array $modules
     */
    public function assertModulesLoaded(array $modules)
    {
        $moduleManager = $this->getApplicationServiceLocator()->get('ModuleManager');
        $modulesLoaded = $moduleManager->getModules();
        $list          = array_diff($modules, $modulesLoaded);
        if ($list) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Several modules are not loaded "%s"', implode(', ', $list))
            ));
        }
        $this->assertEquals(count($list), 0);
    }

    /**
     * Assert modules were not loaded with the module manager
     *
     * @param array $modules
     */
    public function assertNotModulesLoaded(array $modules)
    {
        $moduleManager = $this->getApplicationServiceLocator()->get('ModuleManager');
        $modulesLoaded = $moduleManager->getModules();
        $list          = array_intersect($modules, $modulesLoaded);
        if ($list) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Several modules WAS not loaded "%s"', implode(', ', $list))
            ));
        }
        $this->assertEquals(count($list), 0);
    }

    /**
     * Retrieve the response status code
     *
     * @return int
     */
    protected function getResponseStatusCode()
    {
        $response = $this->getResponse();
        if (! $this->useConsoleRequest) {
            return $response->getStatusCode();
        }

        $match = $response->getErrorLevel();
        if (null === $match) {
            $match = 0;
        }

        return $match;
    }

    /**
     * Assert response status code
     *
     * @param int $code
     */
    public function assertResponseStatusCode($code)
    {
        if ($this->useConsoleRequest) {
            if (! in_array($code, [0, 1])) {
                throw new ExpectationFailedException($this->createFailureMessage(
                    'Console status code assert value must be O (valid) or 1 (error)'
                ));
            }
        }
        $match = $this->getResponseStatusCode();
        if ($code != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting response code "%s", actual status code is "%s"', $code, $match)
            ));
        }
        $this->assertEquals($code, $match);
    }

    /**
     * Assert not response status code
     *
     * @param int $code
     */
    public function assertNotResponseStatusCode($code)
    {
        if ($this->useConsoleRequest) {
            if (! in_array($code, [0, 1])) {
                throw new ExpectationFailedException($this->createFailureMessage(
                    'Console status code assert value must be O (valid) or 1 (error)'
                ));
            }
        }
        $match = $this->getResponseStatusCode();
        if ($code == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting response code was NOT "%s"', $code)
            ));
        }
        $this->assertNotEquals($code, $match);
    }

    /**
     * Assert the application exception and message
     *
     * @param string $type application exception type
     * @param string|null $message application exception message
     */
    public function assertApplicationException($type, $message = null)
    {
        $exception = $this->getApplication()->getMvcEvent()->getParam('exception');
        if (! $exception) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting application exception, param "exception" does not exist'
            ));
        }
        if (true === $this->traceError) {
            // set exception as null because we know and have assert the exception
            $this->getApplication()->getMvcEvent()->setParam('exception', null);
        }

        if (! method_exists($this, 'expectException')) {
            // For old PHPUnit 4
            $this->setExpectedException($type, $message);
        } else {
            $this->expectException($type);
            if (! empty($message)) {
                $this->expectExceptionMessage($message);
            }
        }

        throw $exception;
    }

    /**
     * Get the full current controller class name
     *
     * @return string
     */
    protected function getControllerFullClassName()
    {
        $routeMatch           = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $controllerIdentifier = $routeMatch->getParam('controller');
        $controllerManager    = $this->getApplicationServiceLocator()->get('ControllerManager');
        $controllerClass      = $controllerManager->get($controllerIdentifier);

        return get_class($controllerClass);
    }

    /**
     * Assert that the application route match used the given module
     *
     * @param string $module
     */
    public function assertModuleName($module)
    {
        $controllerClass = $this->getControllerFullClassName();
        $match           = substr($controllerClass, 0, strpos($controllerClass, '\\'));
        $match           = strtolower($match);
        $module          = strtolower($module);
        if ($module != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting module name "%s", actual module name is "%s"', $module, $match)
            ));
        }
        $this->assertEquals($module, $match);
    }

    /**
     * Assert that the application route match used NOT the given module
     *
     * @param string $module
     */
    public function assertNotModuleName($module)
    {
        $controllerClass = $this->getControllerFullClassName();
        $match           = substr($controllerClass, 0, strpos($controllerClass, '\\'));
        $match           = strtolower($match);
        $module          = strtolower($module);
        if ($module == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting module was NOT "%s"', $module)
            ));
        }
        $this->assertNotEquals($module, $match);
    }

    /**
     * Assert that the application route match used the given controller class
     *
     * @param string $controller
     */
    public function assertControllerClass($controller)
    {
        $controllerClass = $this->getControllerFullClassName();
        $match           = substr($controllerClass, strrpos($controllerClass, '\\') + 1);
        $match           = strtolower($match);
        $controller      = strtolower($controller);
        if ($controller != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller class "%s", actual controller class is "%s"', $controller, $match)
            ));
        }
        $this->assertEquals($controller, $match);
    }

    /**
     * Assert that the application route match used NOT the given controller class
     *
     * @param string $controller
     */
    public function assertNotControllerClass($controller)
    {
        $controllerClass = $this->getControllerFullClassName();
        $match           = substr($controllerClass, strrpos($controllerClass, '\\') + 1);
        $match           = strtolower($match);
        $controller      = strtolower($controller);
        if ($controller == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller class was NOT "%s"', $controller)
            ));
        }
        $this->assertNotEquals($controller, $match);
    }

    /**
     * Assert that the application route match used the given controller name
     *
     * @param string $controller
     */
    public function assertControllerName($controller)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getParam('controller');
        $match      = strtolower($match);
        $controller = strtolower($controller);
        if ($controller != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller name "%s", actual controller name is "%s"', $controller, $match)
            ));
        }
        $this->assertEquals($controller, $match);
    }

    /**
     * Assert that the application route match used NOT the given controller name
     *
     * @param string $controller
     */
    public function assertNotControllerName($controller)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getParam('controller');
        $match      = strtolower($match);
        $controller = strtolower($controller);
        if ($controller == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting controller name was NOT "%s"', $controller)
            ));
        }
        $this->assertNotEquals($controller, $match);
    }

    /**
     * Assert that the application route match used the given action
     *
     * @param string $action
     */
    public function assertActionName($action)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getParam('action');
        $match      = strtolower($match);
        $action     = strtolower($action);
        if ($action != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting action name "%s", actual action name is "%s"', $action, $match)
            ));
        }
        $this->assertEquals($action, $match);
    }

    /**
     * Assert that the application route match used NOT the given action
     *
     * @param string $action
     */
    public function assertNotActionName($action)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getParam('action');
        $match      = strtolower($match);
        $action     = strtolower($action);
        if ($action == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting action name was NOT "%s"', $action)
            ));
        }
        $this->assertNotEquals($action, $match);
    }

    /**
     * Assert that the application route match used the given route name
     *
     * @param string $route
     */
    public function assertMatchedRouteName($route)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getMatchedRouteName();
        $match      = strtolower($match);
        $route      = strtolower($route);
        if ($route != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf(
                    'Failed asserting matched route name was "%s", actual matched route name is "%s"',
                    $route,
                    $match
                )
            ));
        }
        $this->assertEquals($route, $match);
    }

    /**
     * Assert that the application route match used NOT the given route name
     *
     * @param string $route
     */
    public function assertNotMatchedRouteName($route)
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if (! $routeMatch) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match      = $routeMatch->getMatchedRouteName();
        $match      = strtolower($match);
        $route      = strtolower($route);
        if ($route == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting route matched was NOT "%s"', $route)
            ));
        }
        $this->assertNotEquals($route, $match);
    }

    /**
     * Assert that the application did not match any route
     */
    public function assertNoMatchedRoute()
    {
        $routeMatch = $this->getApplication()->getMvcEvent()->getRouteMatch();
        if ($routeMatch) {
            $match      = $routeMatch->getMatchedRouteName();
            $match      = strtolower($match);
            throw new ExpectationFailedException($this->createFailureMessage(sprintf(
                'Failed asserting that no route matched, actual matched route name is "%s"',
                $match
            )));
        }
        $this->assertNull($routeMatch);
    }

    /**
     * Assert template name
     * Assert that a template was used somewhere in the view model tree
     *
     * @param string $templateName
     */
    public function assertTemplateName($templateName)
    {
        $viewModel = $this->getApplication()->getMvcEvent()->getViewModel();
        $this->assertTrue($this->searchTemplates($viewModel, $templateName));
    }

    /**
     * Assert not template name
     * Assert that a template was not used somewhere in the view model tree
     *
     * @param string $templateName
     */
    public function assertNotTemplateName($templateName)
    {
        $viewModel = $this->getApplication()->getMvcEvent()->getViewModel();
        $this->assertFalse($this->searchTemplates($viewModel, $templateName));
    }

    /**
     * Recursively search a view model and it's children for the given templateName
     *
     * @param  \Laminas\View\Model\ModelInterface $viewModel
     * @param  string    $templateName
     * @return boolean
     */
    protected function searchTemplates($viewModel, $templateName)
    {
        if ($viewModel->getTemplate($templateName) == $templateName) {
            return true;
        }
        foreach ($viewModel->getChildren() as $child) {
            return $this->searchTemplates($child, $templateName);
        }

        return false;
    }
}
