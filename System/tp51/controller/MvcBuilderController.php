<?php
namespace MvcBuilder\System\tp51;

use app\Base\model\Menu;
use think\Controller;
use think\Exception;
use think\facade\Request;
use CMaker\Maker;
use MvcBuilder\MvcBuilder;
use MvcBuilder\MvcBuilderHelper;
use MvcBuilder\MvcBuilderUploader;
use MvcBuilder\Core\MenuMaker;
use think\Db;

class MvcBuilderController extends Controller{

    protected static $models = null;
    protected static $models_component = null;

    protected function initialize()
    {

        self::$models = new MvcBuilderModels();
        self::$models_component = new MvcBuilderModelsComponent();
        $config = get_uploader_config();
        $this->CONFIG = $config['config'];
        $this->ERRORINFO = $config['errorinfo'];

    }



    /**
     * 查看
     */
    public function index(){
        p('123');
        die();
        if(Request::isGet()){
            return $this->fetch(__DIR__ . '/../../../View/index.html');
        }

        if(Request::isPost()){
            $post = input('post.');

            $return['data'] = self::$models->getList($post);
            $return['count'] = self::$models->count();
            $return['code'] = 0;
            return json($return);

        }

    }


    /**
     *添加
     */
    public function add(){

        $tpl_plan = MvcBuilder::getTplPlan('atcmf');

        $this->assign('tpl_plan' ,$tpl_plan);
        return $this->fetch(__DIR__ . '/../../../View/add.html');
    }


    /**
     *编辑
     */
    public function edit(){
        $id = input('param.id');
        $dom = $value = '';
        $models = self::$models->find($id)->toArray();

        $fields = self::$models_component->order('sorts asc')->where('models_id',$models['id'])->select()->toArray();

        $componet = '';

        foreach($fields as $k => $v){

            $set = json_decode($v['setting'] ,true);

            //根据组件的设置组装组件
            $obj = Maker::build($v['component_name']);
            foreach($set['base'] as $sk => $sv){

                $obj->$sk($sv);
            }
            $componet .= $obj->render();

//            $str = '{:CMaker('.$v['component_name'].')';
//            foreach($set['base'] as $sk => $sv){
//                $str .= '->'.$sk.'("'.$sv.'")';
//            }
//            $str .= '->render()}';
//            $componet .= $str."\r\n";


            $componet .= '<div class="layui-input-inline" style="display: none"><a style="float:right" href="javascript:;" onclick="showsetting(this)" class="layui-btn"><i class="fa fa-edit"></i>编辑</a></div>';
            $componet .= '<div class="hidden_list" style="display: none">';
            $componet .= Maker::build('hidden')->classname('__component_id')->name('component_id['.$k.']')->value($v['id'])->render();
            $componet .= Maker::build('hidden')->classname('__form_order')->name('form_order['.$k.']')->value($k)->render();
            $componet .= Maker::build('hidden')->classname('__component_name')->name('component_name['.$k.']')->value($v['component_name'])->render();

            //生成唯一值
            $setting_key = md5( uniqid('',true).uniqid('',true) );
            cache($setting_key ,$v['setting'],86400);
            $componet .= Maker::build('hidden')->classname('__setting')->name('setting['.$k.']')->value($setting_key)->render();
            $componet .= '</div>';

        }


        $tpl_plan = MvcBuilder::getTplPlan('tp51');

        $this->assign('tpl_plan' ,$tpl_plan);
        $this->assign('models',$models);
        $this->assign('componet' ,$componet);

        return $this->fetch(__DIR__ . '/../../../View/edit.html');

    }


