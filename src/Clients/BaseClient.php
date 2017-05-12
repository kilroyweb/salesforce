<?php

namespace KilroyWeb\Salesforce\Clients;

abstract class BaseClient{

    protected $connection;
    protected $credentials = [];

    public function setCredentials(array $credentials = []){
        $this->credentials = $credentials;
    }

    public function getConnection(){
        return $this->connection;
    }

}