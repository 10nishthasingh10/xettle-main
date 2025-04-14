<?php

namespace App\Services\DocVerify;

interface DocVerify
{
    public function send(DocVerifyBO $obj);

    public function create($userId, $dbData, $taxData);

    public function update($cond = [], $data = []);
}
