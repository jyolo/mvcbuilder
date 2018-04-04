/**
 * 所有的链接的处理模块
 * A:表单提交 提交按钮 需要 有 layui-btn class 并且 有 lay-submit 属性
 * 一个表单可以有多个 按钮 layui-bn
 * 1：请求的时候 layui-bn 如果有data-url 则优先请求该地址，
 * 2：layui-bn 如果有指定  data-call-back="table_pdel" 指定请求成功的回调函数则 请求成功后会执行该回调函数
 * 回调函数可以定义在本模块的 callbackFunc 里面 或者页面底部 比如 function table_pdel(){//do something)
 *
 * B:普通的连接 需指定 link class 并且指定 data-type 连接的类型
 * (a标签的请求地址 直接用href  非 A 标签 通过 data-url 属性 或 url)
 * 1：page 打开新的页面
 * 2：table-delete 表格里面的删除
 * .....如果有其它的连接类型需求，可以继续在本js里面拓展
 *
 * C：分页连接 需要外部的容器 ul 或者其它的元素需要 指定的 pagination （tp5默认是该class，如果是其它class，直接在本js 修改即可）
 */
var $;
layui.define(['table','tableExtend'],function (exports) {

    $ = layui.jquery
        ,table = layui.table
        ,tableExtend = layui.tableExtend;



    var handler = {

        post:function(el){
            var url = _getUrl(el);
            var data = new Function("return "+el.attr('data-action'))();

            $.ajax({
                url: url,
                method:'post',
                data:data,
                cache:false,
                success: function (msg) {
                    //错误
                    if(msg.code == 0){
                        layer.msg(msg.msg, {
                            icon:5,
                            time: 1600 ,
                            shade: 0.5
                        });

                    }else{ //成功
                        layer.msg(msg.msg, {
                            icon:6,
                            time: 1600 ,
                            shade: 0.5
                        },function () {
                            //如果有传递url 则直接跳转
                            if(msg.url){
                                window.location.href = msg.url;
                                return;
                            }
                        });
                    }


                },
                error: function (msg) {
                    layer.open({
                        type: 1
                        ,anim: 4
                        ,maxmin: true
                        ,area: ['80%', '46rem']
                       // ,title: title
                        ,content: msg.responseText,
                    });
                },
                complete: function () {

                }
            });


            return false;

        },
        //表单的提交
        submitForm:function(){
            var button = this;

            //先unbind 防止提交多次
            $(this).parents('form').unbind('submit').bind('submit',function(){

                var form = $(this) ,url;

                //提交按钮的 data-url  优先级 高于 form 的 action
                if($(button).attr('data-url')){
                    url = $(button).attr('data-url');
                }else{
                    url = form.attr('action');
                }
                var method = form.attr('method') ? form.attr('method') : 'post';
                var data = form.serialize();


                /*
                 * 搜索重载table start
                 * 指定提交事件类型
                 * table-index 表格的索引 0 开始
                 * 按钮实例 example : <button class="layui-btn" lay-submit data-type="search_reload_table" table-index="0">搜索</button>
                 * */

                var type = $(button).attr('data-type');
                if(type == 'search_reload_table'){
                    var component_table = [];
                    //根据组件的设置 ,找出table
                    $.each(window.component_set ,function(i,n){
                        if(n.component_name == 'table'){
                            //component_table.push(n)
                            component_table.push(n);
                        }
                    });

                    //p(component_table);

                    var table_index = $(button).attr('table-index');
                   // p(table_index);

                    var serializeObj={};
                    var array=form.serializeArray();
                    //表单的数字解析成对象
                    $(array).each(function(){
                        if(serializeObj[this.name]){
                            if($.isArray(serializeObj[this.name])){
                                serializeObj[this.name].push(this.value);
                            }else{
                                serializeObj[this.name]=[serializeObj[this.name],this.value];
                            }
                        }else{
                            serializeObj[this.name]=this.value;
                        }
                    });

                    var table_id = component_table[table_index].uniqid_id;
                   // p(url);
                    table.reload(table_id, {
                        url: url
                        ,where: serializeObj //设定异步数据接口的额外参数
                        ,page:1
                        //,done:tableExtend.tableDone
                    });

                    return false;
                }

                /****** 搜索重载 end ****************/


                //检查是否有定义回调
                var callback = $(button).attr('data-call-back') ? $(button).attr('data-call-back') : false ;
                var error_callback = $(button).attr('data-error-call-back') ? $(button).attr('data-error-call-back') : false ;

                var table_index = $(button).attr('table_index');

                var loder;
                $.ajax({
                    type:method,
                    url: url,
                    data:data,
                    dataType:'json',
                    beforeSend:function(){
                        loder = layer.load(3);
                    },
                    complete:function () {
                        layer.close(loder);
                    },
                    success:function(msg){
                        //如果返回的是str 直接返回
                        if(typeof msg == 'string')return ;

                        if(msg.code == 0){//失败
                            parent.layer.msg(msg.msg, {
                                icon:5,
                                time: 1600 ,
                                shade: 0.5
                            },function(){
                                if(error_callback !== false){
                                    var functionname = new Function("return "+error_callback)();
                                    if(typeof functionname == 'function'){
                                        functionname(msg);
                                    }
                                    return;
                                }
                                return ;
                            });
                        }
                        else
                        { //成功

                            parent.layer.msg(msg.msg ? msg.msg : '操作成功', {
                                icon:6,
                                time: 1600 ,
                                shade: 0.5
                            },function () {
                                //如果有自定义回调函数 优先执行 回调函数
                                if(callback !== false){
                                    //如果是定义在外部的function ,则直接调用外部函数
                                    var functionname = new Function("return "+callback)();
                                    if(typeof functionname == 'function'){

                                        if(table_index) msg.table_index = table_index;

                                        functionname(msg);
                                    }
                                    return ;
                                }

                                //如果全局的listTable 不为 undefined 则自动 reload
                                if(typeof parent.listTable != 'undefined'){
                                    parent.listTable.msg = msg;
                                }else{
                                    //单独单开iframe
                                    parent.table.msg = msg
                                }

                               //关闭所有的弹窗
                               parent.layer.closeAll();

                                //如果传递了连接 则完成后跳转
                                if(msg.url){
                                    window.location.href = msg.url;
                                    return;
                                }

                            });
                        }

                    }
                });



                return false;
            });




        },
        /*
        * 获取link的类型
        * page 以layer弹窗的方式打开新的页面
        * table-delete 表格中的 行 删除
        * post 以post方式发送请求
        * */
        getType:function () {
            //防止link 按钮 在form 里面 造成表单的误提交
            $(this).parents('form').submit(function(){
                return false;
            })

            var type = $(this).attr('data-type');



            //没有定义任何类型的 连接则默认是 分页连接
            if(typeof type == 'undefined'){
                var url = $(this).attr('href');
                return;
            }else{
                switch(type){
                    case 'page':
                        var url = _getUrl($(this));
                        //自定义窗口关闭销毁时候的回调
                        var _callback = new Function('return '+$(this).attr('page-end-back'))();

                        if(typeof _callback == 'function'){
                            layer_loadPage($(this),_callback);
                        }else{
                            layer_loadPage($(this));
                        }

                        break;
                    case 'table-delete':
                        var el = $(this);
                        layer.confirm('确定删除', {icon: 3, title:'提示'}, function(index){
                            handler.deleteline(el);
                            layer.close(index);
                        });
                        break;
                    case 'post':
                        handler.post($(this));
                        break;

                }
            }



            return false;
        }
    }

    //外部接口
    var output = {
        init:function () {
            $('.layui-btn[lay-submit]').unbind('click').bind('click',handler.submitForm);
            $('.link').unbind('click').bind('click',handler.getType);

            p('linkhandler inited');
        }
    }
    exports('linkHandler',output);
});
