<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-03-07
 * Time: 17:14
 */

/**
 * 解析post where 条件
 * 适用于 thinkphp5.1
 */
function _parseWhere($postWhere){
    $where = [];

    foreach($postWhere as $k => $v){

        if(is_array($v)){ //数组的形式 where[open_time][between time][]



            array_walk($v,function($sv ,$sk)use($k,$v,&$where){

//                p($v);

                /**
                 * 区间设置格式如下 where[finish_time][range][-]
                 * array(1) {
                        ["range"]=>
                            array(1) {
                            ["-"]=>
                                string(23) "2018-06-01 - 2018-07-31"
                            }
                     }
                 */
                if(key($v) == 'range'){
                    $timerange  =  $v[key($v)];
                    $rang_char =  key($timerange);
                    $time  = $timerange[$rang_char];
                    $time_arr = explode($rang_char,$time);
                    array_push($where,[$k ,'>=' ,trim($time_arr[0])]);
                    array_push($where,[$k ,'<=' ,trim($time_arr[1])]);

                }else{
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

                }



            });


        }
        else
        { //非数组的形式 where[admin_name]

            if(strlen($v)){
                //如果是自动生成的path 字段则左右两侧加上逗号
                //tp5.1.7 表达式修改过，上面方法失效
                //$where[] = ['' ,'EXP' ,'instr('.$k.',\''.$v.'\')'];

                if(strpos($k ,'id')){ //匹配到id 则 用 = 号
                    $where[] = [$k ,'=' ,$v];
                }else{
                    $where[] = ['' ,'EXP' ,\think\Db::raw('instr('.$k.',\''.$v.'\')')];
                }


            }
        }



    }

    return $where;
}

/**
 * 解析post where 条件
 * 适用于 thinkphp5.0
 */
function __parseWhere($postWhere){
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
                //$where[$k] = $v;
                $where[$k] = ['like' ,'%'.$v.'%'];
            }
        }



    }

    return $where;
}

/**
 * 获取上传 配置 / 错误信息
 */
function get_uploader_config(){
    $arr['errorinfo'] =[//上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    ];
    $arr['config'] = [
        /* 上传图片配置项 */
        "imageActionName"=> "uploadimage", /* 执行上传图片的action名称 */
        "imageFieldName"=> "upfile", /* 提交的图片表单名称 */
        "imageMaxSize"=> 2048000, /* 上传大小限制，单位B */
        "imageAllowFiles"=> [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
        "imageCompressEnable"=> true, /* 是否压缩图片,默认是true */
        "imageCompressBorder"=> 1600, /* 图片压缩最长边限制 */
        "imageInsertAlign"=> "none", /* 插入的图片浮动方式 */
        "imageUrlPrefix"=> "", /* 图片访问路径前缀 */
        "imagePathFormat"=> "/upload/image/{yyyy}{mm}{dd}/{uniqid}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        /* {filename} 会替换成原文件名,配置这项需要注意中文乱码问题 */
        /* {uniqid} 会替换成原文件名,配置这项需要注意中文乱码问题 */
        /* {time} 会替换成时间戳 */
        /* {yyyy} 会替换成四位年份 */
        /* {yy} 会替换成两位年份 */
        /* {mm} 会替换成两位月份 */
        /* {dd} 会替换成两位日期 */
        /* {hh} 会替换成两位小时 */
        /* {ii} 会替换成两位分钟 */
        /* {ss} 会替换成两位秒 */
        /* 非法字符 \ => * ? " < > | */
        /* 具请体看线上文档=> fex.baidu.com/ueditor/#use-format_upload_filename */

        /* 涂鸦图片上传配置项 */
        "scrawlActionName"=> "uploadscrawl", /* 执行上传涂鸦的action名称 */
        "scrawlFieldName"=> "upfile", /* 提交的图片表单名称 */
        "scrawlPathFormat"=> "/upload/image/{yyyy}{mm}{dd}/{uniqid}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "scrawlMaxSize"=> 2048000, /* 上传大小限制，单位B */
        "scrawlUrlPrefix"=> "", /* 图片访问路径前缀 */
        "scrawlInsertAlign"=> "none",

        /* 截图工具上传 */
        "snapscreenActionName"=> "uploadimage", /* 执行上传截图的action名称 */
        "snapscreenPathFormat"=> "/upload/image/{yyyy}{mm}{dd}/{uniqid}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "snapscreenUrlPrefix"=> "", /* 图片访问路径前缀 */
        "snapscreenInsertAlign"=> "none", /* 插入的图片浮动方式 */

        /* 抓取远程图片配置 */
        "catcherLocalDomain"=> ["127.0.0.1", "localhost", "img.baidu.com"],
        "catcherActionName"=> "catchimage", /* 执行抓取远程图片的action名称 */
        "catcherFieldName"=> "source", /* 提交的图片列表表单名称 */
        "catcherPathFormat"=> "/upload/image/{yyyy}{mm}{dd}/{uniqid}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "catcherUrlPrefix"=> "", /* 图片访问路径前缀 */
        "catcherMaxSize"=> 2048000, /* 上传大小限制，单位B */
        "catcherAllowFiles"=> [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */

        /* 上传视频配置 */
        "videoActionName"=> "uploadvideo", /* 执行上传视频的action名称 */
        "videoFieldName"=> "upfile", /* 提交的视频表单名称 */
        "videoPathFormat"=> "/upload/video/{yyyy}{mm}{dd}/{uniqid}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "videoUrlPrefix"=> "", /* 视频访问路径前缀 */
        "videoMaxSize"=> 102400000, /* 上传大小限制，单位B，默认100MB */
        "videoAllowFiles"=> [
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"
        ], /* 上传视频格式显示 */

        /* 上传文件配置 */
        "fileActionName"=> "uploadfile", /* controller里,执行上传视频的action名称 */
        "fileFieldName"=> "upfile", /* 提交的文件表单名称 */
        "filePathFormat"=> "/upload/file/{yyyy}{mm}{dd}/{uniqid}", /* 上传保存路径,可以自定义保存路径和文件名格式 */
        "fileUrlPrefix"=> "", /* 文件访问路径前缀 */
        "fileMaxSize"=> 51200000, /* 上传大小限制，单位B，默认50MB */
        "fileAllowFiles"=> [
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ], /* 上传文件格式显示 */

        /* 列出指定目录下的图片 */
        "imageManagerActionName"=> "listimage", /* 执行图片管理的action名称 */
        "imageManagerListPath"=> "/upload/image/", /* 指定要列出图片的目录 */
        "imageManagerListSize"=> 20, /* 每次列出文件数量 */
        "imageManagerUrlPrefix"=> "", /* 图片访问路径前缀 */
        "imageManagerInsertAlign"=> "none", /* 插入的图片浮动方式 */
        "imageManagerAllowFiles"=> [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */

        /* 列出指定目录下的文件 */
        "fileManagerActionName"=> "listfile", /* 执行文件管理的action名称 */
        "fileManagerListPath"=> "/upload/file/", /* 指定要列出文件的目录 */
        "fileManagerUrlPrefix"=> "", /* 文件访问路径前缀 */
        "fileManagerListSize"=> 20, /* 每次列出文件数量 */
        "fileManagerAllowFiles"=> [
            ".png", ".jpg", ".jpeg", ".gif", ".bmp",
            ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
            ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
            ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
            ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
        ] /* 列出的文件类型 */
    ];

    return $arr;
}