<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/27
 * Time: 9:36
 */

namespace MvcBuilder\Core;


use MvcBuilder\MvcBuilder;
use think\Db;
use think\Exception;

class MenuBuilder extends MvcBuilder
{
    public $defualt_icon = 'fa-circle'; //默认的图标
    protected $unaccept_method = ['__construct','_initialize'];//排序Controller的一些方法

    /**
     * 获取基本反射信息，包含所有的method 的第一行的中文注释 作为菜单名称
     * @param $post string module ,array models_id . example : module => rfid ,models_id[0] => 8 models_id[1] => 10
     * @return array
     */
    public function get_base_info($post,$from = 'models'){

        switch ($from){
            case 'models':

                $models_ids = join(',',$post['models_id']);
                $models = Db::name('models')->field('models_name as menu_name,table_name as controller')->where('id in('.$models_ids.')')->select();
                foreach($models as $k => $v){
                    $models[$k]['app_namespace'] = $app_namespace = env('app_namespace').'\\'.$post['module'].'\\'.config('app.url_controller_layer');
                    $models[$k]['module'] = $post['module'];
                }
                break;
            case 'data':
                $models = $post;
                if(!count($models))throw new Exception('数据为空');
                foreach($models as $k => $v){
                    if(!isset($v['module']) || !strlen($v['module']))throw new Exception('数据中缺少 module');
                    $models[$k]['app_namespace'] = $app_namespace = env('app_namespace').'\\'.$v['module'].'\\'.config('app.url_controller_layer');
                }
                break;
        }


        foreach ($models as $k => $v){
            $file_name = $this->tablename_to_filename($v['controller']);
            $class = $v['app_namespace'].'\\'.$file_name;
            if(!class_exists($class)) throw new Exception($class.' 不存在请先生成文件');

            $data[$k] = $v;
            $data[$k]['namespace'] = $class ;
            $data[$k]['icon'] = (isset($post['icon']) && $post['icon']) ? $post['icon'] : $this->defualt_icon ;
            $data[$k]['filename'] = $file_name;


            $rc = new \ReflectionClass($class);
            $methods = get_class_methods($class);
            //排除不接受的方法
            $methods = array_diff($methods, $this->unaccept_method);

            foreach($methods as $sk => $sv){
                $note = $rc->getMethod($sv)->getDocComment();
                $note = $this->get_frist_note($note);

                $data[$k]['action'][$sk] = ['method' => $sv ,'note' => $note];
            }

        }

        return $data;


    }

    /**
     * 表名转换成文件名称
     * @param $tablename
     * @return string
     */
    public function tablename_to_filename($tablename){
        $name = explode('_',$tablename);
        $str = '';
        foreach($name as $k => $v){
            $str .= ucfirst($v);
        }
        return $str;
    }

    /**
     * 获取第一行的注释
     */
    public function get_frist_note($note){
        // / * 替换成空
        $note = preg_replace("/\/|\*|/",'',$note);
        //去除首尾空白字符
        $note = trim($note);
        //已换行分割成数组
        $arr =  explode("\r\n",$note);
        // 替换掉所有空白字符
        $str = preg_replace('/\s+/','',$arr[0]);
        return $str;
    }

}