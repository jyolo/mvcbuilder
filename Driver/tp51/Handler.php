<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/26
 * Time: 13:57
 */

namespace MvcBuilder\Driver\tp51;


use MvcBuilder\Core\ContentBuilder;
use MvcBuilder\Core\Driver;

class Handler extends Driver
{

    public $config = [
        'app_path' => APP_PATH, //应用的路径
        'view_path' => THEME_PATH, //视图的路径
        'folder' => [ //目录的结构
            '__file__'   => ['common.php'],
            '__dir__'    => ['controller', 'model', 'validate' , 'view'  ], //视图文件加,通常会有一些默认的页面
        ],
        'view_file' => ['index','add','edit'], //视图默认生成的文件
        'suffix' => [
            'controller' => '.php',
            'model' => '.php',
            'validate' => '.php',
            'view' => '.html',
        ],
    ];

    /**
     * 生成florder目录结构
     * 定义models信息
     * @return null
     */
    public function makeFolderMap(){

        try{

            $filepath = $this->config['app_path'].self::$data['file_name'].DIRECTORY_SEPARATOR;
            $arr = [];
            $models = $this->get_models(self::$data['models_id']);

            foreach($this->config['folder'] as $k => $v){
                //文件的小标为数字
                if($k == '__file__'){
                    foreach($v as $sk => $sv){

                        $arr[$sk] = $filepath.$sv;
                    }
                }
                //目录的下标 为字符串
                if($k == '__dir__'){

                    //循环加入 controller ,models,validata,view 的目录结构
                    foreach($v as $sk => $folder){
                        $suffix = isset($this->config['suffix'][$folder]) ? $this->config['suffix'][$folder] : '.php';
                        $arr[$folder] = $tem = []; // like arr[controller] = []

                        foreach($models as $mk => $mv){
                            $tname  = $this->table_name_to_file_name($mv['table_name']);
                            switch ($folder){
                                case 'view':
                                    if(isset($this->config['view_path'])){
                                        //$path = $this->config['view_path'].self::$data['file_name'].DIRECTORY_SEPARATOR.$tname;//视图的文件夹名字不能使用驼峰否则或not found
                                        $path = $this->config['view_path'].self::$data['file_name'].DIRECTORY_SEPARATOR.$mv['table_name'];
                                    }else{ //未定义视图的路径 则采用默认的路径
                                        //$path = $filepath.$folder.DIRECTORY_SEPARATOR.$tname; //视图的文件夹名字不能使用驼峰否则或not found
                                        $path = $filepath.$folder.DIRECTORY_SEPARATOR.$mv['table_name'];
                                    }

                                    if(isset($this->config['view_file'])){

                                        foreach($this->config['view_file'] as $viewfile){
                                            $tem = [
                                                'models_info' => $mv,       //models的信息
                                                'tpl_type' => $folder,      //tpl类型
                                                'tpl_plan' => $mv['tpl_plan'],//模板方案
                                                'view_file' => $viewfile,
                                                'dir' => $path,    //模板的文件
                                                'file' => $path.DIRECTORY_SEPARATOR.$viewfile.$suffix           //将要生成的文件
                                            ];
                                            array_push($arr[$folder],$tem);
                                        }
                                    }
                                    break;
                                default:
                                    $tem = [
                                        'models_info' => $mv,  //models的信息
                                        'tpl_type' => $folder, //tpl类型
                                        'tpl_plan' => $mv['tpl_plan'] ,//模板方案
                                        'dir' => $filepath.$folder,    //模板的文件
                                        'file' => $filepath.$folder.DIRECTORY_SEPARATOR.$tname.$suffix          //将要生成的文件
                                    ];
                                    $arr[$folder][$mk] = $tem;
                                    break;
                            }

                        }




                    }
                }
            }

            return $arr;

        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

    }



    /**
     *  生成文件
     */
    public function buildFile($foldermap)
    {
        try{
            $module_path = $this->config['app_path'].self::$data['file_name'];
            //新的模块不存在的情况下 新建文件夹
            if(!file_exists($module_path))mkdir($module_path);

            foreach($foldermap as $k => $v){
                //文件
                if(is_numeric($k)){
                    if(!file_exists($v))file_put_contents($v,'<?php'."\r\n");
                }
                //目录
                if(is_string($k)){
                    foreach($v as $sk => $sv){
                        //创建目录
                        if(!file_exists($sv['dir']))mkdir($sv['dir'],0775,true);
                        //创建文件
                        ContentBuilder::create($sv);
                    }

                }

            }

            return true;
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }

    }


    /**
     * 驱动自定义表名称 需要转化成什么格式文件名称
     * @param $table_name
     * @return mixed
     */
    private function table_name_to_file_name($table_name){
        if(strpos($table_name,'_')){
            $table_name = explode('_',$table_name);
            array_walk($table_name,function($value)use(&$tname){
                //这个版本的tp 核心代码 没有对控制器进行驼峰判断，只进行了首字母大写的判断
                $tname .= ucfirst($value);
                //$tname .= $value;
            });
            $tname = ucfirst($tname);
        }else{
            $tname = ucfirst($table_name);
        }

        return $tname ;
    }



}