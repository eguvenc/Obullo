<?php

namespace App\Command;

class ClearCache
{
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
