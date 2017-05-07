<?php

namespace KilroyWeb\Salesforce\Fields;

class Field
{

    public $name;
    public $nullable;
    public $minOccurs;
    public $type;
    public $value;
    public $selectable = false;
    public $filterable = false;
    public $creatable = false;
    public $updatable = false;

    public function __construct($attributes = [])
    {
        $this->setAttributes($attributes);
        $this->removeExtraCharactersFromType();
        $this->updateAttributeDefaults();
    }

    public function setAttributes($attributes=[]){
        foreach($attributes as $attributeKey=>$attributeValue){
            $this->$attributeKey = $attributeValue;
        }
    }

    private function removeExtraCharactersFromType()
    {
        $this->type = str_replace('tns:', '', $this->type);
        $this->type = str_replace('ens:', '', $this->type);
        $this->type = str_replace('xsd:', '', $this->type);
        if ($this->type == 'ID') {
            $this->type = 'id';
        }
    }

    private function updateAttributeDefaults()
    {
        $this->updateSelectableDefault();
        $this->updateFilterableDefault();
        $this->updateCreatableDefault();
        $this->updateUpdatableDefault();
    }

    private function updateSelectableDefault()
    {
        $selectableTypes = [
            'id',
            'string',
            'boolean',
            'dateTime',
            'date',
            'double',
        ];
        if (in_array($this->type, $selectableTypes)) {
            $this->selectable = true;
        }
    }

    private function updateFilterableDefault()
    {
        $types = [
            'id',
            'string',
            'boolean',
            'dateTime',
            'date',
            'double',
        ];
        if (in_array($this->type, $types)) {
            $this->filterable = true;
        }
    }

    private function updateCreatableDefault()
    {
        $types = [
            'id',
            'string',
            'boolean',
            'dateTime',
            'date',
            'double',
        ];
        if ($this->name != 'Id') {
            if (in_array($this->type, $types)) {
                $this->creatable = true;
            }
        }
    }

    private function updateUpdatableDefault()
    {
        $types = [
            'id',
            'string',
            'boolean',
            'dateTime',
            'date',
            'double',
        ];
        if ($this->name != 'Id') {
            if (in_array($this->type, $types)) {
                $this->updatable = true;
            }
        }
    }

    public function setValue($value)
    {
        $this->value = $this->formatValue($value);
    }

    private function formatValue($value){
        if($this->type == 'base64Binary'){
            $value = $this->formatBase64Value($value);
        }else{
            $value = $this->formatSOAPValue($value, $this->type);
        }
        return $value;
    }

    public function formatSOAPValue($value,$format){
        switch($format){
            case 'Id':
                $value = trim($value);
                break;
            case 'address':
                $value = trim($value);
                break;
            case 'double':
                $value = preg_replace("/[^0-9.-]/", "", $value);
                if(empty($value)){
                    $value = 0;
                }
                break;
            case 'dateTime':
                $value = date("Y-m-d",strtotime($value)) . 'T' . date("H:i:s",strtotime($value));
                break;
            case 'date': //0000-00-00?
                $value = date('Y-m-d',strtotime($value));
                break;
            case 'boolean':
                $value = trim($value);
                $value = (boolean) $value;
                break;
            case 'int':
                $value = preg_replace("/[^0-9.-]/", "", $value);
                $value = floor($value);
                break;
            default:
                $value = trim($value);
                break;
        }
        return $value;
    }

    private function formatBase64Value($value){
        if(!$this->isBase64($value)){
            $fileContents = file_get_contents($value);
            $value = chunk_split(base64_encode($fileContents));
        }
        return $value;
    }

    private function isBase64($str){
        $str = str_replace("\r\n",'',$str);
        if ( base64_encode(base64_decode($str, true)) === $str){
            return true;
        } else {
            return false;
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