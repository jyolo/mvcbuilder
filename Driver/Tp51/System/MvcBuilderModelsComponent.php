<?php
/**
 * Created by PhpStorm.
 * User: jyolo
 * Date: 2017/2/13
 * Time: 11:07
 */

namespace MvcBuilder\Driver\Tp51\System;


use think\Model;
use think\Request;
use think\Session;

class MvcBuilderModelsComponent extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'jy_models_component';

    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段
    




}