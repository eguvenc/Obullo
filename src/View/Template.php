<?php

namespace Obullo\View;

use Throwable;
use Exception;
use LogicException;
use Obullo\View\Template\Name;
use Obullo\Container\ContainerAwareTrait;
use Obullo\Container\ContainerAwareInterface;

/**
 * Derived from league/plates package
 */
class Template implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $_data = array();
    protected $_engine;

    /**
     * Constructor
     *
     * @param Engine $engine template engine
     */
    public function __construct(Engine $engine)
    {
        $this->_engine = $engine;
    }

    /**
     * Returns to service
     *
     * @param  string $name service name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * Set template variable
     *
     * @param string $name  key
     * @param mixed  $value value
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Assign or get template data.
     *
     * @param  array $data
     * @return mixed
     */
    public function data(array $data = null)
    {
        if (is_null($data)) {
            return $this->_data;
        }
        $this->_data = array_merge($this->_data, $data);
    }

    /**
     * Start buffer
     *
     * @return void
     */
    public function start()
    {
        ob_start();
    }

    /**
     * Clean and return to buffer
     *
     * @return string
     */
    public function end()
    {
        return ob_get_clean();
    }

    /**
     * Render view
     *
     * @param  string $name filename
     * @param  array  $data variables
     * @return void
     */
    public function render($name, $data = array())
    {
        $name = new Name($this->_engine, $name);

        $this->data($data);
        if (false == $name->doesPathExist()) {
            throw new LogicException(
                sprintf(
                    'The template %s could not be found at %s.',
                    $name->getName(),
                    $name->getPath()
                )
            );
        }
        extract($this->_data, EXTR_OVERWRITE);
        try {
            $level = ob_get_level();
            ob_start();

            include $name->getPath();

            $content = ob_get_clean();
            return $content;
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
    }
}
