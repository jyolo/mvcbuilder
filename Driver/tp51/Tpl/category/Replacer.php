<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/27
 * Time: 10:55
 */

namespace MvcBuilder\Driver\tp51\Tpl\category;
use CMaker\components\checkbox;
use CMaker\Maker;
use MvcBuilder\ComponentSettingMap;
use MvcBuilder\Driver\tp51\CommonReplacer;
use think\Exception;

class Replacer extends CommonReplacer
{

    public static function _search_component_($models,$module){
        //预定义文本组件作为搜索项的时候
        $text_component = ['text','number','ueditor'];
        //预定义选择组件作为搜索项的时候
        $select_component = ['select','checkbox','radio','switchs','relation'];
        $str = "\t\t";


        foreach($models['component'] as $k => $v){
            $setting = json_decode($v['setting'] ,true);
            //没开启搜索的 直接跳过
            if(isset($setting['insearch']) && $setting['insearch'] != 'on') {

                continue;
            }else{
                if(in_array($v['component_name'] ,$select_component)){

                    if(in_array($v['component_name'] ,['select','radio','checkbox','switchs'])){


                        if($v['component_name'] == 'switchs') {
                            $option_setting = $setting['base']['text'];
                        }else{
                            $option_setting = $setting['base']['option'];
                        }

                        $str .= '{:CMaker("select")';
                        $str .= '->label(\''.$setting['base']['label'].'\')';
                        $str .= '->option(\''.$option_setting.'\')';
                        $str .= '->name(\'where['.$setting['field']['name'].']\')';
                        $str .= '->render()}'."\r\n";

                    }



                    if($v['component_name'] == 'relation'){
                        $str .= '{:CMaker("relation")';
                        foreach($setting['base'] as $sk => $sv){
                            $str .= '->'.$sk.'("'.$sv.'")';
                        }

                        //在category中 relation的搜索字段 是固定值 path
                        //$str .= '->name(\'where[path]\')';
                        $str .= '->name(\'where['.$setting['field']['name'].']\')';
                        $str .= '->render()}'."\r\n";

                    }


                }


                if(in_array($v['component_name'] ,$text_component)){
                    $str .= '{:CMaker("text")';
                    $str .= '->label(\''.$setting['base']['label'].'\')';
                    $str .= '->name(\'where['.$setting['field']['name'].']\')';
                    $str .= '->placeholder(\'请输入搜索的'.$setting['base']['label'].'\')';

                    $str .= '->render()}'."\r\n";
                }


            }



        }



        return $str;
    }
    public static function _table_($models,$module){


        $url = url($module['file_name'].'/'.$models['table_name'].'/index');

        $cols = '['."\r\n";
        $cols .=    '[\'type\'=>\'checkbox\'] ,'."\r\n";

        //显示模型的主键
        $cols .= '[\'field\' => "'.$models['primary_name'].'",\'title\' => \''.$models['primary_name'].'\',\'sort\' => true ],'."\r\n";

        $param = [];
        foreach($models['component'] as $k => $v){
            $setting = json_decode($v['setting'] ,true);
            //如果是关联选择 则通过table传递 关系字段
            if($v['component_name'] == 'relation'){
                $param['relation_field'] = $setting['base']['field'];
            }

            if(isset($setting['intable']) && $setting['intable'] == 'on'){
                $cols .= '[\'field\' => \''.$setting['field']['name'].'\',\'title\' => \''.$setting['base']['label'].'\',\'sort\' => true ] ,'."\r\n";
            }
        }

        $cols .= '[\'toolbar\' => \'#actionTpl\' ,\'title\' => \'操作\',\'fixed\' => \'right\']'."\r\n";
        $cols .= ']';



        $str = '{:CMaker("table")';
        $str .= '->filter("'.$models['table_name'].'")';
        $str .= '->cols('.$cols.')';
        $str .= '->page(true)';
        $str .= '->url(\''.$url.'\')';
        $param_arr = '';
        if(count($param)){
            $param_arr .= '[';
            foreach($param as $k => $v){
                $param_arr .= '\''.$k.'\'=>\''.$v.'\',';
            }
            $param_arr = trim($param_arr,',');
            $param_arr .= ']';
            $str .= '->param('.$param_arr.')';
        }


        $str .= '->render()';
        $str .= '}';


        return $str;

    }

