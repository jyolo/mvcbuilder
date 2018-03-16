<?php
/**
 * 公共的替换class
 * Time: 13:58
 */

namespace MvcBuilder\Driver\Thinkcmf\Replacer;


use MvcBuilder\CmakerSettingMap;

class CommonReplacer extends CmakerSettingMap
{

    public static function _module_name_($models,$module){

        if(strpos($module['file_name'],'_')){
            $arr = explode('_',$module['file_name']);
            $str = '';
            foreach($arr as $k => $v){
                $str .= ucfirst($v);
            }
            return $str;
        }else{
            return ucfirst($module['file_name']);
        }

    }

    public static function _models_zh_name_($models){
        return ucfirst($models['models_name']);
    }

    public static function _models_en_name_($models){
        if(strpos($models['table_name'],'_')){
            $arr = explode('_',$models['table_name']);
            $str = '';
            foreach($arr as $k => $v){
                $str .= ucfirst($v);
            }
            return $str;
        }else{
            return ucfirst($models['table_name']);
        }
    }






}


