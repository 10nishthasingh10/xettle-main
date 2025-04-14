<?php

namespace App\Services\DocVerify;

class DocService
{

    private $service;

    public function __construct(DocVerify $service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }
}
