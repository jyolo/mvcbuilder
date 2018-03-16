<?php
/**
 * Created by PhpStorm.
 * User: jyolo
 * Date: 2017/2/13
 * Time: 11:07
 */

namespace MvcBuilder\Driver\Tp51\System;


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

    protected $auto = ['manager_id','manager_name'];//新增的时候自动完成

    //自动完成
    protected function setManagerIdAttr()
    {
        $manager = Session::get('manager');

        return $manager['id'];
    }
    //自动完成
    protected function setManagerNameAttr()
    {
        $manager = Session::get('manager');
        return $manager['login_name'];
    }

    public function getList($post){

        $res = $this->page($post['page'] ,$post['limit'])->select()->toArray();
        //当前分页没有数据，但总数还是有的
        //fixed 第二页数据删除完了之后 table reload 不会跳转到 第一页
        if(!count($res) && $this->count($this->pk) > 0){
            $currpage = (($post['page'] - 1) >= 1) ? $post['page'] - 1 : 1 ;
            $res = $this->page($currpage ,$post['limit'])->select()->toArray();
        }
        return $res;
    }


    public function _add($post,$field_info)
    {
        Db::startTrans();

        //写入models 表
        $flag = $this
            ->isUpdate(false)
            ->allowField(true)
            ->save($post);

        if(!$flag){
            Db::rollback();
            $this->error = '新增models失败';
            return false;
        }

        //写入models_fields表
        $mfields = new ModelsFields();

        $flag = $mfields->_saveAll($this->id ,$field_info);

        if(!$flag){
            Db::rollback();
            $this->error = $mfields->getError();
            return false;
        }

        //创建表
        $flag = TableMaker::create($field_info);
        if(!$flag){
            Db::rollback();
            $this->error = TableMaker::getError();
            return false;
        }


        Db::commit();
        return true;
    }


    public function _save($post ,$field_info){
        Db::startTrans();

        //组装update数据 和 add数据
        $update = $add = $add_field = $up_field = [];

        foreach($field_info as $k =>$v){
            if(isset($v['id']) && intval($v['id']) > 0){
                $update[$k] = $v;
                $up_field[$k]['name_en'] = $v['name_en'];
                $up_field[$k]['comment'] = $v['comment'];
                $up_field[$k]['type'] = $v['type'];
                $up_field[$k]['length'] = $v['length'];
            }else{
                
                $keys = array_keys($v);
                if(in_array('id',$keys))unset($v['id']);
                if(!in_array('modeis_id',$keys))$v['models_id'] = $post['models_id'];
                $add[$k] = $v;
                $add_field[$k]['name_en'] = $v['name_en'];
                $add_field[$k]['comment'] = $v['comment'];
                $add_field[$k]['type'] = $v['type'];
                $add_field[$k]['length'] = $v['length'];
            }


        }
        sort($update);
        sort($add);
        //先执行更新字段和添加字段
        if($add_field){
            $flag = TableMaker::add_field($post['table_name'],$add_field);
            if(!$flag){
                $this->error = '新增字段失败,请检查是否有重名的字段';
                return false;
            }
        }

        if($up_field){
            $flag = TableMaker::update_field($post['table_name'],$up_field);
            
            if(!$flag){
                $this->error = '更新字段失败';
                return false;
            }
        }

        //更新字段信息
        $mfields = new ModelsFields();

        if($update){
            $flag = $mfields->saveAll($update);
            if(!toArray($flag)){
                Db::rollback();
                $this->error = '更新models_fields失败';
                return false;
            }
        }
        if($add){
            $flag =  $mfields->saveAll($add);
            if(!toArray($flag)){
                Db::rollback();
                $this->error = '新增models_fields失败';
                return false;
            }
        }

        //写入models 表
        $flag = $this
            ->isUpdate(true)
            ->allowField(true)
            ->save($post,['id' => $post['models_id']]);
        if(!$flag){
            Db::rollback();
            $this->error = '更新models失败';
            return false;
        }



        Db::commit();
        return true;


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