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
use MvcBuilder\Driver\tp51\Tpl\normal\Replacer;
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
            $post['page'] = (isset($post['page'])) ? $post['page'] : 1;
            $post['limit'] = (isset($post['limit'])) ? $post['limit'] : 10;

            $where = '';
            if(isset($post['where']))$where = _parseWhere($post['where']);

            $model = new model\_models_en_name_();

            $return['data'] = $model->where($where)->page($post['page'],$post['limit'])->select()->toArray();
            $return['count'] = $model->where($where)->count();
            $return['code'] = 0;

            return json($return);

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