    /**
     * 编辑组件
     */
    public function get_model_form(){
        $param = input('param.');

        //获取组件保存编辑之后的setting的值,如果有值的话 再次点击编辑 则默认 用setting创建组件
        $setting = (isset($param['setting']) ) ? $param['setting'] : false;

        $MvcBuilderHelper = new MvcBuilderHelper(trim($param['component_name']) ,$setting);

        //检查编辑的组件 是否允许 在 MvcBuilder 中使用
        if(!$MvcBuilderHelper->check_allowed_component(trim($param['component_name'])))throw new \ErrorException('该组件不允许在MvcBuilder中使用');
        //获取组件的设置选项
        $setting_dom = $MvcBuilderHelper->set;


        //设置项 提交的地址
        $setting_dom['url'] = url('mvcbuilder/edit_model_form',['form_order' => trim($param['form_order']) ,'form_type' => trim($param['component_name']) ]);


        $setting = json_decode(cache($setting) , true);

        //是否在table中
        $this->assign('intable' ,(isset($setting['intable']) && $setting['intable'] == 'on') ? 'checked' : '');
        //是否在搜索的选项中
        $this->assign('insearch' ,(isset($setting['insearch']) && $setting['insearch'] == 'on') ? 'checked' : '');
        //设置的dom
        $this->assign('setting' ,$setting_dom);

        return $this->fetch(__DIR__ . '/../../../View/get_model_form.html');

    }

    /**
     * 保存组件编辑
     */
    public function edit_model_form(){
        $post = input('post.');
        $get = input('param.');

        $el_index = $get['form_order'];

        $data['form_order'] = $el_index;
        //如果已经存在的setting_key 的 缓存标志的话，直接赋值 ，不在重新生成
        if(isset($post['setting_key']) && is_array(json_decode(cache($post['setting_key']) , true))){
            $setting_key = $post['setting_key'];
        }else{
            $setting_key = md5( uniqid('',true).uniqid('',true) );
        }


        //缓存设置的信息 86400秒 / 24个小时
        cache($setting_key ,json_encode($post) ,86400);


        //组件的设置json格式
        $data['html'] = Maker::build('hidden')->classname('__setting')->name('setting['.$el_index.']')->value($setting_key)->render();


        $value = isset($post['value']) ? explode('|',$post['value']): '';


        //获取组件
        $component = Maker::getClass($get['form_type']);


        //获取组件属性
        $component_attr = $component::attr();


        try{

            //根据post 数据 和 组件的属性 创建新的组件
            $new_component = Maker::build($get['form_type']);
            //根据post 值 与 组建的 attr 设置值
            foreach($post['base'] as $k => $v){
                if(key_exists($k,$component_attr)){
                    $new_component->$k($v);
                }
            }
            $data['new_component'] = $new_component->render();
            //获取最后一个组件的设置选项（第0个是hidden组件）
            $data['new_component_attr'] = Maker::$components[count(Maker::$components)-1];

            $this->success('编辑成功' ,'',$data);

        }catch (Exception $e){

            $this->error($e->getMessage());
        }


    }


    /**
     * 保存添加
     */
    public function add_action(){
        $post = input('post.');

        //models 验证
//        $flag = $this->validate($post,'Models.add');
//        if($flag !== true)$this->error($flag);


        //组件的配置 写入数据库记录
        MvcBuilder::core('SqlBuilder')->initData($post)->create();


        if(MvcBuilder::$error !== false) $this->error(MvcBuilder::$error);
        $this->success('添加成功');

    }
    /**
     *  保存编辑
     */
    public function edit_action(){
        $post = input('post.');

        //models 验证
        //$flag = $this->validate($post,'Models.add');
        //if($flag !== true)$this->error($flag);
        //组件的配置 写入数据库记录
        MvcBuilder::core('SqlBuilder')->initData($post)->update();

        if(MvcBuilder::$error !== false) $this->error(MvcBuilder::$error);

        $this->success('编辑成功');

    }
    /**
     *删除
     */
    public function del(){
        $post = input('post.');

        $flag = self::$models->_del($post['id']);
        if(!$flag)$this->error($this->models->getError());
        $this->success('删除成功');
    }

    /**
     *批量删除
     */
    public function pdel(){
        $post = input('post.');
       // p($post);
//        $this->success('删除成功');
//        die();
        if(!isset($post['ids'])) $this->error('请先选择');

        foreach($post['ids'] as $k => $v){
            $flag = self::$models->_del($v);
            if(!$flag)$this->error($this->models->getError());
        }

        $this->success('删除成功');
    }

