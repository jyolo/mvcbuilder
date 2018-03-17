<?php

use MvcBuilder\Driver\ThinkCmf\System\MvcBuilderController;
Route::rule('mvcbuilder/:action', function ($action,MvcBuilderController $builderController){
    return $builderController->$action();
});

