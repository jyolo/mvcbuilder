<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/26
 * Time: 10:32
 */

namespace MvcBuilder\Core;


use MvcBuilder\MvcBuilder;
use think\Db;

abstract class Driver extends MvcBuilder
{

    protected $config = [
        'app_path' => APP_PATH, //应用的路径
        'view_path' => THEME_PATH, //视图的路径
        'folder' => [ //目录的结构
            '__file__'   => ['common.php'],
            '__dir__'    => ['controller', 'model', 'validate' , 'view'  ], //视图文件加,通常会有一些默认的页面
        ],
        'view_file' => ['index','add','edit'], //视图默认生成的文件
        'suffix' => [
            'controller' => '.php',
            'model' => '.php',
            'validate' => '.php',
            'view' => '.html',
        ],
    ];
    /**
     * 创建目录文件地图 数组 下标字符串 为 目录， 下标数字 为 文件
     * @return array
     */
    abstract function makeFolderMap();
    /**
     * 根据目录文件创建文件
     * @return array
     */
    abstract function buildFile($foldermap);


    /**
     *  获取models
     * @param $model_ids array
     * @return mixed
     */
    protected function get_models($model_ids){
        $field = 'id,module_id,models_name,table_name,tpl_plan,primary_name,primary_type,primary_length,status';
        $models = Db::name('models')
            ->field($field)
            ->where('id','in',$model_ids)
            ->select();
        if(!$models)throw new \Exception('模型不存在');
        //获取模型的所有组件
        foreach($models as $k => $v){
            $models[$k]['component'] = Db::name('models_component')
                ->field('id as cid,component_name,setting')
                ->where('models_id','eq',$v['id'])
                ->order('sorts asc')
                ->select();
        }

        return $models;
    }








}