    /**
     *生成模型
     */
    public function build(){
        $models = self::$models->select()->toArray();

        $this->assign('models' ,$models);
        return $this->fetch(__DIR__ . '/../../../View/build.html');
    }

    /**
     * 生成菜单
     */
    public function build_menu(){
        $models = self::$models->select()->toArray();

        $this->assign('models' ,$models);
        return $this->fetch(__DIR__ . '/../../../View/build_menu.html');
    }

    /**
     * 执行生成菜单
     */
    public function build_menu_action(){
        $post = input('post.');
        if(!isset($post['models_id']))$this->error('请选择右侧模型');

        //调用MenuBuilder 根据模型 获取到 权限菜单表 所需的基本信息
        $MenuBuilder = MvcBuilder::core('MenuBuilder');
        $info = $MenuBuilder->get_base_info($post);



        //具体写入数据库，需要做哪些字段的映射，根据具体的 权限菜单表 来决定。
        //如果设置 topmenu 最顶级的菜单
        $topmenu = [];
        if(isset($post['topmenu']) && $post['topmenu']){
            $topmenu['name'] = $post['topmenu'];
            $topmenu['pid'] = 0;
            $topmenu['icon'] = strlen($post['icon']) ? $post['icon'] : $MenuBuilder->defualt_icon;
            $topmenu['module'] = $post['module'];
            $topmenu['controller'] = 'defualt';
            $topmenu['action'] = 'defualt';
        }

        foreach($info as $k => $v){

            $parent[$k]['name'] = $v['menu_name'];
            $parent[$k]['pid'] = 0;
            $parent[$k]['icon'] = strlen($post['icon']) ? $post['icon'] : $v['icon'];
            $parent[$k]['module'] = $v['module'];
            $parent[$k]['controller'] = $v['controller'];
            $parent[$k]['action'] = $v['action'][0]['method'];

            foreach($v['action'] as $sk => $sv){
                $son[$k][$sk]['name'] = $sv['note'];
                $son[$k][$sk]['module'] = $v['module'];
                $son[$k][$sk]['controller'] = $v['controller'];
                $son[$k][$sk]['action'] = $sv['method'];
            }
        }


        //实例化多个model 否则在事物中循环插入会报key重复的错误
        foreach($parent as $k => $v){
            $MenuModle[$k] = new Menu();
        }

        Db::startTrans();
        try{

            $topmenu_id = false;
            if(count($topmenu)){
                $topmenuModel = new Menu();
                $flag = $topmenuModel->isUpdate(false)->save($topmenu);
                if(!$flag) throw new Exception('topmenu 写入失败');
                $topmenu_id = $topmenuModel->getLastInsID();
            }


            foreach($parent as $k => $v){
                $flag = $MenuModle[$k]->where([
                    ['module' ,'=' ,$v['module']],
                    ['controller' ,'=',$v['controller']],
                    ['action' ,'=',$v['action']]
                ])->select();
                if(count($flag)) throw new Exception('权限菜单中已经存在：module->'.$v['module'].'， controller->'.$v['controller'].'， action->'.$v['action']);

                if($topmenu_id != false){
                    $v['pid'] = $topmenu_id;
                }

                $flag = $MenuModle[$k]->isUpdate(false)->save($v);
                if(!$flag) throw new Exception('parent menu 写入失败');

                foreach($son[$k] as $sk => $sv){
                    $son[$k][$sk]['pid'] = $MenuModle[$k]->getLastInsID();
                    $son[$k][$sk]['isshow'] = 1; //子菜单隐藏
                }

                $flag = $MenuModle[$k]->isUpdate(false)->saveAll($son[$k]);

                Db::commit();

            }

            $this->success('生成成功');
        }catch (Exception $e){
            Db::rollback();
            
            throw new Exception('菜单数据写入失败'.$e->getMessage());
        }


    }

