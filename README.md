1：不依赖任何框架。

2：可视化拖拽生成表单模型，一键生成MVC文件和对应的curd等功能

3：灵活的拓展性。自定义生成方案。curd等功能 ，写一次即可封装成生成方案，无需做重复的工作。

快速开始 

1：composer require jyolo/mvcbuilder 

2：导入mvcbuilder.sql

3：复制 static 静态文件 到项目的根目录 

4：在当前框架添加对应的路由映射到System对应的框架目录里面的 controller

    这里以tp5.1 做示例：
    
    use MvcBuilder\System\Tp51\MvcBuilderController;
    Route::rule('mvcbuilder/:action', function ($action,MvcBuilderController $builderController){
        return $builderController->$action();
    });
        
5：开始访问 http:://域名/index.php/mvcbuilder/index 开始使用  

目前只适配thinkphp5.1。后期会考虑为laravel yii ci 等框架适配。
注意： 如果访问返回的是字符串，修改 config/app.php 配置文件  default_return_type => 'html'

文档地址：https://www.kancloud.cn/jyolo/atcmf/626651 （持续完善中...）

