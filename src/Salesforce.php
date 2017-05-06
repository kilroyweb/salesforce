<?php

namespace KilroyWeb\Salesforce;

class Salesforce
{

    private $objectName;

    public static function object($objectName){
        $instance = new static;
        $instance->setObject($objectName);
        return $instance;
    }

    private function setObject($objectName){
        $this->objectName = $objectName;
    }

    public function find($id){
        return $id;
    }
}
