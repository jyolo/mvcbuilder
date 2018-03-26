<?php
/**
 * MVC 生成器 辅助class 用于创建模型的时候 一些设置选项
 * Date: 2017/12/19
 * Time: 13:09
 */

namespace MvcBuilder;
use CMaker\Maker;
use think\Exception;

class MvcBuilderHelper extends CmakerSettingMap
{
    private $component_attr = '';
    private $component_name ;
    //允许的组件
    public $allowed_component = [
        'text',
        'textarea',
        'password',
        'number',
        'radio' ,
        'checkbox',
        'select',
        'daterange',
        'switchs',
        'hidden',
        'ueditor',
        'webuploader',
        'linkselect',
        'relation'
    ];
    //返回的设置
    public $set = [];
    private $setting ;

    /**
     * MvcBuilderHelper constructor.
     * @param $component_name 组件的名称
     * @param bool $setting_key 组件设置的key
     * @throws \ErrorException
     */
    public function __construct($component_name ,$setting_key = false)
    {
        $this->component_name = $component_name;
        //获取组件
        $class = Maker::getClass($component_name);

        if(!class_exists($class)) throw new \ErrorException('组件不存在');
        //获取组件的attr
        $this->component_attr = $class::attr();

        $this->set = $this->getSet($setting_key);

    }

    /**
     * 检查组件是否在MvcBuilder中被允许
     * @return bool
     */
    public function check_allowed_component($component_name){
        return in_array($component_name ,$this->allowed_component);
    }

    /**
     * 获取设置的dom
     * @return array
     */
    private function getSet($setting_key){
        $arr = [];

        if($setting_key != false){
            $this->setting = json_decode(cache($setting_key) , true);

            $arr['base'] = Maker::build('hidden')->name('setting_key')->value($setting_key)->render();
            $arr['base'] .= $this->base();
        }else{

            $arr['base'] = $this->base();

        }
        //固定值
        $arr['field'] = $this->field();
        $arr['verify'] = $this->verify();
        return $arr;
    }

    /**
     * 获取基础设置 动态根据组件的 attr 而变
     */
    private function base(){
        $setComponentsStatusAttr = self::tosplitKeyToValue(self::$setComponentsStatusAttr);
        //key的值分割 成值
        $map = self::tosplitKeyToValue(self::$map);
        $common_map = self::$common_map;

        //如果有设置选项
        if($this->setting['base']){

            foreach($this->setting['base'] as $k => $v){
                //根据设置选项 动态的改变 公共组件设置的 值
                //赋值公共主键 value 的
                if(isset($common_map[$k])){
                    $component_name = $common_map[$k]['component'];
                    $statusAttr = $setComponentsStatusAttr[$component_name];
                    $common_map[$k][$statusAttr] = $v;
                }

                /**
                 * 组件编辑之后。
                 * 基础设置的每一个 表单组件，默认值的组件的属性
                 */
                if(isset($map[$this->component_name][$k])){

                    $component_name = $map[$this->component_name][$k]['component'];

                    if(!isset($setComponentsStatusAttr[$component_name]))throw new Exception('请设置组件~值~对应的属性');
                    //根据预设的配置 获取组件 value 值得属性
                    $statusAttr = $setComponentsStatusAttr[$component_name];
                    $map[$this->component_name][$k][$statusAttr] = $v;
                }

            }
        }

        $str = '';
        //获取公共的设置选项
        foreach($common_map as $k => $v){
            $obj = Maker::build($v['component']);
            $obj->name('base['.$k.']');
            foreach ($v as $ck => $cv){
                if($ck == 'component')continue;

                $obj->$ck($cv);
            }
            $str .= $obj->render();
        }


        //获取组件自己的 设置选项
        if(isset($map[$this->component_name])){

            //获取组件特有的设置选项 组装表单
            foreach($map[$this->component_name] as $k => $v){


                $obj = Maker::build($v['component']);
                $obj->name('base['.$k.']');

                foreach($v as $ck => $cv){
                    if($ck == 'component')continue;
                    $obj->$ck($cv);
                }

                $str .= $obj->render();

            }
        }




        return $str;
    }

    /**
     * 获取数据库设置 固定的设置选项
     *
     */
    private function field(){
        $name = isset($this->setting['field']['name']) ? $this->setting['field']['name'] :'';

        $str = Maker::build('text')
            ->label('字段名称')
            ->value($name)
            ->helpinfo('多个值用" | "分隔')
            ->name('field[name]')
            ->layVerify('required')
            ->render();

        $type_value = self::$fieldType;
        $seleted = null;
        if(isset($this->setting['field']['type'])){
            foreach($type_value as $k => $v){
                if(strtoupper($k) == strtoupper($this->setting['field']['type'])){$seleted = strtoupper($k) ;}
            }
        }

        $field_map = self::tosplitKeyToValue(self::$fieldMap);
        if(isset($field_map[$this->component_name])){
            $map = $field_map[$this->component_name];
        }else{
            $map['type'] = $map['length'] = $map['defualt_value'] = '';
        }



        $str .= Maker::build('select')
            ->label('字段类型')
            ->helpinfo('多个值的时候均使用相同的字段类型')
            ->option($type_value)
            ->choose($seleted ? $seleted : $map['type'])
            ->name('field[type]')
            ->layVerify('required')
            ->render();


        $str .= Maker::build('text')
            ->label('字段长度')
            ->value(isset($this->setting['field']['length']) ? $this->setting['field']['length'] : $map['length'])
            ->helpinfo('字段长度')
            ->name('field[length]')
            ->render();

        $str .= Maker::build('text')
            ->label('字段默认值')
            ->value(isset($this->setting['field']['defualt_value']) ? $this->setting['field']['defualt_value'] : $map['defualt_value'])
            ->helpinfo('多个值用" | "分隔')
            ->name('field[defualt_value]')
            ->render();

        $str .= Maker::build('text')
            ->label('字段说明')
            ->value(isset($this->setting['field']['comment']) ? $this->setting['field']['comment'] : '')
            ->helpinfo('多个值用" | "分隔')
            ->name('field[comment]')
            ->render();


        return $str;
    }

    /**
     * 获取验证设置 固定的设置选项
     */
    private function verify(){


        $layui_verify = [
            'required'  => '必填项',
            'phone'  => '手机号',
            'email'  => '邮箱',
            'url'  => '网址',
            'number'  => '数字',
            'date'  => '日期',
            'identity'  => '身份证',
        ];
        $choose = isset($this->setting['verify']['layVerify']) ? $this->setting['verify']['layVerify'] : false;
        if($choose)$choose = join(',',$choose);

        $str = Maker::build('checkbox')
            ->label('layui验证规则')
            ->option($layui_verify)
            ->choose($choose)
            ->helpinfo('可以多选')
            ->name('verify[layVerify]')
            ->render();

//        $str .= Maker::build('text')
//            ->label('服务端验证规则')
//            ->value(isset($this->setting['verify']['serverVerfy']) ? $this->setting['verify']['serverVerfy'] : '')
//            ->helpinfo('多个值用" | "分隔')
//            ->name('verify[serverVerfy]')
//            ->render();

//        $str .= Maker::build('text')
//            ->label('服务端验证提示')
//            ->value(isset($this->setting['verify']['serverVerfy_msg']) ? $this->setting['verify']['serverVerfy_msg'] : '')
//            ->helpinfo('多个值用" | "分隔')
//            ->name('verify[serverVerfy_msg]')
//            ->render();

        return $str;
    }









}