    /**
     *执行生成模型
     */
    public function build_action(){
        $post = input('post.');

        if(!isset($post['models_id']))$this->error('请选择右侧模型');


        $MvcBuilder = MvcBuilder::init('atcmf' ,$post);
        //创建目录地图
        $foldermap = $MvcBuilder->makeFolderMap();


        //生成文件
        $MvcBuilder->buildFile($foldermap);

        $this->success('生成成功');




    }

    /**
     *  ueditor 编辑器 配置/上传
     */
    public function ueditor(){

        //由于config 获取到的配置文件均被小写 所以采用require 的方式获取配置文件
        //$CONFIG = Config::get('upload.');

        $CONFIG = $this->CONFIG;


        $action = input('get.action');

        switch ($action) {
            case 'config':
                exit(json_encode($CONFIG)) ;
                break;
            /* 上传图片 */
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];

                break;
            /* 上传涂鸦 */
            case 'uploadscrawl':
                //$result = $this->_ueditor_upload();
                break;
            /* 上传视频 */
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $CONFIG['videoPathFormat'],
                    "maxSize" => $CONFIG['videoMaxSize'],
                    "allowFiles" => $CONFIG['videoAllowFiles']
                );
                $fieldName = $CONFIG['videoFieldName'];
                break;
            /* 上传文件 */
            case 'uploadfile':
                $config = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize" => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
            /* 列出图片 */
            case 'listimage':
                $result = "";
                break;
            /* 列出文件 */
            case 'listfile':

                break;
            /* 抓取远程文件 */
            case 'catchimage':

                break;
            default:
                $result = json_encode(array('state'=> '请求地址出错'));
                break;
        }
        $up = new MvcBuilderUploader($fieldName, $config);
        echo json_encode($up->getFileInfo());

    }

    public function webupload(){
        $action = input('post.action');

        try{
            $field_name = key($_FILES);

            if(!count($_FILES)) return ['code' => 0,'msg'=> '无上传文件' ,'data' => ''];

            $error_code = $_FILES[$field_name]['error'];
            $msg = $this->ERRORINFO[$error_code];

            if($msg != 'SUCCESS')return json(['code' => 0,'msg'=> $msg ,'data' => '']);

            switch ($action) {

                /* 上传图片 */
                case 'image':
                    $config = [
                        "pathFormat" => $this->CONFIG['imagePathFormat'],
                        "maxSize" => $this->CONFIG['imageMaxSize'],
                        "allowFiles" => $this->CONFIG['imageAllowFiles']
                    ];
                    break;
                /* 上传视频 */
                case 'video':
                    $config = [
                        "pathFormat" => $this->CONFIG['videoPathFormat'],
                        "maxSize" => $this->CONFIG['videoMaxSize'],
                        "allowFiles" => $this->CONFIG['videoAllowFiles']
                    ];
                    break;
                /* 上传文件 */
                case 'audio':
                    $config = [
                        "pathFormat" => $this->CONFIG['filePathFormat'],
                        "maxSize" => $this->CONFIG['fileMaxSize'],
                        "allowFiles" => $this->CONFIG['fileAllowFiles']
                    ];
                    break;
                /* 上传文件 */
                case 'file':
                    $config = [
                        "pathFormat" => $this->CONFIG['filePathFormat'],
                        "maxSize" => $this->CONFIG['fileMaxSize'],
                        "allowFiles" => $this->CONFIG['fileAllowFiles']
                    ];
                    break;
                default:
                    return ['code' => 0,'msg'=> '找不到请求的方法' ,'data' => ''];
                    break;
            }
            //存储路径改为layuplaod
            $config['pathFormat'] = str_replace('/ueditor/','',$config['pathFormat']);


            $up = new MvcBuilderUploader($field_name, $config);



            $info = $up->getFileInfo();

            if($info['state'] != 'SUCCESS')return json(['code' => 0,'msg'=> $info['state'] ,'data' => '']);


            return json(['code' => 1,'msg'=> '上传成功' ,'data' => $info]);
        }catch (Exception $e){
            return json(['code' => 0,'msg'=> $e->getMessage() ,'data' => '']);
        }
    }


}