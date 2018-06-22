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
use think\facade\Request;
use think\Db;

/**
 * _models_zh_name_
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
            $post['page'] = (isset($post['page'])) ? $post['page'] : 1;
            $post['limit'] = (isset($post['limit'])) ? $post['limit'] : 10;

            $where = '';
            if(isset($post['where']))$where = _parseWhere($post['where']);

            $model = new model\_models_en_name_();

            $res = $model->where($where)->page($post['page'] ,$post['limit'])->order('add_time desc')->select()->toArray();

            //当前分页没有数据，但总数还是有的
            //fixed 第二页数据删除完了之后 layuitable reload 不会自动跳转到 第一页
            if(!count($res) && $model->where($where)->count($model->getPk()) > 0){
                $currpage = (($post['page'] - 1) >= 1) ? $post['page'] - 1 : 1 ;
                $res = $model->where($where)->page($currpage ,$post['limit'])->select()->toArray();
            }
            $return['data'] = $res;
            $return['count'] = $model->where($where)->count();
            $return['code'] = 0;

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
_notSelectTopCat_
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
_notSelectTopCat_
        $flag = $model->isUpdate(true)->save($post);
        $new_data = $model->find($post['_primary_name_'])->toArray();
        $flag ? $this->success('操作成功','',$new_data):$this->error('操作失败');
    }
    /**
     * 表格编辑
     */
    public function table_edit(){
        $post = input('post.');
        if(!isset($post['_primary_name_']) || !$post['_primary_name_'])$this->error('缺少 _primary_name_');

        $model = new model\_models_en_name_();
        $flag = $model->isUpdate(true)->save($post);
        $flag ? $this->success('操作成功'):$this->error('操作失败');

    }
    /**
     * 删除
     */
    public function del(){
        $post = input('post.');
        $model = new model\_models_en_name_();
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
        $flag = $model->where('_primary_name_','in',$post['ids'])->delete();
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }

    /**
     * 批量操作
     */
    public function batch(){
        $type = input('param.type');
        $value = input('param.value');
        $post = input('post.');
        if(!isset($post['ids']))$this->error('请选选择');

        $model = new model\_models_en_name_();
        $update = [$type => $value];
        $flag = Db::table($model->getTable())->where('_primary_name_','in',$post['ids'])->update($update);
        $flag ? $this->success('操作成功'):$this->error('操作失败');
    }

}