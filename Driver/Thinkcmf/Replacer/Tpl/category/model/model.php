<?php
namespace app\_module_name_\model;
use think\Model;

/**
 * 自动化模型的model模板文件
 */
class _models_en_name_ extends Model
{

    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段
    protected $auto = ['path'];

    _setFieldNameAttr_

    _setPathAttr_


}