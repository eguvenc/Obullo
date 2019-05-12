<?php

namespace Obullo\Container;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    /**
     * Set psr11 container
     * 
     * @param ContainerInterface $container container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Returns to psr11 container
     * 
     * @return object
     */
    public function getContainer() : ContainerInterface;
}