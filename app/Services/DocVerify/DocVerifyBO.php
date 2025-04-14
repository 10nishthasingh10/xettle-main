<?php

namespace App\Services\DocVerify;

class DocVerifyBO
{
    public $http = 'post';
    public $uri;
    public $param;
    public $log = 'yes';
    public $userId;
    public $table = 'validation';
    public $slug;
    public $clientRefId;
}
