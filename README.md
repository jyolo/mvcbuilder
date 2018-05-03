# mvcbuilder

#快速开始 
1: composer require jyolo/mvcbuilder 

2: 导入mvcbuilder.sql

3: 在当前框架添加对应的路由映射到System对应的框架目录里面的 controller

    这里以tp5.1 做示例：
    注意:如果访问返回的是字符串，修改tp51 config/app.php  -> default_return_type => 'html'

        use MvcBuilder\System\Tp51\MvcBuilderController;
        Route::rule('mvcbuilder/:action', function ($action,MvcBuilderController $builderController){
            return $builderController->$action();
        });
        
4:开始访问 http:://域名/index.php/mvcbuilder/index 开始使用

