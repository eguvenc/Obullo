<?php

namespace Obullo\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Obullo\Container\ContainerAwareInterface;
use Obullo\Container\ContainerAwareTrait;

/**
 * Model helper plugin to fetch page models.
 */
class Model extends AbstractHelper implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var object
     */
    protected $model;

    /**
     * Return model
     * 
     * @return 
     */
    public function __invoke($model)
    {
        return $this->container->build($model);    
    }
}