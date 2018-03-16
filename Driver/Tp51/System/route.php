<?php

use MvcBuilder\Driver\Tp51\System\MvcBuilderController;
Route::rule('mvcbuilder/:action', function ($action,MvcBuilderController $builderController){
    return $builderController->$action();
});

