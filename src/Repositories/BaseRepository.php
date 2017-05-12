<?php

namespace KilroyWeb\Salesforce\Repositories;

abstract class BaseRepository{

    protected $objectName;

    public static function find($objectId){
        return static::query()->find($objectId);
    }

    public static function query(){
        $instance = new static;
        return \Salesforce::object($instance->objectName);
    }

    public static function create(array $attributes = []){
        $instance = new static;
        return \Salesforce::create($instance->objectName,$attributes);
    }

    public static function update($objectId, array $attributes = []){
        $instance = new static;
        return \Salesforce::update($instance->objectName,$objectId,$attributes);
    }

    public static function delete($objectId){
        $instance = new static;
        return \Salesforce::delete($instance->objectName,$objectId);
    }

}