<?php

namespace KilroyWeb\Salesforce\Clients;

use Davispeixoto\ForceDotComToolkitForPhp\SforceEnterpriseClient;

class Salesforce extends BaseClient {

    protected $requiredCredentials = [
        'wsdlPath',
        'username',
        'password',
        'token',
    ];

    public function createConnection(){
        $this->connection = new SforceEnterpriseClient();
        $this->connection->createConnection(storage_path($this->credentials['wsdlPath']));
        try{
            $this->connection->login(
                $this->credentials['username'],
                $this->credentials['password'].$this->credentials['token']
            );
        } catch (\Exception $e) {
            return 'Caught exception: '.$e->getMessage()."\n";
        }
    }

    public function query($sql){
        return $this->connection->query($sql);
    }

}