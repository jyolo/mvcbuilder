<?php

use MvcBuilder\System\tp51\MvcBuilderController;
Route::rule('mvcbuilder/:action', function ($action,MvcBuilderController $builderController){
    return $builderController->$action();
});

