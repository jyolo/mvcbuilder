<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/8
 * Time: 20:13
 */

namespace app\_module_name_\controller;

use app\base\controller\Common;
use app\_module_name_\model;
use CMaker\Component;
use think\facade\Request;


/**
 * [menu]角色管理[/menu]
 */
class _models_en_name_ extends Common
{


    /**
     * 查看
     */
    public function index(){

        if(Request::isGet()){

            return $this->fetch();
        }

        if(Request::isPost()){
            $post = input('post.');
            $model = new model\_models_en_name_();

            $relation_field_str = '_relation_field_all_';
            $relation_field = explode(',',$relation_field_str);

            if(isset($post['where']) && count($post['where'])){

                $pid = (isset($post['where'][$relation_field[1]]) && $post['where'][$relation_field[1]])
                    ? $post['where'][$relation_field[1]] : false;

                if($pid){
                    $model = $model->where('','exp','instr(path ,"'.$pid.'")');
                    unset($post['where'][$relation_field[1]]);
                }
                $where = _parseWhere($post['where']);
                $model = $model->where($where);
            }

            $list = $model->select()->toArray();

            $config['treeData'] = $list;
            $config['field'] = $relation_field_str;

            $return['data'] = Component::get_tree_array($config,true);
            sort($return['data']);//从新排序下标从0开始，否则layui table 多选以及字段排序会出问题。
            $return['code'] = 0;
            $return['count'] = count($list);

            return json($return);


        }


    }
    /**
     * 添加
     */
    public function add(){
        return $this->fetch();
    }
    /**
     * 编辑
     */
    public function edit(){
        $id = intval(input('param._primary_name_'));
        if(!$id) $this->error('id 不存在');

        $model = new model\_models_en_name_();

        $data = $model->find($id);
        //获取原始数据
        $data = $data->getData();

        $this->assign('vo',$data);
        return $this->fetch();
    }

    /**
     * 保存添加
     */
    public function add_action(){
        $post = input('post.');
        $model = new model\_models_en_name_();
_notSetValueComponent_

        $flag = $model->isUpdate(false)->save($post);
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }
    /**
     * 保存编辑
     */
    public function edit_action(){
        $post = input('post.');
        $model = new model\_models_en_name_();
_notSetValueComponent_

        if($post['_primary_name_'] == $post['_relation_field_']) $this->error('上级不可以是自己');

        $flag = $model->isUpdate(true)->save($post);
        $new_data = $model->find($post['_primary_name_'])->toArray();
        $flag ? $this->success('操作成功','',$new_data):$this->error('操作失败');
    }
    /**
     * 删除
     */
    public function del(){
        $post = input('post.');
        $model = new model\_models_en_name_();

        $childs = $model->where('','exp','instr(path,",'.$post['_primary_name_'].',")')->select();
        if(count($childs) > 0)$this->error('请先删除子类');

        $flag = $model->where('_primary_name_','=',$post['_primary_name_'])->delete();
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }

    /**
     * 批量删除
     */
    public function pdel(){
        $post = input('post.');
        $model = new model\_models_en_name_();

        if(!isset($post['ids']))$this->error('请选选择');
        $list = $model->where('_primary_name_','in',$post['ids'])->select()->toArray();
        foreach($list as $k => $v){
            $childs = $model->where('','exp','instr(path,",'.$v['_primary_name_'].',")')->count();
            if($childs > 0)$this->error('请先删除子类');
        }

        $flag = $model->where('_primary_name_','in',$post['ids'])->delete();
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }


}