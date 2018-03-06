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
        $driver_called = explode('\\',$self)[0].'\Driver\\'.$driver_name.'\Handler';

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

        $tpl_path = __DIR__ .'\Driver\\'.$driver_name.'\Tpl';

        //检查驱动是否继承了core 里面的driver
        if(!file_exists($tpl_path))throw new \ErrorException($tpl_path .'不存在');
        $arr = array_slice(scandir($tpl_path) ,2);
        $arg = [];
        foreach($arr as $k=>$v){
            $arg[$v] = $v;
        }
        return $arg;
    }



}