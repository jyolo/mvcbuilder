<?php
/**
 * 公共的替换class
 * Time: 13:58
 */

namespace MvcBuilder\Driver\Tp51;


use MvcBuilder\CmakerSettingMap;
use think\Db;

class CommonReplacer extends CmakerSettingMap
{

    public static function _module_name_($models,$module){
        return $module['file_name'];
//        if(strpos($module['file_name'],'_')){
//            $arr = explode('_',$module['file_name']);
//            $str = '';
//            foreach($arr as $k => $v){
//                $str .= ucfirst($v);
//            }
//            return $str;
//        }else{
//            return ucfirst($module['file_name']);
//        }

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


    public static function _batch_component_button_($module ,$models){
        $controller = self::_models_en_name_($module);

        $html = '';
        foreach($module['component'] as $k => $v){
            $set = json_decode($v['setting'] ,true);
            if(!isset($set['isbatch']) || $set['isbatch'] != 'on'  || $v['component_name'] != 'switchs')continue;
            $filed_name = $set['field']['name'];

            $open_action_url = "{:url('{$controller}/batch',['type' => '{$filed_name}' ,'value' => \"{$set['base']['onvalue']}\" ])}";
            $off_action_url = "{:url('{$controller}/batch',['type' => '{$filed_name}' ,'value' => \"{$set['base']['offvalue']}\"])}";

            $find = ['是否'];
            $replace = '';
            $text = str_replace($find,$replace ,$set['base']['label']);

            $html .=<<<EOT
    <div class="layui-btn-group">
        <button class="layui-btn layui-btn-sm" lay-submit data-url="{$open_action_url}" data-call-back="reload_table" >
            {$text}
        </button>
        <button class="layui-btn layui-btn-sm" lay-submit data-url="{$off_action_url}" data-call-back="reload_table" >
            取消{$text}
        </button>
    </div>
EOT;

        }

        return $html;
    }



}


