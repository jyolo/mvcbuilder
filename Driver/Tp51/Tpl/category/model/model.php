<?php
namespace __app_namespace__\_module_name_\model;
use think\Model;
use think\Db;
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

    public function setPathAttr($value,$data){
            if(!isset($data['pid']))return $this->where('id', $data['id'])->value('path');
            if($data['_relation_field_'] == 0 || $data['_relation_field_'] == null) return '0,';
            $parent_path = $this->where('_primary_name_', $data['_relation_field_'])->value('path');
            $parent_path = trim($parent_path ,',');
            return $parent_path.','.$data['_relation_field_'] .',' ;

    }

    public function _save($post){

        $oldpath = $this->where('_primary_name_','=',$post['_primary_name_'])->value('path');
        $path = $this->setPathAttr($post['_relation_field_'],$post);
        //没有改变层级
        if($oldpath == $path)return $this->isUpdate(true)->save($post);

        $all_update_data = [];
        $son = $this->field('_primary_name_,path')
            ->where('','exp','instr(path,",'.$post['_primary_name_'].',")')
            ->select()->toArray();

        //组装子元素要更新的数据
        foreach($son as $k => $v){
            $new_path = str_replace($oldpath,$path ,$v['path']);
            $v['path'] = $new_path;
            $all_update_data[$k] = $v;
        }
        $parent_update = [
            '_primary_name_' => $post['_primary_name_'] ,
            '_relation_field_' => (intval($post['_relation_field_']) ?  $post['_relation_field_'] : 0) ,
            'path' => $path
        ];
        $parent_update = array_merge($post,$parent_update);
        //压入要更新parent的数据
        array_unshift($all_update_data , $parent_update );


        Db::startTrans();
        try{
            foreach($all_update_data as $k => $v){
                $flag = Db::table($this->getTable())->update($v);
                if(!$flag){
                    Db::rollback();
                    return false;
                }
            }
            Db::commit();
            return true;
        }catch (Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage());
            return false;
        }


    }
}