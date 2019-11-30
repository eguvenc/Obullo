<?php

namespace Obullo\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Model helper plugin to fetch page models.
 */
class Model extends AbstractHelper
{
    /**
     * @var object
     */
    protected $model;
    
    /**
     * Set model
     * 
     * @param $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Return model
     * 
     * @return 
     */
    public function __invoke()
    { 
        return $this->model;    
    }
}