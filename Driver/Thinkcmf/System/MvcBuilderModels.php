<?php
/**
 * Created by PhpStorm.
 * User: jyolo
 * Date: 2017/2/13
 * Time: 11:07
 */

namespace MvcBuilder\Driver\Thinkcmf\System;


use think\Model;
use think\facade\Session;
use think\Db;
use app\base\library\TableMaker;

class MvcBuilderModels extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'jy_models';

    protected $autoWriteTimestamp = 'datetime';//开启自动写入时间字段
    protected $createTime = 'add_time'; //定义创建时间字段
    protected $updateTime = 'update_time'; //定义更新时间字段


    public function getList($post){

        $res = $this->page($post['page'] ,$post['limit'])->select()->toArray();

        //当前分页没有数据，但总数还是有的
        //fixed 第二页数据删除完了之后 table reload 不会跳转到 第一页
        if(!count($res) && $this->count() > 0){
            $currpage = (($post['page'] - 1) >= 1) ? $post['page'] - 1 : 1 ;
            $res = $this->page($currpage ,$post['limit'])->select()->toArray();
        }

        return $res;
    }

    public function _del($id){
        Db::startTrans();

        $table_name = $this::get($id)->table_name;

        $flag = $this::destroy($id);

        if(!$flag){
            $this->error = '删除models数据失败';
            Db::rollback();
            return false;
        }
        //删除字段表信息
        $res = MvcBuilderModelsComponent::destroy(['models_id' => $id]);

        if(!$flag){
            $this->error = '删除models_fields失败';
            Db::rollback();
            return false;
        }
        Db::commit();

        return self::drop_table(config('database.prefix').$table_name);

    }

    /**
     * 删除表
     * @return bool|string
     */
    public static function drop_table($table_name){
        $sql = 'DROP TABLE IF EXISTS `'.$table_name.'`';

        $flag = Db::execute($sql);

        return true;
    }

}