    public static function _primary_name_($models){
        return $models['primary_name'];
    }

    public static function _add_form_component_($models){
        $component = '';
        foreach($models['component'] as $k => $v){
            $setting = json_decode($v['setting'] ,true);


            $component .= '{:CMaker(\''.$v['component_name'].'\')';

            foreach($setting['base'] as $sk => $sv){
                $component .= '->'.$sk.'(\''.$sv.'\')';
            }

            $component .= '->name(\''.$setting['field']['name'].'\')';

            $component .= '->layVerify(\''.$setting['verify']['layVerify'].'\')';

            $component .= '->render()}'."\r\n";

        }

        return $component;

    }

    public static function _notSetValueComponent_($models,$module){
        $funcStr = '';
        foreach($models['component'] as $k => $v){
            $setting = json_decode($v['setting'] ,true);
            $FieldName = strtolower($setting['field']['name']);

            switch($v['component_name']){
                case 'checkbox':
                    $funcStr .= <<<EOT
\t\tisset(\$post['{$FieldName}'])  ? \$post['{$FieldName}']: \$post['{$FieldName}'] = false;
EOT;

                    break;
                case 'switchs': //switch 组件 在关闭的时候是 没有值的

                    $funcStr .=  <<<EOT
\t\tisset(\$post['{$FieldName}'])  ? \$post['{$FieldName}']: \$post['{$FieldName}'] = '0';
EOT;
                    $funcStr .= "\n";

                    break;
            }

        }

        return $funcStr;
    }

    public static function _edit_form_component_($models){

        //获取组件值的属性
        $ComponentsStatusAttr = self::tosplitKeyToValue(self::$setComponentsStatusAttr);

        $component = '';
        foreach($models['component'] as $k => $v){

            $setting = json_decode($v['setting'] ,true);

            $component .= '{:CMaker(\''.$v['component_name'].'\')';

            //如果表单组件中并未设置 statusAttr属性 则抛出错误
            if(!isset($ComponentsStatusAttr[$v['component_name']]))throw new Exception($v['component_name'].' 未设置statusAttr属性');

            $attr = $ComponentsStatusAttr[$v['component_name']];



            foreach($setting['base'] as $sk => $sv){
                //当$sk == 设定的attr 的时候 跳过，避免重复
                if($sk == $attr)continue;
                $component .= '->'.$sk.'(\''.$sv.'\')';
            }
            $component .= '->'.$attr.'($vo[\''.$setting['field']['name'].'\'])';

            $component .= '->name(\''.$setting['field']['name'].'\')';

            $component .= '->layVerify(\''.$setting['verify']['layVerify'].'\')';

            $component .= '->render()}'."\r\n";

        }

        $component .= '{:CMaker(\'hidden\')->name(\''.$models['primary_name'].'\')->value($vo[\''.$models['primary_name'].'\'])->render()}';


        return $component;

    }


