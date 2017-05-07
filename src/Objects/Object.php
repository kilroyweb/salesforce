<?php

namespace KilroyWeb\Salesforce\Objects;

class Object{

    public function __construct($attributes = [])
    {
        $this->setAttributes($attributes);
    }

    public function setAttributes($attributes=[]){
        foreach($attributes as $attributeKey=>$attributeValue){
            $this->$attributeKey = $attributeValue;
        }
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }else{
            return null;
        }
    }

}