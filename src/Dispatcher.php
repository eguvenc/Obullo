<?php

namespace Obullo;

use ReflectionClass;
use ReflectionParameter;
use Obullo\Router\Router;
use Obullo\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface as Request;
use Interop\Container\ContainerInterface;
use Obullo\Exception\PageMethodNotExistsException;
use Obullo\Exception\InvalidPageResponseException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

final class Dispatcher
{
    /**
     * @var object
     */
    private $request;

    /**
     * @var model
     */
    private $model;

    /**
     * @var string page method
     */
    private $method;

    /**
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * @var Obullo\Router\Router
     */
    private $router;

    /**
     * Dispatch options
     *
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param array $options options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Set request object
     *
     * @param request object
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns to request object
     *
     * @return object
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set router
     *
     * @param Router $router router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Returns to router
     *
     * @return Obullo\Router\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Set page model
     *
     * @param object $model page handler
     */
    public function setPageModel($model)
    {
        $this->model = $model;
    }

    /**
     * Returns to page model
     *
     * @return object
     */
    public function getPageModel()
    {
        return $this->model;
    }

    /**
     * Set page method
     *
     * @param string $method
     */
    public function setPageMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Returns to page method (onGet, onPost ..)
     *
     * @return string
     */
    public function getPageMethod()
    {
        return $this->method;
    }

    /**
     * Set reflection object
     *
     * @param ReflectionClass $reflection reflection class
     */
    public function setReflectionClass(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Returns to reflection object
     *
     * @return object
     */
    public function getReflectionClass() : ReflectionClass
    {
        $reflection = ($this->reflection) ? $this->reflection : new ReflectionClass($this->getPageModel());

        return $reflection;
    }

    /**
     * Execute page handler
     *
     * @return null|response object
     * @throws PageMethodNotExistsException if display_exceptions is not true
     */
    public function dispatch()
    {
        $request = $this->getRequest();
        $methodName = $this->getPageMethod();
        $reflection = $this->getReflectionClass();
        $requestedName = $reflection->getName();

        if ($reflection->hasMethod($methodName)) {
            $reflectionParameters = $reflection->getMethod($methodName)->getParameters();
            if (empty($reflectionParameters)) {
                $response = $this->getPageModel()->$methodName();
                $this->isResponse($response, $requestedName);
                return $response;
            }
            $resolver = $this->resolveParameters($request, $requestedName);
            $parameters = array_map($resolver, $reflectionParameters);
            $response = $this->getPageModel()->$methodName(...$parameters);
            $this->isResponse($response, $requestedName);
            return $response;
        }
        if (! empty($this->options['partial_view'])) {
            throw new PageMethodNotExistsException(
                sprintf(
                    'The method %s does not exists in %s.',
                    $methodName,
                    $reflection->getName()
                )
            );
        }
        return null; // 404 not found
    }

    /**
     * Check response
     *
     * @param  object  $response response
     * @param  string  $requestedName model name
     * @return void
     * @throws PageNullResponseException is response null
     */
    private function isResponse($response, $requestedName)
    {
        if (null == $response) {
            throw new InvalidPageResponseException(
                sprintf(
                    'Return value of %s must be an instance of Psr\Http\Message\ResponseInterface, null returned',
                    $requestedName
                )
            );
        }
    }

    /**
     * Returns a callback for resolving a parameter from matched route arguments, including http raw body data.
     *
     * @param Psr\Http\Message\ServerRequestInferface $request
     * @return callable
     */
    private function resolveParameters(Request $request, $requestedName)
    {
        /**
         * @param ReflectionParameter $parameter
         * @return mixed
         * @throws ServiceNotFoundException If type-hinted parameter cannot be
         *   resolved to a argument name in the matched route.
         */
        return function (ReflectionParameter $parameter) use ($request, $requestedName) {

            $parameterName = $parameter->getName();
            $router = $this->getRouter();

            if ($parameter->isArray()) {

                // https://tools.ietf.org/html/rfc7231
                // 
                switch ($request->getMethod()) {
                    case ServerRequest::METHOD_POST:
                    case ServerRequest::METHOD_PUT:
                    case ServerRequest::METHOD_PATCH:
                    case ServerRequest::METHOD_OPTIONS:
                        $parameterData = $request->getParsedBody();
                        break;
                    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
                    // 
                    case ServerRequest::METHOD_HEAD:
                    case ServerRequest::METHOD_GET:
                    case ServerRequest::METHOD_TRACE:
                    case ServerRequest::METHOD_CONNECT:
                    case ServerRequest::METHOD_DELETE:
                    // PROPFIND — used to retrieve properties, stored as XML, from a web resource.
                    case ServerRequest::METHOD_PROPFIND:
                        $parameterData = $request->getQueryParams();
                        break;
                    default:
                        $parameterData = array();
                        break;
                }
                if (in_array($parameterName, [
                        'options',
                        'get',
                        'head',
                        'post',
                        'put',
                        'delete',
                        'trace',
                        'connect',
                        'patch',
                        'propfind'
                    ])) {
                    return $parameterData;
                }
                return [];  // default array
            }

            // on query method support without no parameters
            // 
            if (false == $router->hasMatch()) {
                return [];
            }
            return $this->resolveRouteParameter($parameter, $router, $requestedName);
        };
    }

    /**
     * Logic common to route parameter resolution.
     *
     * @param ReflectionParameter $parameter
     * @param Obullo\Router\Router $router
     * @param string $requestName class name
     * @return mixed
     * @throws ServiceNotFoundException If type-hinted parameter cannot be
     *   resolved to a argument name in the matched route.
     */
    private function resolveRouteParameter(ReflectionParameter $parameter, $router, $requestedName)
    {
        $name = $parameter->getName();
        $args = $router->getMatchedRoute()->getArguments();
        /**
         * Bind route arguments
         */
        if (array_key_exists($name, $args)) {
            return $args[$name];
        }

        if (! $parameter->isDefaultValueAvailable()) {
            throw new ServiceNotFoundException(sprintf(
                'Unable to create service "%s"; unable to resolve default value of route parameter "%s"',
                $requestedName,
                $name
            ));
        }

        return $parameter->getDefaultValue();
    }
}