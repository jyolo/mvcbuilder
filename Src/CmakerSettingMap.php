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

    //设定每个组件 的 设置选项 以及 设置选项 所用到的 组件
    public static $map = [
        //key 为组件名字 多个相同的 可以 用 | 线分割
        'text|number' => [
            //key 为组件属性
            'placeholder' => [
                'component' => 'text',
                'label' => '输入框提示文本',
            ],
        ],
        'daterange' => [
            //key 和组件的attr 对应
            'range' => [
                'component' => 'select', //使用哪个组件
                'label' => '时间分隔符', // 组件的属性
                'helpinfo' => '',
                'option' => [
                    '-' => '-',
                    '~' => '~'
                ],
                'layVerify' => 'required',
            ],
            'readonly' => [
                'component' => 'switchs',
                'label' => '只读',
                'text' => '是|否',
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
            'serverUrl' => [
                'component' => 'text', //使用哪个组件
                'label' => '服务端url',
                'value' => 'zxc',
                'layVerify' => 'required',
            ]
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

        ]
    ];

    //每个组件所用设置组件的 值 显示的属性
    public static $setComponentsStatusAttr = [
        'text|number|hidden|ueditor|webuploader' => 'value',
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

}