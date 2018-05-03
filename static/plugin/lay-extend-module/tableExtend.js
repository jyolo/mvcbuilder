layui.define(['table'],function (exports) {
    var table = layui.table
    ,$= layui.jquery;


    var handler = {
        /**
         *
         */
        tableID:'',
        /**
         *
         */
        _checkbox:function(obj){
            var tableID = handler.tableID;

            //如果触发的是全选，则为：all，如果触发的是单选，则为：one
            switch(obj.type){
                case 'all':

                    $.each(table.cache[''+tableID+''],function (i,n) {
                        if(typeof n == 'object'){
                            var filter = 'checked_'+n.id;
                            var hidden = '<input type="hidden" id="'+filter+'" value="'+n.id+'" name="ids[]">';
                            if(n.LAY_CHECKED == true){
                                //避免重复的元素，先删除遗留的元素
                                $('.layui-btn[lay-submit]').parents('form').find('#'+filter).remove();

                                $('.layui-btn[lay-submit]').parents('form').append(hidden);
                            }else{

                                $('.layui-btn[lay-submit]').parents('form').find('#'+filter).remove();
                            }
                        }

                    });
                    break;
                case 'one':

                    var filter = 'checked_'+obj.data.id;
                    var hidden = '<input type="hidden" id="'+filter+'" value="'+obj.data.id+'" name="ids[]">';
                    if(obj.checked){
                        $('.layui-btn[lay-submit]').parents('form').append(hidden);
                    }else{
                        $('.layui-btn[lay-submit]').parents('form').find('#'+filter).remove();
                    }
                    break;
            }




        },
        /**
         * 此方法需要外部调用 统一处理工具条的按钮事件
         * layui table tool工具条 handler
         * 按钮标签属性 lay-event="add|edit|action"  可选[ confirm-msg="确定xx？" confirm-type="del" ]
         * example: <span data-url="{:url('menu/del')}"  class="layui-btn " lay-event="action" confirm-msg="确定删除？" confirm-type="del">删除</span>
         * example: table.on('tool(filter)', link.tableToolHandler);
         * @param obj
         */
        _tool:function(obj){

            var data = obj.data;
            var Event = obj.event; //获得 lay-event 对应的值
            var tr = obj.tr; //获得当前行 tr 的DOM对象



            var button = $(this);

            //添加和 编辑 需要 定义 data-field 属性
            if(Event == 'add' || Event == 'edit'){

                //如果设置的 data-field 属性 则iframe 的链接上会带上对应的参数
                if(typeof button.attr('data-field') == 'undefined' ){
                    layer.msg('请先设置data-field 属性');
                    return;
                }

                var data_field = button.attr('data-field').replace(' ','');

                if(data_field.length != 0){
                    data_field = data_field.split(',');
                    var url = _getUrl(button) + '?';
                    $.each(data_field,function(i,n){
                        url += n + '=' + obj.data[n] + '&';
                    });
                    //去掉最后一个&字符
                    url = url.substring(0,url.length-1);
                    //检查是否已经设置过了url
                    if(!button.hasClass('has-set-url')){
                        //重新设置元素的url
                        _setUrl(button ,url);
                    }

                }
            }



            switch (Event){
                case 'openPage':
                    //如果有回调
                    var _callback = new Function('return '+$(this).attr('data-call-back'))();
                    if(typeof _callback == 'function'){
                        layer_loadPage(button ,_callback );
                    }else{
                        layer_loadPage(button);
                    }

                    break;
                //赋值linkhandler 所需熟悉
                case 'edit':
                    var callback ={
                        end : function () {
                            //if(parent.listTable == 'undefined')return;
                            // p(window.table);
                            try{
                                //通过linkhandler submit 方法里面 把全局的listtable 赋值了msg 对象 无刷新即时修改表格
                                // if(typeof parent.listTable.msg == 'object' && parent.listTable.msg.code == 1){
                                //     $.each(parent.listTable.msg.data,function(i,n){
                                //         tr.find('td[data-field='+i+']').find('div').html(n);
                                //     });
                                // }


                                if(typeof window.table.msg == 'object' && window.table.msg.code == 1){

                                    $.each(window.table.msg.data,function(i,n){
                                        tr.find('td[data-field='+i+']').find('div').html(n);
                                    });
                                }
                            }catch (e){
                                console.log(e);
                            }

                        }
                    };

                    layer_loadPage(button ,callback);
                    break;
                case 'action':
                    var confirm_msg = button.attr('confirm-msg');
                    var confirm_type = button.attr('confirm-type');


                    var url = button.attr('data-url');
                    //如果有定义confirm 提示信息
                    if(confirm_msg){

                        parent.layer.confirm(confirm_msg, function (index) {
                            parent.layer.close(index);
                            var pload = parent.layer.load(3);

                            $.ajax({
                                url: url,
                                method:'post',
                                data:data,
                                success: function (msg) {

                                    //删除成功的时候 删掉当前行
                                    if(msg.code == 1){
                                        //根据确认类型 执行不同的操作
                                        switch (confirm_type){
                                            case 'del':
                                                //删除最后一条的时候 table 进行 reload
                                                if(obj.tr.siblings().length == 0){

                                                    var component_table = [];
                                                    //根据组件的设置 ,找出table
                                                    $.each(window.component_set ,function(i,n){
                                                        if(n.component_name == 'table'){
                                                            //component_table.push(n)
                                                            component_table.push(n);
                                                        }
                                                    });
                                                    //前面iframe里面只有一个table的时候
                                                    if(component_table.length == 1){
                                                        var table_id = component_table[0].uniqid_id;
                                                        table.reload(table_id);
                                                    }else{
                                                        //多个的时候 暂未处理
                                                    }

                                                }else{
                                                    obj.del();
                                                }
                                                break;
                                        }

                                        var icon = 6;
                                        var msg_func = function () {
                                            $.each(msg.data,function(i,n){
                                                obj.tr.find('td[data-field='+i+']').find('div').html(n);
                                            });
                                        };
                                    }else{
                                        var icon = 5;
                                        var msg_func = function () {};
                                    }
                                    //关闭loading
                                    parent.layer.close(pload);
                                    //弹出提示
                                    parent.layer.msg(msg.msg, {
                                        icon:icon,
                                        time: 1200 ,
                                        shade: 0.5
                                    },msg_func);


                                }
                            });
                        });

                    }
                    else
                    {

                    }


                    break;

            }
        },
        _edit:function(obj){

            var tableID = handler.tableID;
            var table_attr = window[tableID+'_attr'];

            var set = table_attr.set;
            var url  = set.editUrl ;
            var param = {};
            param[obj.field] = obj.value;
            param['id'] = obj.data.id;

            $.post(url,param,function(msg){
                if(msg.code == 1){
                    if(set.editReload == true){
                        table.reload(tableID);
                    }else{
                        layer.load(3 ,{time: 500});
                    }

                }else{
                    parent.layer.msg(msg.msg, {
                        icon:5,
                        time: 200 ,
                        shade: 0.5
                    });
                }

            });


        },
        /**
         * table 自动化渲染完成 以及 reload 重载之后 统计某一列的数字之和
         * 自动化渲染需要配置  done:layui.LinkHandler.tableDone
         * reload 已经内置 无需配置
         * 1：需要统计的 字段 需在 表单头部 th 中 定义 lay-count="true"
         * 2: 显示统计的结果 需在页面 定义 id="table_字段_count" 的任意标签即可
         * 3: 新增lay-count-filter="{reimbursement_state:'已通过'}" 统计过滤属性，如果定义了 只会统计 满足过滤器中的条件 的行数
         */
        tableDone:function(res, curr, count){
            var count = 0;
            var count_field_arr = {};

            //return false;

            this.elem.find('th').each(function(i,n){
                if($(n).attr('lay-count') == 'true'){
                    var data_obj = new Function("return "+$(n).attr('lay-data'))();
                    var filter = new Function("return "+$(n).attr('lay-count-filter'))();


                    count_field_arr[data_obj.field] = {num:parseFloat(0),filter:filter};
                }
            });

            $(res.data).each(function(i,n){

                for (key in count_field_arr){

                    var number = parseFloat(n[key]);
                    if(!number)continue;
                    //统计标记 为true 循环中就一直继续 false 则跳过
                    var flag = true ;
                    if(count_field_arr[key].filter){
                        var filter = count_field_arr[key].filter;

                        for(sk in filter){

                            if(n[sk] != filter[sk]){

                                flag = false;
                            }
                        }

                    }

                    if(flag){
                        count_field_arr[key].num += number;
                    }

                }
            });

            //统计的数字 遍历到模板
            for(key in count_field_arr){
                $('#table_'+key+'_count').html(count_field_arr[key].num.toFixed(2));
            }

        },

        /*
        * ajax 加载的页面无法执行 自动化渲染
        * extend中自定义初始化渲染
        * */
        render:function (filter) {
            var t = $('table[lay-filter='+filter+']');
            var option = new Function('return '+ t.attr('lay-data'))();
            option.cols = [[]];
            option.elem = t;
            option.elem.find('th').each(function (i,n) {
                var a = new Function('return '+ $(n).attr('lay-data'))();
                a.title = $(n).text();
                option.cols[0].push(a);
            });



            var tableObj = table.render(option);
            tableObj.reload(option);

        },


    };


    exports('tableExtend', handler);
});