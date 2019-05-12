<?php

namespace Obullo\Http;

use Psr\Http\Message\RequestInterface as Request;

trait RequestAwareTrait
{
    protected $request;

    /**
     * Set psr7 request
     * 
     * @param Request $request request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Returns to psr7 request
     * 
     * @return object
     */
    public function getRequest() : Request
    {
        return $this->request;
    }
}