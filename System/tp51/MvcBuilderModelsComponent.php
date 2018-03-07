<?php
// +----------------------------------------------------------------------
// | jyolo/mvcbuilder
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2028 http://i3tp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: jyolo <364851534@qq.com>
// +----------------------------------------------------------------------
namespace MvcBuilder\System\tp51;


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