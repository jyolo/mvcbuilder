<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 10:19
 */

namespace MvcBuilder\Core;

use MvcBuilder\MvcBuilder;
use CMaker\Maker;
use think\Db;
use think\Exception;


class SqlBuilder extends MvcBuilder
{
    //自动写入的字段
    public $autoInsertField = [
        [
            'name' => 'listorder',
            'type' => 'int',
            'length' => '11',
            'defualt_value' => '0',
            'comment' => '排序',
        ],
        [
            'name' => 'add_time',
            'type' => 'datetime',
            'length' => '',
            'defualt_value' => '',
            'comment' => '添加时间',
        ],
        [
            'name' => 'update_time',
            'type' => 'datetime',
            'length' => '',
            'defualt_value' => '',
            'comment' => '更新时间',
        ],
    ];

    /**
     * 设置自动写入的字段
     */
    public function setAutoField($fields){
        if(is_array($fields) && count($fields)){
            $this->autoInsertField =  $fields;
        }
    }

    /**
     * 初始化数据
     */
    public function initData($post){

        //检查未设置的组件
        if(!$this->check_unsetting_component($post)) return self::$_instance;

        $data['models_name'] = $post['models_name'];
        $data['table_name'] = $post['table_name'];
        if(isset($post['models_id']) && intval($post['models_id'])){
            $data['models_id'] = intval($post['models_id']);
        }

        $data['tpl_plan'] = $post['tpl_plan'];

        $data['primary']['name'] = $post['primary']['name'] ? $post['primary']['name'] : null;
        $data['primary']['type'] = $post['primary']['type'] ? $post['primary']['type'] : null;
        $data['primary']['length'] = $post['primary']['length'] ? $post['primary']['length'] : null;

        $new_post = [];
        foreach($post['setting'] as $k => $v){

            if(!cache($v)){
                self::$error = '第'.($k+1).'个'.$post['component_name'][$k].'请先设置组件';
                return self::$_instance;
            }

            $arg = json_decode(cache($v),true);

            //删除无用的值
            if(isset($arg['setting_key']) && $arg['setting_key']) unset($arg['setting_key']);


            $arg['component_name'] = $post['component_name'][$k];

            $new_post[$k] = $arg;

            //编辑的id
            if(isset($post['component_id'][$k]) && intval($post['component_id'][$k])) {
                $new_post[$k]['component_id'] = $post['component_id'][$k];
            }

        }



        $data['component'] = $new_post;

        self::$data = $data;

        return self::$_instance;
    }

    /**
     * 执行创建
     * @return bool
     */
    public function create(){
        if(self::$error != false) return self::$_instance;

        //创建表
        $flag = $this->makeTable();
        if($flag){
            $flag = $this->insertModels();
            //写入失败的时候 直接删除创建的表 人工回滚
            if(!$flag)$this->dropTable();

            return true;
        }

    }

    /**
     * 执行更新
     * @return bool
     */
    public function update(){
        if(self::$error != false) return false;

        //更新models信息以及表,主键的信息
        if(!$this->updateModels())return false;

        return $this->updateComponent();

    }

    /**
     * 更新组件
     */
    private function updateComponent(){
        //p(self::$data['component']);
        $info = Db::table('jy_models_component')->where('models_id' ,self::$data['models_id'])->select();

        $up['new'] = $up['update'] = $up['del'] = [];
        //组装 新增，更新 ，删除的组件数据
        foreach(self::$data['component'] as $k => $v){


            foreach($info as $ik => $iv){
                $old_cid['ids'][$ik] = $iv['id'];
                $old_cid['info'][$iv['id']] = $iv;

                $oldfield = json_decode($iv['setting'] ,true);
                if(isset($v['component_id']) && $v['component_id'] == $iv['id']){
                    //设置字段更新标志
                    if(count(array_diff_assoc($v['field'] ,$oldfield['field']))) $v['up_field'] = true;
                }
            }

            //没有组件id 则是新增组件
            if(!isset($v['component_id'])){
                $up['new'][$k] = $v;
            }else{ //有id的则是更新的组件
                $post_cid[$k] = $v['component_id'];
                //默认所有组件都更新
                $up['update'][$k] = $v;
            }

        }

        //如果有旧的组件信息
        if(isset($old_cid) && count($old_cid['ids'])){
            //删除的组件数据
            $del = array_diff( $old_cid['ids'] ,$post_cid);
            foreach($del as $dk => $dv){
                $up['del'][$dk] = $old_cid['info'][$dv];
            }
        }




        if(count($up['new'])){
            //返回新增的组件的id
            $cids = $this->add_component($up['new']);
            if($cids == false) return false;
        }
        if(count($up['update'])){
            $flag = $this->update_component($up['update']);
            //p($flag);
        }
        if(count($up['del'])){
            $flag = $this->del_component($up['del']);
            //p($flag);
        }

        if(self::$error == false){
            return true;
        }else{
            return false;
        }



    }

