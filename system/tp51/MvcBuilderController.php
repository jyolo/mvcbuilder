<?php
namespace MvcBuilder\system\tp51;
use think\Controller;
use think\facade\Request;
use CMaker\Maker;
use MvcBuilder\MvcBuilder;
use MvcBuilder\MvcBuilderHelper;

class MvcBuilderController extends BaseController {

    protected static $models = null;
    protected static $models_component = null;

    protected function initialize()
    {

        self::$models = new MvcBuilderModels();
        self::$models_component = new MvcBuilderModelsComponent();
        //p(Request::module());
       // p(Request::controller());
        //p(Request::action());
        $this->requseturl = Request::module() . '/' . Request::controller();
        $this->assign('requseturl',$this->requseturl);
    }

    /**
     * [menu]查看[/menu]
     */
    public function index(){
        if(Request::isGet()){

            return $this->fetch(__DIR__ . '/view/index.html');
        }

        if(Request::isPost()){
            $post = input('post.');

            $list = self::$models->getList($post);
            $list['count'] = self::$models->count();
            $this->result($list);

        }

    }


    /**
     *[menu]添加[/menu]
     */
    public function add(){


        $tpl_plan = MvcBuilder::getTplPlan('tp51');

        $this->assign('tpl_plan' ,$tpl_plan);
        return $this->fetch(__DIR__ . '/view/add.html');
    }


    /**
     *[menu]编辑[/menu]
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

            $setting_key = uniqid();
            cache($setting_key ,$v['setting'],86400);
            $componet .= Maker::build('hidden')->classname('__setting')->name('setting['.$k.']')->value($setting_key)->render();
            $componet .= '</div>';

        }




        $tpl_plan = MvcBuilder::getTplPlan('tp51');

        $this->assign('tpl_plan' ,$tpl_plan);
        $this->assign('models',$models);
        $this->assign('componet' ,$componet);

        return $this->fetch(__DIR__ . '/view/edit.html');

    }


    /**
     * [menu]编辑组件[/menu]
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
        $setting_dom['url'] = url($this->requseturl.'/edit_model_form',['form_order' => trim($param['form_order']) ,'form_type' => trim($param['component_name']) ]);


        $setting = json_decode(cache($setting) , true);

        //是否在table中
        $this->assign('intable' ,(isset($setting['intable']) && $setting['intable'] == 'on') ? 'checked' : '');
        //是否在搜索的选项中
        $this->assign('insearch' ,(isset($setting['insearch']) && $setting['insearch'] == 'on') ? 'checked' : '');
        //设置的dom
        $this->assign('setting' ,$setting_dom);

        return $this->fetch(__DIR__ . '/view/get_model_form.html');

    }

    /**
     * [menu]保存组件编辑[/menu]
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
            $setting_key = uniqid();
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
     * [menu]保存添加[/menu]
     */
    public function add_action(){
        $post = input('post.');

        //models 验证
        $flag = $this->validate($post,'Models.add');
        if($flag !== true)$this->error($flag);


        //组件的配置 写入数据库记录
        MvcBuilder::core('SqlBuilder')->initData($post)->create();


        if(MvcBuilder::$error !== false) $this->error(MvcBuilder::$error);
        $this->success('添加成功');

    }
    /**
     *  [menu]保存编辑[/menu]
     */
    public function edit_action(){
        $post = input('post.');

        //models 验证
        $flag = $this->validate($post,'Models.add');
        if($flag !== true)$this->error($flag);
        //组件的配置 写入数据库记录
        MvcBuilder::core('SqlBuilder')->initData($post)->update();

        if(MvcBuilder::$error !== false) $this->error(MvcBuilder::$error);

        $this->success('编辑成功');

    }
    /**
     *[menu]删除[/menu]
     */
    public function del(){
        $post = input('post.');

        $flag = $this->models->_del($post['id']);
        if(!$flag)$this->error($this->models->getError());
        $this->success('删除成功');
    }

    /**
     *[menu]批量删除[/menu]
     */
    public function pdel(){
        $post = input('post.');

        foreach($post['ids'] as $k => $v){
            $flag = $this->models->_del($v);
            if(!$flag)$this->error($this->models->getError());
        }

        $this->success('删除成功');
    }

    /**
     *  逆向生成表单模型 通过数据库
     *  [menu]逆向模型[/menu]
     */
    public function reverse(){
        if(Request::instance()->isPost()){
            $post = input('post.');
            $table = $post['choosed'];

            $isset = model\Models::where(['table_name' =>str_replace(C('database.prefix'),'',$table)])->value('id');
            if($isset){
                $this->result('',0,'模型已经存在models_id:' .$isset);
            }

            $componet = ReverseModel::init($table)->get_componet();
            if($componet == false){
                $this->result('',0,'获取组件失败');
            }

            //逆向模型数据data 放入session
            session('reverse_data',ReverseModel::$data);
            $table_name = str_replace(C('database.prefix'),'',$table);

            $this->result($componet,1,$table_name);
        }


        if(Request::instance()->isGet()){
            $tables = [];
            $sql = 'show table status';
            $res = Db::query($sql);
            foreach($res as $k => $v){
                if(in_array($v['Name'] ,['jy_menu','jy_models','jy_models_fields','jy_module','jy_manager','jy_role']))continue;
                $tables[$k]['name'] = $v['Name'];
            }
            $this->assign('tables',$tables);
            $this->assign('componet',$this->get_right_compont());
            return $this->fetch();
        }

    }
    /**
     * [menu]保存逆向添加模型[/menu]
     */
    public function reverse_add_action(){

        $post = input('post.');

        //models 验证
        $flag = $this->validate($post,'Models.add');

        if($flag !== true)$this->error($flag);


        $flag = ModelsMaker::init_data($post,'add')->validate_field_data()->create();

        if($flag === false){
            $this->error(ModelsMaker::getError());
        }


        $this->success('添加成功',url($this->requseturl.'/index'));

    }


    /**
     * 获取右侧拖拽的组件
     * @return mixed
     */
    protected function get_right_compont(){

        $group = FormGroup::get_group();



        $dom = '';

        foreach ($group as $k=>$v){

            foreach($v['componet'] as $sk => $sv){
                if($sv == 'hidden') continue;

                $dom .= FormMaker::build($sv)
                    ->classname($sv.' preview')
                    ->render();
            }


            @$group[$k]['dom'] .= $dom;
            $dom = '';
        }



        return $group;

    }
}