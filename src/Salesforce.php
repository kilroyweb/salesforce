<?php

namespace KilroyWeb\Salesforce;

use KilroyWeb\Salesforce\Fields\Field;
use KilroyWeb\Salesforce\Objects\BaseObject;
use KilroyWeb\Salesforce\Parsers\WSDLParser;
use KilroyWeb\Salesforce\QueryBuilders\SalesforceQueryBuilder;

class Salesforce
{

    private $client;
    private $wsdlPath;
    private $objectName;
    private $fields;
    private $selectableFields;
    private $selectArray = [];
    private $filterArray = [];
    private $limit = 2000;

    public static function init(){
        $instance = new static;
        $instance->wsdlPath = config('salesforce.wsdl-path');
        $instance->client = new \KilroyWeb\Salesforce\Clients\Salesforce();
        $instance->client->setCredentials([
            'wsdlPath' => $instance->wsdlPath,
            'username' => config('salesforce.username'),
            'password' => config('salesforce.password'),
            'token' => config('salesforce.token'),
        ]);
        $instance->client->createConnection();
        return $instance;
    }

    private function getCreatableFields($attributes=[]){
        $returnFields = [];
        $fields = collect($this->fields);
        if(!empty($attributes)) {
            foreach ($attributes as $attributeName=>$attributeValue) {
                $field = $fields->where('name',$attributeName)->first();
                if($field && $field->creatable){
                    $field->setValue($attributeValue);
                    $returnFields[$field->name] = $field->value;
                }
            }
        }
        return $returnFields;
    }

    private function getUpdatableFields($attributes=[]){
        $returnFields = [];
        $fields = collect($this->fields);
        if(!empty($attributes)) {
            foreach ($attributes as $attributeName=>$attributeValue) {
                $field = $fields->where('name',$attributeName)->first();
                if($field && $field->updatable){
                    $field->setValue($attributeValue);
                    $returnFields[$field->name] = $field->value;
                }
            }
        }
        return $returnFields;
    }

    public static function describe($objectName){
        $instance = static::init();
        $response = $instance->client->getConnection()->describeSObject($objectName);
        $object = new BaseObject($response);
        return $object;
    }

    public static function objectField($objectName, $fieldName){
        $instance = static::init();
        $description = static::describe($objectName);
        if(!empty($description->fields)){
            foreach($description->fields as $field){
                if($field->name == $fieldName){
                    return $field;
                }
            }
        }
        return null;
    }

    public static function pickList($objectName, $fieldName){
        $instance = static::init();
        $field = static::objectField($objectName,$fieldName);
        $items = [];
        if($field){
            foreach($field->picklistValues as $picklistValue){
                $items[] = $picklistValue;
            }
        }
        return $instance->collectionOfObjects($items);
    }

    public static function create($objectName, $attributes = []){
        $instance = static::init();
        $instance->setObjectName($objectName);
        $instance->getObjectFields();
        $attributes = (array) $attributes;
        $attributes = $instance->getCreatableFields($attributes);
        $response = $instance->client->getConnection()->create([$attributes], $objectName);
        return $response;
    }

    public static function update($objectName, $objectId, $attributes = []){
        $instance = static::init();
        $instance->setObjectName($objectName);
        $instance->getObjectFields();
        if(empty($objectId)){
            abort(500,'No Id Given');
        }
        $attributes = (array) $attributes;
        $attributes = $instance->getUpdatableFields($attributes);
        $attributes['Id'] = $objectId;
        $response = $instance->client->getConnection()->update([$attributes], $objectName);
        return $response;
    }

    public static function delete($objectName, $objectId){
        $instance = static::init();
        if(empty($objectId)){
            abort(500,'No Id Given');
        }
        //TODO
    }

    public static function object($objectName){
        $instance = static::init();
        $instance->setObjectName($objectName);
        $instance->getObjectFields();
        return $instance;
    }

    public function getObjectFields(){
        $parser = new WSDLParser();
        $wsdlFields = $parser
            ->setPath(base_path($this->wsdlPath))
            ->parseFieldsForType($this->objectName);
        $fields = [];
        $fields[] = new Field(['name'=>'Id','type'=>'id']);
        foreach($wsdlFields as $wsdlField){
            $fields[] = new Field($wsdlField);
        }
        $this->fields = $fields;
        $this->selectFields = [];
        foreach($this->fields as $field){
            if($field->selectable){
                $this->selectableFields[] = $field;
            }
        }
    }

    private function setObjectName($objectName){
        $this->objectName = $objectName;
    }

    public function find($id,$attributes=[]){
        $attributes['filters']['Id'] = $id;
        $attributes['limit'] = 1;
        $records = $this->get($attributes);
        if(!$records->isEmpty()){
            return $records->first();
        }
        return null;
    }

    public function where($field,$operator,$value){
        $this->filterArray[$field] = $value;
        return $this;
    }
    
    public function limit($limit){
        $this->limit = $limit;
        return $this;
    }
    
    public function select($fields){
        $this->selectArray = $fields;
        return $this;
    }

    public function get($attributes=[]){
        $this->setQueryAttributes($attributes);
        $query = new SalesforceQueryBuilder();
        $query->table($this->objectName);
        $query->select($this->getSelectForQuery());
        $query->where($this->getFiltersForQuery());
        $query->limit($this->limit);
        $sql = $query->generate();
        $queryResult = $this->client->query($sql);
        return $this->collectionOfObjects($queryResult->records);
    }
    
    public function query($sql){
        $instance = static::init();
        $queryResult = $instance->client->query($sql);
        return $instance->collectionOfObjects($queryResult->records);
    }

    public function collectionOfObjects($array){
        $objects = collect();
        foreach($array as $arrayItem){
            $object = new BaseObject($arrayItem);
            $objects->push($object);
        }
        return $objects;
    }

    public function setQueryAttributes($attributes){
        if(isset($attributes['select'])){
            $this->selectFields = $attributes['select'];
        }
        if(isset($attributes['filters'])){
            $this->filterArray = $attributes['filters'];
        }
        if(isset($attributes['limit'])){
            $this->limit = $attributes['limit'];
        }
    }

    public function getSelectForQuery(){
        if(!empty($this->selectArray)){
           return $this->selectArray;
        }
        $selectableFieldsArray = [];
        foreach($this->selectableFields as $selectableField){
            $selectableFieldsArray[] = $selectableField->name;
        }
        return $selectableFieldsArray;
    }

    public function getFiltersForQuery(){
        if(!empty($this->filterArray)){
            return $this->filterArray;
        }
        return [];
    }
    
    public function call(){
        $instance = static::init();
        return $instance->client->getConnection();
    }

}
