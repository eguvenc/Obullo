<?php

namespace Obullo;

use Laminas\Diactoros\Response;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Obullo\Container\ContainerAwareTrait;

class ErrorHandlerManager
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string module name
     */
    protected $module;

    /**
     * Set config
     *
     * @param array $config config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Returns to config
     *
     * @return array
     */
    public function getConfig() : array
    {
        return $this->config;
    }

    /**
     * Set resolved module
     *
     * @param string $module
     */
    public function setResolvedModule(string $module)
    {
        $this->module = $module;
    }

    /**
     * Returns to module
     *
     * @return string
     */
    public function getResolvedModule() : string
    {
        return $this->module;
    }

    /**
     * Set error handlers
     *
     * @return void
     */
    public function getErrorHandlers()
    {
        $config = $this->getConfig();
        $module = $this->getResolvedModule();

        $return['error_generator'] = 'App\Middleware\ErrorResponseGenerator';
        if (! empty($config['error_handlers'][$module]['error_generator'])) {
            $return['error_generator'] = $config['error_handlers'][$module]['error_generator'];
        }
        $return['error_404'] = 'App\Middleware\ErrorNotFoundHandler';
        if (! empty($config['error_handlers'][$module]['error_404'])) {
            $return['error_404'] = $config['error_handlers'][$module]['error_404'];
        }
        $return['error_generator'] = $this->createErrorGenerator($return['error_generator']);
        $return['error_404'] = new $return['error_404']($this->getContainer());
        return $return;
    }

    /**
     * Create and return to error generator handler 
     * 
     * @param  string $errorGeneratorHandler error generator class
     * @return object
     */
    protected function createErrorGenerator(string $errorGeneratorHandler)
    {
        $errorHandler = new ErrorHandler(
            function () {
                return new Response;
            },
			new $errorGeneratorHandler($this->getContainer())            
        );
        return $errorHandler;
    }
}
