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
     * [menu]查看[/menu]
     */
    public function index(){

        if(Request::isGet()){

            return $this->fetch();
        }

        if(Request::isPost()){
            $post = input('post.');
            $model = new model\_models_en_name_();

            if(isset($post['relation_field']) && strlen($post['relation_field'])){

                $relation_field = explode(',',$post['relation_field']);

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
                $config['field'] = $post['relation_field'];

                $list = Component::get_tree_array($config);

                $list = Component::tree_to_array($list,$config['field'],true);

                sort($list);
                $return['data'] = [];
                foreach($list as $k => $v){
                    if($k == 0 && $v[$relation_field[1]] == 0 ){
                        $pid = explode(',',trim($v['path'],','));
                        $v[$relation_field[1]] = array_pop($pid);
                    }

                    unset($v['son']);
                    $return['data'][] = $v;
                }
                $return['code'] = 0;
                $return['count'] = count($list);

                return json($return);
            }else{
                $return['code'] = 1;
                $return['data'] = [];
                $return['error_msg'] = '缺少relation_field参数';
                return json($return);
            }


        }


    }
    /**
     * [menu]添加[/menu]
     */
    public function add(){
        return $this->fetch();
    }
    /**
     * [menu]编辑[/menu]
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
     * [menu]保存添加[/menu]
     */
    public function add_action(){
        $post = input('post.');
        $model = new model\_models_en_name_();
_notSetValueComponent_
        $flag = $model->isUpdate(false)->save($post);
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }
    /**
     * [menu]保存编辑[/menu]
     */
    public function edit_action(){
        $post = input('post.');
        $model = new model\_models_en_name_();
_notSetValueComponent_
        $flag = $model->isUpdate(true)->save($post);
        $new_data = $model->find($post['_primary_name_'])->toArray();
        $flag ? $this->success('操作成功','',$new_data):$this->error('操作失败');
    }
    /**
     * [menu]删除[/menu]
     */
    public function del(){
        $post = input('post.');
        $model = new model\_models_en_name_();
        $flag = $model->where('_primary_name_','=',$post['_primary_name_'])->delete();
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }

    /**
     * [menu]批量删除[/menu]
     */
    public function pdel(){
        $post = input('post.');
        $model = new model\_models_en_name_();
        if(!isset($post['ids']))$this->error('请选选择');
        $flag = $model->where('_primary_name_','in',$post['ids'])->delete();
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }


}