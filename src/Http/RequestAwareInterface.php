<?php

namespace Obullo\Http;

use Psr\Http\Message\RequestInterface as Request;

interface RequestAwareInterface
{
    /**
     * Set psr7 request
     * 
     * @param Request $request request
     */
    public function setRequest(Request $request);

    /**
     * Returns to psr7 request
     * 
     * @return object
     */
    public function getRequest() : Request;
}