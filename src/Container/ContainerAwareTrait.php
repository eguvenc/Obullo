<?php

namespace Obullo\Container;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{
    protected $container;

    /**
     * Set psr11 container
     * 
     * @param ContainerInterface $container container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Returns to psr11 container
     * 
     * @return object
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }
}