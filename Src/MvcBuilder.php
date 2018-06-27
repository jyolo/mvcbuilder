<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 10:19
 */

namespace MvcBuilder;


class MvcBuilder
{
    public static $_instance = null;
    public static $data;
    public static $error = false;


    /**
     * 实例化驱动
     * @param $class_name
     * @return null
     * @throws \ErrorException
     */
    public static function init($driver_name,$post = []){
        $self = get_class();
        //拓展的生成方案
        $exntend_Handler = '\MvcbuilderDriver\\'.$driver_name.'\Handler';
        //优先使用拓展的生成方案
        if(class_exists($exntend_Handler)){
            $driver_called = $exntend_Handler;
        }else{
            $driver_called = explode('\\',$self)[0].'\Driver\\'.$driver_name.'\Handler';
        }

        //检查驱动是否继承了core 里面的driver
        if(get_parent_class($driver_called) != explode('\\',$self)[0].'\Core\Driver') throw new \ErrorException($driver_called .'驱动未定义');

        //单列化 驱动
        if(is_null(self::$_instance)){
           self::$_instance = new $driver_called;
        }

        //赋值
        self::$data = $post;

        return self::$_instance;
    }

    /**
     * 实例化核心类
     * @param $class_name
     * @return null
     * @throws \ErrorException
     */
    public static function core($class_name){
        //$obj= new \ReflectionClass();
        $self = get_class();

        $called = 'MvcBuilder\Core\\'.$class_name;
        //检查是否为子类
        if(!is_subclass_of($called ,$self))throw new \ErrorException($called .'未定义');

        if(is_null(self::$_instance)){
            self::$_instance = new $called;
        }
        return self::$_instance;
    }
    /**
     * 获取生成方案
     */
    public static function getTplPlan($driver_name){
        $self = get_class();

        $extend_handler = '\MvcbuilderDriver\\'.$driver_name.'\Handler';
        if(class_exists($extend_handler))
        {
            $ref = new \ReflectionClass($extend_handler);
            $handler_path = $ref->getFileName();
            $arr = explode('\\',$handler_path);
            array_pop($arr);
            $tpl_path = join('\\',$arr).'\\tpl';
            $handler = new $extend_handler;
            if(!file_exists($tpl_path))throw new \Exception($driver_name.'拓展的Replacer 的 Tpl 不存在');
        }
        else
        {
            $self = get_class();
            $driver_called = explode('\\',$self)[0].'\Driver\\'.$driver_name.'\Handler';
            $tpl_path = __DIR__ .'/../'.DIRECTORY_SEPARATOR.'Driver'.DIRECTORY_SEPARATOR. $driver_name . DIRECTORY_SEPARATOR .'Tpl';
            
            $handler = new $driver_called;

            if(!file_exists($tpl_path))throw new \Exception($tpl_path .'不存在');
        }



        $arr = array_slice(scandir($tpl_path) ,2);

        $handler_tpl_map = property_exists($handler ,'tpl') ? $handler->tpl : false;

        $arg = [];
        foreach($arr as $k=>$v){
            //有定义 文件夹的名称 则使用名称，没定义则使用文件名字
            if(isset($handler_tpl_map[$v]) && $handler_tpl_map[$v]){
                $arg[$v] = $handler_tpl_map[$v];
            }else{
                $arg[$v] = $v;
            }
        }

        return $arg;
    }



}