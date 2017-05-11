<?php

namespace KilroyWeb\Salesforce;

use KilroyWeb\Salesforce\Fields\Field;
use KilroyWeb\Salesforce\Objects\Object;
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

    public function get($attributes=[]){
        $this->setQueryAttributes($attributes);
        $query = new SalesforceQueryBuilder();
        $query->table($this->objectName);
        $query->select($this->getSelectForQuery());
        $query->where($this->getFiltersForQuery());
        if(isset($attributes['limit'])){
            $query->limit($attributes['limit']);
        }
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
            $object = new Object($arrayItem);
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
}
