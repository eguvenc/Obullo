<?php

namespace Obullo\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Obullo\Container\ContainerAwareInterface;
use Obullo\Container\ContainerAwareTrait;

/**
 * Model helper plugin to fetch page models.
 */
class Model extends AbstractHelper implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Return model
     * 
     * @return 
     */
    public function __invoke($model, array $options = null)
    {
        return $this->container->build($model, $options);
    }
}