    public static function _setFieldNameAttr_($models,$module){

        $funcStr = '';
        foreach($models['component'] as $k => $v){
            $setting = json_decode($v['setting'] ,true);
            $FieldName = ucfirst($setting['field']['name']);

            switch($v['component_name']){
                case 'checkbox':
                    $option = json_encode(explode('|',$setting['base']['option']));
                    $funcStr .= <<<EOT
    function set{$FieldName}Attr(\$value){
        if(\$value != false)return join(',',\$value);
        return '';
    }
    //获取器 值得转化
    public function get{$FieldName}Attr(\$value)
    {
        if(strlen(\$value)){
            \$attr = explode(',',\$value);
            \$status = json_decode('{$option}',true);
            foreach(\$attr as \$k => \$v){
                \$attr[\$k] = \$status[\$v];
            }
            return join(',',\$attr);
        }
    }\r\n
EOT;
                    $funcStr .= "\n";
                    break;
                case 'switchs':
                    $option = json_encode(explode('|',$setting['base']['text']));

                    $funcStr .= <<<EOT
    //获取器 值得转化
    public function get{$FieldName}Attr(\$value)
    {
        \$status = json_decode('{$option}',true);
        if(\$value == 1)return \$status[0];
        return \$status[1];
    }\r\n
EOT;


                    break;
                case 'radio':
                case 'select':
                    $option = json_encode(explode('|',$setting['base']['option']));

                    $funcStr .= <<<EOT
    //获取器 值得转化
    public function get{$FieldName}Attr(\$value)
    {
        \$status = json_decode('{$option}',true);
        return \$status[\$value];
    }\r\n
EOT;
                    break;
                case 'relation':
                    $field = explode(',',$setting['base']['field']);
                    $funcStr .= <<<EOT
    //获取器 值得转化
    public function set{$FieldName}Attr(\$value)
    {
        if(!strlen(\$value)) return 0;
        return \$value;
    }\r\n
EOT;
                    break;
                case 'webuploader':
                    $funcStr .= <<<EOT
    function set{$FieldName}Attr(\$value){
        if(\$value != false){
            if(is_array(\$value)) return join(',',\$value);
            return \$value;
        }
        return '';
    }
    //获取器 值得转化
    public function get{$FieldName}Attr(\$value)
    {
        if(strlen(\$value)){
            \$attr = explode(',',\$value);
            return join(',',\$attr);
            
            
            //如果有需要 则可以显示成图片
            //\$str = '';
            //foreach(\$attr as \$k => \$v){
            //    \$str .= '<img src="'.\$v.'"\ width="80" height="80">';
            //}
            //return \$str;
            
            
        }
    }\r\n
EOT;

                    break;
            }

        }
        return $funcStr;
    }

    public static function _setPathAttr_($models,$module){
        $relation_field = '';
        //获取关系型字段
        foreach($models['component'] as $k => $v){
            if($v['component_name'] == 'relation'){
                $setting = json_decode($v['setting'] ,true);
                $relation_field = $setting['base']['field'];
            }
        }

        $field_arr = explode(',',$relation_field);
        $relation_field = $field_arr[1];
        $key_field = $field_arr[0];
        $str =<<<EOT
function setPathAttr(\$value,\$data){
        if(isset(\$data['{$relation_field}']) && strlen(\$data['{$relation_field}'])){

            if(\$data['{$relation_field}'] == 0 || \$data['{$relation_field}'] == null) return 0;
            \$parent_path = \$this->where('{$key_field}', \$data['{$relation_field}'])->value('path');
            \$parent_path = !strlen(\$parent_path) ? 0 : trim(\$parent_path ,',') ;
            return \$parent_path.','.\$data['{$relation_field}'] .',';

        }else{//排序的时候是没有赋值 parentid

            if(isset(\$data['{$key_field}']) && \$data['{$key_field}'] > 0){
                \$parent_path = \$this->where('{$key_field}' , \$data['{$key_field}'])->value('path');
                \$parent_path = !strlen(\$parent_path) ? 0 :\$parent_path ;
                return \$parent_path .',';
            }else{
                return 0;
            }

        }
    }
EOT;
            return $str;

    }


    public static function _validata_rule_(){
        return '\'\'';
    }
    public static function _validata_msg_(){
        return '\'\'';
    }
    public static function _validata_scene_(){
        return '\'\'';
    }



    public static function _CMakerJs_(){
        return '{:CMakerJs("all")}';
    }



}