<?php

namespace KilroyWeb\Salesforce\Facades;

use Illuminate\Support\Facades\Facade;

class Salesforce extends Facade{

    protected static function getFacadeAccessor() {
        return 'salesforce';
    }

}