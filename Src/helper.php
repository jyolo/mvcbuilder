<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-03-07
 * Time: 17:14
 */

/**
 * 解析post where 条件
 */
function _parseWhere($postWhere){
    $where = [];

    foreach($postWhere as $k => $v){

        if(is_array($v)){ //数组的形式 where[open_time][between time][]

            array_walk($v,function($sv ,$sk)use($k,$v,&$where){

                //范围选择 两个值都为true  between
                if(strlen($sv[0]) && strlen($sv[1])){

                    $where[$k] = [$sk,[$sv[0] ,$sv[1]] ];
                }


                //范围选择 第一个值为true  > 大于
                if(strlen($sv[0]) && !strlen($sv[1])){
                    $where[$k] = ['>',$sv[0] ];
                }
                //范围选择 第二个值为true  < 小于
                if(!strlen($sv[0]) && strlen($sv[1])){
                    $where[$k] = ['<',$sv[1] ];
                }
            });


        }else{ //非数组的形式 where[admin_name]
            if(strlen($v)){
                //如果是自动生成的path 字段则左右两侧加上逗号
                $where[] = ['' ,'exp' ,'instr('.$k.',\''.$v.'\')'];
            }
        }



    }

    return $where;
}