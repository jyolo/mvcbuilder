<?php
/**
 * 组件设置的映射地图
 * User: Administrator
 * Date: 2018/1/4
 * Time: 10:13
 */

namespace MvcBuilder;


class CmakerSettingMap
{
    //所有组件的相同的属性的设置选项
    public static $common_map = [
        // key 组件的属性
        'label' => [
            'component' => 'text', //组件的类型
            'label' => '表单名称', //组件属性.....
            'layVerify' => 'required',
            'helpinfo' => '',
        ],
        'helpinfo' => [
            'component' => 'text',
            'label' => '帮助信息',
            'layVerify' => '',
            'helpinfo' => '',
        ],
    ];

    //设定每个组件的设置选项 以及 设置选项 所用到的 组件
    public static $map = [
        //key 为组件名字 多个相同的 可以 用 | 线分割
        'text|number' => [
            //key 为组件属性
            'placeholder' => [
                'component' => 'text',
                'label' => '输入框提示文本',
            ],
        ],
        'datepicker' => [
            //key 和组件的attr 对应
            'type' => [
                'component' => 'select', //使用哪个组件
                'label' => '类型', // 组件的属性
                'helpinfo' => '选择分隔符则代表开启区间选择',
                'option' => [
                    'date' => '日期选择器[ 默认值 ]',
                    'datetime' => '时间选择器[ 可选年、月、日、时、分、秒 ]',
                    'year' => '年选择器[ 只提供年列表选择 ]',
                    'month' => '年月选择器[ 只提供年、月选择 ]',
                    'time' => '时间选择器[ 只提供时、分、秒选择 ]',
                ],
                'choose' => 'date',
                'layVerify' => 'required',
            ],

            'lang' => [
                'component' => 'radio', //使用哪个组件
                'label' => '语言', // 组件的属性
                'option' => [
                    'cn' => '中文',
                    'en' => '英文',
                ],
                'choose' => 'cn',
                'layVerify' => 'required',
            ],
            'theme' => [
                'component' => 'radio', //使用哪个组件
                'label' => '主题', // 组件的属性
                'option' => [
                    'default' => '默认简约',
                    'molv' => '墨绿背景',
                    'grid' => '格子主题',
                ],
                'choose' => 'default',
                'layVerify' => 'required',
            ],
            'format' => [
                'component' => 'text', //使用哪个组件
                'label' => '自定义格式', // 组件的属性
                'placeholder' => 'yyyy-MM-dd HH:mm:ss',
                'helpinfo' => 'yyyy年MM月dd日 HH时mm分ss秒	等于 2017年08月18日 20时08分08秒',
                'layVerify' => '',
            ],

            'range' => [
                'component' => 'select', //使用哪个组件
                'label' => '时间分隔符', // 组件的属性
                'helpinfo' => '选择分隔符则代表开启区间选择',
                'option' => [
                    '-' => '-',
                    '~' => '~'
                ],
                'layVerify' => '',
            ],
            'readonly' => [
                'component' => 'switchs',
                'label' => '只读',
                'text' => '是|否',
                'open' => '1',
                'helpinfo' => '',
            ],
        ],
        'select|radio|checkbox' =>[
            'option' => [
                'component' => 'text', //使用哪个组件
                'label' => '选项值',
                'helpinfo' => '示例:key-value|key-value 或 value|value',
                'layVerify' => 'required',
            ],
            'choose' => [
                'component' => 'text', //使用哪个组件
                'label' => '默认选中值',
                'helpinfo' => '选填',
            ],
        ],
        'switchs' => [
            'text' => [
                'component' => 'text',
                'label' => '选项字符',
                'placeholder' => '比如：是|否',
                'helpinfo' => '最多两个值，用 | 先分割',
            ],
            'open' => [
                'component' => 'switchs',
                'label' => '默认开启',
                'text' => '是|否',
            ],
        ],
        'ueditor' => [
            'show' => [
                'component' => 'select', //使用哪个组件
                'label' => '工具栏显示方式',
                'option' => 'simple-简单|middle-常用|all-所有',
                'layVerify' => 'required',
            ],
        ],
        'webuploader' => [
            'uploadtype' =>[
                'component' => 'select', //使用哪个组件
                'label' => '上传类型',
                'option' => 'image-图片|file-文件',
                'layVerify' => 'required',
            ],
            'multiple' =>[
                'component' => 'switchs', //使用哪个组件
                'label' => '是否多选',
                'text' => '是|否',
                'helpinfo' => '文件多选'
            ],
            'auto' =>[
                'component' => 'switchs', //使用哪个组件
                'label' => '自动上传',
                'text' => '是|否',
                'helpinfo' => '选中文件后直接上传'
            ],
        ],
        'relation' =>[
            'table' => [//数据表
                'component' => 'text',
                'label' => '数据表',
                'layVerify' => 'required',
            ],
            'field' => [//选择字段
                'component' => 'text',
                'label' => '字段',
                'helpinfo' => '多个逗号隔开。第一个为表单提交的值，第二个为展现的值',
                'layVerify' => 'required',
            ],
            'showtype' =>[ //展现形式
                'component' => 'select',
                'label' => '展现形式',
                'option' => 'select-下拉选择|radio-单选|checkbox-复选框|treeSelect-多层级下拉选择',
                'layVerify' => 'required',
            ],

        ],
        'linkselect' =>[
            'linkfield' => [
                'component' => 'text',
                'label' => '关联字段',
                'showtype' => 'block',
                'placeholder' => '格式:cid|wid|gid',
                'layVerify' => 'required',
            ],
            'param' => [
                'component' => 'text',
                'label' => '请求参数',
                'showtype' => 'block',
                'placeholder' => '格式：type-goods_cat,a-b|type-goods_warehouse ;逗号为',
                'layVerify' => 'required',
            ],
            'showfield' => [
                'component' => 'text',
                'label' => '显示字段',
                'showtype' => 'block',
                'placeholder' => '格式：id-cat_name|wid-wname ；第一个为value值第二个为option显示',
                'layVerify' => 'required',
            ],
            'serverUrl' => [//数据表
                'component' => 'text',
                'label' => '请求地址',
                'showtype' => 'block',
                'layVerify' => 'required',
            ],

        ]
    ];
    public static $fieldType = [
        //常用
        'TINYINT' => 'TINYINT',
        'INT' => 'INT',

        'VARCHAR' => 'VARCHAR',
        'TEXT' => 'TEXT',

        'DATE' => 'DATE',
        'DATETIME' => 'DATETIME',
        'DECIMAL' => 'DECIMAL',
        //数字类型
        'SMALLINT' => 'SMALLINT',
        'MEDIUMINT' => 'MEDIUMINT',
        'BIGINT' => 'BIGINT',
        'FLOAT' => 'FLOAT',
        'DOUBLE' => 'DOUBLE',

        //字符串类型
        'CHAR' => 'CHAR',
        'TINYBLOB' => 'TINYBLOB',
        'TINYTEXT' => 'TINYTEXT',
        'BLOB' => 'BLOB',
        'MEDIUMBLOB' => 'MEDIUMBLOB',
        'MEDIUMTEXT' => 'MEDIUMTEXT',
        'LONGBLOB' => 'LONGBLOB',
        'LONGTEXT' => 'LONGTEXT',
        //时间类型
        'TIME' => 'TIME',
        'YEAR' => 'YEAR',
        'TIMESTAMP' => 'TIMESTAMP',
    ];
    //设定每个组件的数据库默认的字段信息
    public static $fieldMap = [
        'text|password|datarange|webuploader' => [
            'type' => 'VARCHAR',
            'length' => '255',
            'defualt_value' => '',
        ],
        'textarea|ueditor' => [
            'type' => 'TEXT',
            'length' => '',
            'defualt_value' => '',
        ],
        'number' => [
            'type' => 'INT',
            'length' => '11',
            'defualt_value' => '0',
        ],
        'select|radio|checkbox|relation' => [
            'type' => 'varchar',
            'length' => '100',
            'defualt_value' => '',
        ],
        'switch' => [
            'type' => 'TINYINT',
            'length' => '1',
            'defualt_value' => '0',
        ],

    ];

    //每个组件所用设置组件的 值 显示的属性
    public static $setComponentsStatusAttr = [
        'text|number|textarea|password|hidden|ueditor|webuploader|linkselect' => 'value',
        'switchs' => 'open',
        'select|radio|checkbox|relation' => 'choose',
    ];

    /**
     * key 为 'a|b' => value 转化成 'a'=>value ,'b' => value
     * @param $arr
     * @return mixed
     */
    public static function tosplitKeyToValue($arr){
        $temArr = $arr;

        foreach($temArr as $k => $v){
            if(strpos($k,'|')){
                $tem = explode('|',$k);
                foreach($tem as $sk => $sv){
                    $arr[$sv] = $v;
                }
                unset($arr[$k]);
            }
        }
        unset($temArr);
        return $arr;
    }

    /**
     *
     */
    public static function splitOptionValue($str){

        if(strpos($str,'|')){
            $return = [];
            $arr = explode('|' ,$str);
            foreach ($arr as $k => $v){

                if(strpos($v,'-')){
                    $arg = explode('-',$v);
                    $return[$arg[0]] = $arg[1];
                }else{
                    $return[$k] = $v;
                }
            }

            return $return;
        }
    }

}