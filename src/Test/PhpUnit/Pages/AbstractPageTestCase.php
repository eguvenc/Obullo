<?php

namespace Obullo\Test\PHPUnit\Pages;

use Laminas\Console\Console;
use Laminas\EventManager\StaticEventManager;
use Laminas\ServiceManager\ServiceManager;
use Obullo\Http\ServerRequest;
use Obullo\Container\ServiceManagerConfig;
use Obullo\PageEvent;
use Obullo\Factory\LazyPageFactory;
use Obullo\Factory\LazyMiddlewareFactory;
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
        $this->container->addAbstractFactory(new LazyPageFactory);
        $this->container->addAbstractFactory(new LazyMiddlewareFactory);
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
     * @return Psr\Http\Message\ServerRequestInterface
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
        return $this->getApplication()->getPageEvent()->getResponse();
    }

    /**
     * Returns to view model of requested handler
     *
     * @param  string $handler fully qualified page model name
     * @return ViewModel
     */
    public function getViewModel($handler)
    {
        $pageModel = $this->getApplication()->getPageEvent()->getPageModel($handler);
        if (! $pageModel) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('The "%s" handler cannot resolved', $handler)
            ));
        }
        return $pageModel->getViewModel();
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
            case ServerRequest::METHOD_OPTIONS:
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
            case ServerRequest::METHOD_HEAD:
            case ServerRequest::METHOD_GET:
            case ServerRequest::METHOD_TRACE:
            case ServerRequest::METHOD_CONNECT:
            case ServerRequest::METHOD_DELETE:
            case ServerRequest::METHOD_PROPFIND:
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
     * @param  string $eventName
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
        $exception = $this->getApplication()->getPageEvent()->getParam('exception');
        if (! $exception) {
            throw new ExpectationFailedException($this->createFailureMessage(
                'Failed asserting application exception, param "exception" does not exist'
            ));
        }
        if (true === $this->traceError) {
            // set exception as null because we know and have assert the exception
            $this->getApplication()->getPageEvent()->setParam('exception', null);
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
     * Get the full page model class name
     *
     * @return string
     */
    protected function getPageModelFullClassName()
    {
        $route = $this->getApplication()->getPageEvent()->getMatchedRoute();
        if (! $route) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        return $route->getHandler();
    }

    /**
     * Get resolved module name
     *
     * @return string
     */
    protected function getResolvedModuleName()
    {
        $route = $this->getApplication()->getPageEvent()->getMatchedRoute();
        if (! $route) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        return $this->getApplication()->getPageEvent()->getResolvedModuleName();
    }

    /**
     * Assert that the application route match used the given module
     *
     * @param string $module
     */
    public function assertModuleName($module)
    {
        $match = $this->getResolvedModuleName();
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
        $match = $this->getResolvedModuleName();
        if ($module == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting module was NOT "%s"', $module)
            ));
        }
        $this->assertNotEquals($module, $match);
    }

    /**
     * Assert that the application route match used the given page model
     *
     * @param string $pageModel
     */
    public function assertPageModel($model)
    {
        $match = $this->getPageModelFullClassName();
        $match = substr($match, strrpos($match, '\\') + 1);
        if ($model != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting page model "%s", actual page model is "%s"', $model, $match)
            ));
        }
        $this->assertEquals($model, $match);
    }

    /**
     * Assert that the application route match used NOT the given page model
     *
     * @param string $model
     */
    public function assertNotPageModel($model)
    {
        $match = $this->getPageModelFullClassName();
        $match = substr($model, strrpos($model, '\\') + 1);
        if ($model == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting page model was NOT "%s"', $model)
            ));
        }
        $this->assertNotEquals($model, $match);
    }

    /**
     * Assert that the application route match used the given page model name
     *
     * @param string $model
     */
    public function assertPageModelName($model)
    {
        $route = $this->getApplication()->getPageEvent()->getMatchedRoute();
        if (! $route) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match = $route->getHandler();
        if ($model != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting model name "%s", actual model name is "%s"', $model, $match)
            ));
        }
        $this->assertEquals($model, $match);
    }

    /**
     * Assert that the application route match used NOT the given page model name
     *
     * @param string $model
     */
    public function assertNotPageModelName($model)
    {
        $route = $this->getApplication()->getPageEvent()->getMatchedRoute();
        if (! $route) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match = $route->getHandler();
        if ($model == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting model name was NOT "%s"', $model)
            ));
        }
        $this->assertNotEquals($model, $match);
    }

    /**
     * Assert that the application route match used the given route path
     *
     * @param string $path
     */
    public function assertMatchedRoutePath($path)
    {
        $route = $this->getApplication()->getPageEvent()->getMatchedRoute();
        if (! $route) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match = $route->getPath();
        if ($path != $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf(
                    'Failed asserting matched route path was "%s", actual matched route path is "%s"',
                    $path,
                    $match
                )
            ));
        }
        $this->assertEquals($path, $match);
    }

    /**
     * Assert that the application route match used NOT the given route path
     *
     * @param string $path
     */
    public function assertNotMatchedRoutePath($path)
    {
        $route = $this->getApplication()->getPageEvent()->getMatchedRoute();
        if (! $route) {
            throw new ExpectationFailedException($this->createFailureMessage('No route matched'));
        }
        $match = $route->getPath();
        if ($path == $match) {
            throw new ExpectationFailedException($this->createFailureMessage(
                sprintf('Failed asserting route path matched was NOT "%s"', $path)
            ));
        }
        $this->assertNotEquals($path, $match);
    }

    /**
     * Assert template name
     * Assert that a template was used somewhere in the view model tree
     *
     * @param string $templateName
     */
    public function assertTemplateName($templateName, $handler = null)
    {
        if ($handler) {
            $viewModel = $this->getViewModel(str_replace('/', '\\', $handler));
        } else {
            $handler = str_replace('/', '\\', $templateName).'Model';
            $viewModel = $this->getViewModel($handler);
        }
        $this->assertTrue($this->searchTemplates($viewModel, $templateName));
    }

    /**
     * Assert not template name
     * Assert that a template was not used somewhere in the view model tree
     *
     * @param string $templateName
     */
    public function assertNotTemplateName($templateName, $handler = null)
    {
        if ($handler) {
            $viewModel = $this->getViewModel(str_replace('/', '\\', $handler));
        } else {
            $handler = str_replace('/', '\\', $templateName).'Model';
            $viewModel = $this->getViewModel($handler);
        }
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