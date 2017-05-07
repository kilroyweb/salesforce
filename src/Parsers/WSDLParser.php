<?php

namespace KilroyWeb\Salesforce\Parsers;

use File;
use SimpleXMLElement;

class WSDLParser extends BaseParser
{

    protected $path;
    protected $xmlNamespacePrefix='';

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function parse()
    {
        $contents = File::get($this->path);
        $xml = new SimpleXMLElement($contents);
        $namespaces = $xml->getDocNamespaces();
        if (isset($namespaces[''])) {
            $defaultNamespaceUrl = $namespaces[''];
            $xml->registerXPathNamespace('default', $defaultNamespaceUrl);
            $this->xmlNamespacePrefix = 'default:';
        }
        return $xml;
    }

    private function getObjects(){
        $xml = $this->parse();
        $xmlTypes = $xml->types;
        $xmlSchema = $xmlTypes->schema;
        return $xmlSchema->children();
    }

    private function getObject($type){
        $objects = $this->getObjects();
        foreach($objects as $object){
            if($object['name'] == $type){
                return $object;
            }
        }
        return false;
    }

    private function getFieldsFromObject($object){
        $fields = [];
        $objectSequence = $object->complexContent->extension->sequence;
        foreach($objectSequence->children() as $objectField){
            $fieldAttributes = [];
            foreach($objectField->attributes() as $key => $value){
                $fieldAttributes[$key] = (string) $value;
            }
            $fields[] = $fieldAttributes;
        }
        return $fields;
    }

    public function parseFieldsForType($type)
    {
        $object = $this->getObject($type);
        $fields = $this->getFieldsFromObject($object);
        return $fields;
    }

}