    /**
     * 添加新组件
     */
    private function add_component($component){
        $table = config('database.prefix').self::$data['table_name'];

        try{
            //检查字段是否存在
            $flag = $this->check_field_exits($table ,$component);
            if(!$flag) return false;


            //创建新的字段
            $flag = $this->add_field($table ,$component);
            if(!$flag) return false;


            Db::startTrans();

            $now = date('Y-m-d H:i:s' ,time());
            $i = 0;
            foreach($component as $sk => $sv){
                $comps[$i]['sorts'] = $sk;
                $comps[$i]['component_name'] = $sv['component_name'];
                $comps[$i]['models_id'] = self::$data['models_id'];
                $comps[$i]['setting'] = json_encode($sv);
                $comps[$i]['add_time'] = $now;
                $comps[$i]['update_time'] = $now;

                //添加字段完成后 写入数据库 返回写入id
                $add_component_ids[$i] = Db::table('jy_models_component')->insertGetId($comps[$i]);

                $i++;
            }

            if(count($add_component_ids)){
                //提交写入数据库
                Db::commit();
                return $add_component_ids;
            }


        }catch (Exception $e){
            self::$error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 更新组件
     */
    private function update_component($component){
        $up_data = [];
        $now = date('Y-m-d H:i:s' ,time());


        try{

            foreach($component as $k => $v){
                $info = Db::table('jy_models_component')->where('id',$v['component_id'])->value('setting');

                if(isset($v['up_field']) && $v['up_field'] == true){
                    $info = json_decode($info,true);
                    $field_change[$k] = [
                        'old_field_name' => $info['field']['name'],
                        'new_field_name' => $v['field']['name'],
                        'type' => $v['field']['type'],
                        'defualt_value' => $v['field']['defualt_value'],
                        'length' => $v['field']['length'],
                    ];
                    unset($v['up_field']);
                }

                $cid = $v['component_id'];
                unset($v['component_id']);


                $up_data[$k]['sorts'] = $k;
                $up_data[$k]['id'] = $cid;
                //有设置变动的 才更新 setting
                if($info != json_encode($v)){
                    $up_data[$k]['setting'] = json_encode($v);
                }
                $up_data[$k]['update_time'] = $now;

            }

            //有字段更新的 则先更新字段
            if(isset($field_change) && count($field_change)){
                $flag = $this->update_field($field_change);
                if(!$flag)return false;
            }




            //修改数据库
            Db::startTrans();
            foreach($up_data as $k => $v){
                $flag = Db::table('jy_models_component')->update($up_data[$k]);
                if(!$flag){
                    self::$error = '第'.$k .'个组件更新失败';
                    Db::rollback();
                    return false;
                }
            }
            Db::commit();
            return true;

        }catch (Exception $e){
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }




    }

    /**
     * 删除组件
     */
    private function del_component($component){
        $table = config('database.prefix').self::$data['table_name'];
        //先删除字段
        $flag = $this->del_field($table ,$component);
        if(!$flag) return false;

        //删除数据库
        foreach($component as $k => $v){
            $ids[] = intval($v['id']);
        }


        $flag = Db::table('jy_models_component')->delete($ids);
        $flag = $flag ? true : false;
        return $flag ;
    }


    /**
     * 更新models
     */
    private function updateModels(){
        $info = Db::table('jy_models')->field('table_name,primary_name,primary_type,primary_length')->find(self::$data['models_id']);


        //检查表的名称是否变更
        if(self::$data['table_name'] !== $info['table_name']){

            if($this->check_table_exists(self::$data['table_name'])){
                self::$error  = self::$data['table_name'] .'表已存在';
                return false;
            }

            $flag = $this->rename_table($info['table_name'],self::$data['table_name']);

            if($flag !== true) {
                self::$error = $flag;
                return false;
            }
        }

        //查询设定主键的类型信息
        $sql = 'SELECT column_name as `name`,COLUMN_TYPE as type FROM information_schema.columns 
          WHERE table_name=\''.config('database.prefix').self::$data['table_name'].'\' AND column_name = \''.$info['primary_name'].'\' ';

        $res = Db::query($sql);


        //检查是否更新主键字段信息
        $update_primary_filed = false;
        if(!$res){
            $update_primary_filed = true;
        }else{
            $old_primary_info = $res[0]['name'].$res[0]['type'];// like fieldnameint(10)
            $new_primary_info = self::$data['primary']['name'].self::$data['primary']['type'].'('.self::$data['primary']['length'].')';
            $update_primary_filed = ($old_primary_info !== $new_primary_info) ? true : false;
        }

        if($update_primary_filed){
            $change[] = [
                'old_field_name' => $info['primary_name'],
                'new_field_name' => self::$data['primary']['name'],
                'type' => self::$data['primary']['type'],
                'length' => self::$data['primary']['length'],
            ];

            $flag = $this->update_field($change ,true);
            if($flag !== true){
                self::$error = $flag;
                return false;
            }
        }



        //更新数据库
        $update_data = Db::table('jy_models')
            ->where('id',self::$data['models_id'])
            ->update([
                'models_name' => self::$data['models_name'],
                'table_name' => self::$data['table_name'],
                'tpl_plan' => self::$data['tpl_plan'],
                'primary_name' => self::$data['primary']['name'],
                'primary_type' => self::$data['primary']['type'],
                'primary_length' => self::$data['primary']['length'],
                'update_time' => date('Y-m-d H:i:s' ,time()),
            ]);

        if(!$update_data){
            self::$error = 'models_数据更新失败';
            return false;
        }


        return true;
    }

    /**
     * 组件的配置 写入models
     */
    private function insertModels(){

        if(self::$error !== false) return self::$error;


        $manager = session('manager');

        $now = date('Y-m-d H:i:s' ,time());

        Db::startTrans();
        try{
            $models_id = Db::table('jy_models')->insertGetId([
                'models_name' => self::$data['models_name'],
                'table_name' => self::$data['table_name'],
                'tpl_plan' => self::$data['tpl_plan'],
                'primary_name' => self::$data['primary']['name'],
                'primary_type' => self::$data['primary']['type'],
                'primary_length' => self::$data['primary']['length'],
                'manager_id' => $manager['id'],
                'manager_name' => $manager['login_name'],
                'add_time' => $now,
                'update_time' => $now
            ]);

            $i = 0;
            foreach(self::$data['component'] as $k => $v){

                $v['base']['issearch'] = $v['base']['showlist'] = 0;
                //开启了showlist
                if(isset($v['base']['showlist']) && $v['base']['showlist'] == 'on')$v['base']['showlist'] = 1;
                //开启了issearch
                if(isset($v['base']['issearch']) && $v['base']['issearch'] == 'on')$v['base']['issearch'] = 1;

                //卸载这两个base 设置选项 （组件的attr中并不存在该两个属性）
                $set = $v;
                unset($set['base']['issearch']);
                unset($set['base']['showlist']);


                $component[$i]['sorts'] = $k;
                $component[$i]['component_name'] = $v['component_name'];
                $component[$i]['models_id'] = $models_id;
                $component[$i]['intable'] = (isset($v['intable']) && $v['intable'] == 'on') ? 1 : 0;
                $component[$i]['insearch'] = (isset($v['insearch']) && $v['insearch'] == 'on') ? 1 : 0;
//                unset($set['intable']);
//                unset($set['insearch']);
                $component[$i]['setting'] = json_encode($set);
                $component[$i]['add_time'] = $now;
                $component[$i]['update_time'] = $now;
                $i++;
            }



            $flag = Db::table('jy_models_component')->insertAll($component);
            Db::commit();

            return true;

        }catch (Exception $e){
            self::$error = $e->getMessage();
            Db::rollback();

            return false;
        }



    }


    /*
     * 创建表
     */
    private function makeTable(){
        $exists = $this->check_table_exists();
        if($exists){
            self::$error = config('database.prefix').self::$data['table_name'].' 表已经存在';
            return false;
        }

        $sql = $this->build_make_table_sql();


        try{
            Db::execute($sql);
            return true;
        }catch (Exception $e){
            self::$error = ' 请检查组件设置字段类型 与 字段默认值 是否匹配';
            return false;
        }

    }

    /**
     * 删除表
     */
    private function dropTable(){
        $sql = 'DROP TABLE '.config('database.prefix').self::$data['table_name'];
        $flag = Db::query($sql);
        return $flag;
    }

    /**
     * 检查table是否存在
     */
    private function check_table_exists($table = ''){

        $table = $table ? $table : self::$data['table_name'];

        $sql = 'show tables like \''.config('database.prefix').self::$data['table_name'].'\' ';
        $flag = Db::query($sql);
        return count($flag) ? true : false ;
    }


    /**
     * 创建生成table 的sql 语句
     * @return string
     */
    private function build_make_table_sql(){

        $table = self::$data['table_name'];

        foreach(self::$data['component'] as $k => $v){

            $field_info[$k] = $v['field'];
        }
        //如果生成方案是 category 则增加一个 path 关系字段
        if(self::$data['tpl_plan'] == 'category'){
            array_push($field_info ,[
                'name' => 'path',
                'type' => 'varchar',
                'length' => '255',
                'defualt_value' => 0,
                'comment' => '分类层级关系地图',
            ]);
        }


        //组装sql 语句
        $sql = 'CREATE TABLE IF NOT EXISTS '.config('database.prefix').$table.' (' ;

        $sql .= '`'.self::$data['primary']['name'].'` '.self::$data['primary']['type'];


        if(self::$data['primary']['length'] > 0){
            $sql .='('.intval(self::$data['primary']['length']).')';
        }

        if(!empty(self::$data['primary']['name'])){
            $sql .=' NOT NULL AUTO_INCREMENT ,';
        }


        //如果有自动写入的字段的
        if($this->autoInsertField != false){
            $field_info = array_merge($field_info ,$this->autoInsertField);
        }


        //组装自定义字段 放中间
        foreach($field_info as $k => $v){

            $type =  strtoupper($v['type']);
            $len =  $v['length'];

            //自动切割多个name字段的时候
            $arg = explode('|' ,$v['name']);
            $comment = explode('|' ,$v['comment']);

            $defualt = explode('|' ,$v['defualt_value']);



            foreach($arg as $sk => $sv){
                $sql .= '`'.strval(trim($sv)).'` '.$type;
                if($len > 0){
                    $sql .='('.$len.')';
                }
                //如果有设置默认值
                if(isset($defualt[$sk]) && strlen($defualt[$sk])){
                    $sql .= ' DEFAULT "'.$defualt[$sk].'"';
                }
                //如果有设置注释
                if(isset($comment[$sk]) && strlen($comment[$sk])){
                    $sql .= ' COMMENT "'.strval(trim(isset($comment[$sk]) ?$comment[$sk] : '')).'" ';
                }

                //数组长度大于1的时候 后面加上逗号
                $sql .= ' , ';
            }
        }



        $last_sql = ' PRIMARY KEY (`'.self::$data['primary']['name'].'`)';
        $sql = $sql . $last_sql;
        $sql = trim($sql,' , ') .');';



        return $sql;
    }

    /*
     * 修改表名
     */
    private function rename_table($oldname,$newname){

        $sql = 'ALTER TABLE `'.config('database.prefix').strval($oldname).'` RENAME `'.config('database.prefix').strval($newname).'`';

        try{
            $falg = Db::execute($sql);
            return true;
        }catch (Exception $e){
            return $e->getMessage();
        }
    }
    /*
     * 添加字段
     */
    private function add_field($table ,$info){


        $sql = 'ALTER TABLE '.$table.' ';

        foreach ($info as $k => $v){


            $defualt = isset($v['field']['defualt_value']) ? $v['field']['defualt_value'] : false;

            $sql .= 'ADD `'.$v['field']['name'].'` '.strtoupper($v['field']['type']);


            if(strlen($v['field']['length']) > 0){

                $sql .= '('.$v['field']['length'].') NOT NULL';
            }

            if($v['field']['comment']){
                $sql .= ' COMMENT "'.$v['field']['comment'].'"';
            }


            if(strlen($defualt) > 0){ //有些字段不能有defualt值 比如 text
                $sql .= ' DEFAULT "'.$defualt.'"';
            }
            $sql .= ',';


        }


        $sql = trim($sql,',');

        try{
            Db::execute($sql);
            return true;
        }catch (Exception $e ){
            self::$error = '新增字段失败';
            return false;
        }
    }
    /*
     * 更新字段
     */
    private function update_field($change ,$isprimarykey = false){

        $sql = 'ALTER TABLE '.config('database.prefix').self::$data['table_name'].' ';

        foreach($change as $k =>$v){

            $sql .= 'CHANGE `'.$v['old_field_name'].'` `'.$v['new_field_name'].'` ';
            if(intval($v['length']) > 0){
                $sql .= strtoupper($v['type']).'('.$v['length'].')';
            }

            if(isset($v['label_name']) && strlen($v['label_name'])){
                $sql .= ' COMMENT "'.$v['label_name'].'" ';
            }

            //如果有设置 defualt
            if(isset($v['defualt_value']) && strlen($v['defualt_value']) > 0){
                $sql .= ' DEFAULT '.$v['defualt_value'].',';
            }

            if($isprimarykey == true){
                $sql .= ' NOT NULL AUTO_INCREMENT ,';
            }


        }



        $sql = trim($sql,',');

        try{
            Db::execute($sql);
            return true;
        }catch (Exception $e ){
            self::$error = $e->getMessage();
            return false;
        }

    }
    /*
     * 删除字段
     */
    private function del_field($table ,$info){


        $sql = 'ALTER TABLE `'.$table.'` ';

        foreach($info as $k => $v){
            $set = json_decode($v['setting'] ,true);
            $sql .= 'DROP COLUMN `'.$set['field']['name'].'`,';
        }

        $sql = rtrim($sql,',');

        try{
            Db::execute($sql);
            return true;
        }catch (Exception $e ){
            self::$error = $e->getMessage();
            return false;
        }
    }
    /**
     * 获取表的所有字段
     */
    private function get_table_fields($table_name){
        $sql = 'show full fields from '.$table_name;
        $field = Db::query($sql);
        return $field;
    }
    /**
     * 检查字段是否存在
     */
    private function check_field_exits($table ,$component){
        if(!count($component)){
            self::$error = 'check_field_exits 字段数组为空';
            return false;
        }

        $fs = '';
        foreach($component as $k => $v){
            $fs .= '\''.$v['field']['name'].'\',';
        }
        $fs = rtrim($fs,',');

        $sql = 'SELECT * FROM information_schema.columns WHERE table_name = \''.$table.'\' AND column_name in('.$fs.')';
        $res = Db::query($sql);
        if(count($res)){
            $ext_field = '';
            foreach ($res as $sv){
                $ext_field .= $sv['COLUMN_NAME'].',';
            }
            $ext_field = rtrim($ext_field ,',');
            self::$error = $ext_field .'字段已存在';
            return false;
        }else{
            return true;
        }

    }


    /**
     * 检查未设置的组件
     * @param $post
     * @return string
     * @throws \ErrorException
     */
    private function check_unsetting_component($post){
        $unsetting = [];
        //组装post数据 ，如果有设置一个组件就会有form_order
        if(isset($post['setting'])){
            foreach($post['form_order'] as $k => $v){
                if(!key_exists($k ,$post['setting'])){
                    $unsetting[$k] = $post['component_name'][$k];
                }
            }
        }

        //监测未设置的组件 并返回
        if(count($unsetting) > 0){
            $error_str = '请设置：';

            foreach($unsetting as $k => $v){
                $componet = Maker::getClass($v);
                //每次都报错 所有未设置的组件
                $error_str .= '第'.($k+1).'个'.$componet::attr()['label'].'组件,';
            }
            $error_str = rtrim($error_str,',');
            self::$error = $error_str;
            return false;
        }

        return true;
    }

}