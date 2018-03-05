<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/27
 * Time: 9:36
 */

namespace MvcBuilder\Core;


use MvcBuilder\MvcBuilder;

class ContentBuilder extends MvcBuilder
{

    private static $tplPath = null; //driver 的 tpl 路径
    private static $info ; //要处理的文件的信息
    private static $notReplaceMethod = ['tosplitKeyToValue'];

    public static function create($info){
        if(self::$tplPath == null)self::$tplPath = self::getTplPath();

        self::$info = $info;
        $content = self::getContent();
        file_put_contents($info['file'],$content);
    }

    public static function getContent(){

        $tplFile = self::getTplPlanFile();
        //替换者
        $replacer = self::getReplacer();
        //内容来源
        $content = file_get_contents($tplFile);

        $content = str_replace($replacer['find'],$replacer['replace'],$content);
//        p($content);
//        die();
        return $content;
    }

    /**
     * 根据调用的驱动Handler 获取 handler 下的tpl
     * @return string
     */
    private static function getTplPath(){

        $func = new \ReflectionClass(self::$_instance);
        $classPath = $func->getFileName();

        $arr = explode('\\',$classPath);
        array_pop($arr);
        $path = join('\\' ,$arr).DIRECTORY_SEPARATOR.'Tpl';

        if(!file_exists($path)) throw new \Exception($path.'不存在');

        return $path;

    }

    /**
     * 获取替换者
     */
    private static function getReplacer(){

        $handler = get_class(self::$_instance);
        $arr = explode('\\',$handler);
        array_pop($arr);
        $handlerRootNameSpace = join('\\',$arr);

        $Replacer = $handlerRootNameSpace.DIRECTORY_SEPARATOR.'Tpl'.DIRECTORY_SEPARATOR.self::$info['tpl_plan'].DIRECTORY_SEPARATOR.'Replacer';

        if(!class_exists($Replacer))throw new \Exception($Replacer.' ,replacer不存在');

        $methods = get_class_methods($Replacer);

        $arg = [];
        foreach($methods as $k => $v){
            //跳过不是替换替换方法的 method
            if(in_array($v,self::$notReplaceMethod)) continue;
            $arg['find'][$k] = $v;
            $arg['replace'][] = $Replacer::$v(self::$info['models_info'] ,self::$data );
        }

        return $arg;
    }

    /**
     * 获取模板方案路径并检查模板方案是否存在
     */
    private static function getTplPlanFile(){

        $path = self::$tplPath.DIRECTORY_SEPARATOR.self::$info['tpl_plan'];

        if(!file_exists($path))throw new \Exception($path.' 方案不存在');

        $suffix = self::$_instance->config['suffix'][self::$info['tpl_type']];

        switch (self::$info['tpl_type']){
            case 'view':
                $file = $path.DIRECTORY_SEPARATOR.self::$info['tpl_type'].DIRECTORY_SEPARATOR.self::$info['view_file'].$suffix;
                break;
            default:
                $file = $path.DIRECTORY_SEPARATOR.self::$info['tpl_type'].DIRECTORY_SEPARATOR.self::$info['tpl_type'].$suffix;
                break;
        }

        if(!file_exists($file))throw new \Exception( $file.'tpl 不存在');

        return $file;
    }


}