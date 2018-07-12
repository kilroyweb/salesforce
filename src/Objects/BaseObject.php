<?php

namespace KilroyWeb\Salesforce\Objects;

class BaseObject{

    public function __construct($attributes = [])
    {
        $this->setAttributes($attributes);
    }

    public function setAttributes($attributes=[]){
        foreach($attributes as $attributeKey=>$attributeValue){
            $this->$attributeKey = $attributeValue;
        }
    }

    public function formattedDate($field,$format){
        $dateValue = $this->$field;
        if(!empty($dateValue)){
            $date = \Carbon\Carbon::parse($dateValue);
            return $date->format($format);
        }
        return null;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }else{
            return null;
        }
    }

}
