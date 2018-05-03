/**
 * Created by Administrator on 2017/5/20.
 * @name:   vip-admin 后台模板 菜单navJS
 * @author: 随丶
 */
;layui.define(['layer', 'element'], function (exports) {
    // 操作对象
    var layer = layui.layer
        , element = layui.element
        , $ = layui.jquery;

    // 封装方法
    var mod = {
        // 添加 HTMl
        addHtml: function (addr, obj, treeStatus, data) {
            // 请求数据
            $.get(addr, data, function (res) {
                var view = "";
                if (res) {
                    $(res).each(function (k, v) {
                        v.son && treeStatus ? view += '<li class="layui-nav-item layui-nav-itemed">' : view += '<li class="layui-nav-item">';
                        if (v.son) {
                            view += '<a href="javascript:;"><i class="fa ' + v.icon + '"></i>' + v.name + '</a><dl class="layui-nav-child">';
                            $(v.son).each(function (ko, vo) {

                                view += '<dd>';
                                if(vo.target){
                                    view += '<a href="' + vo.url + '" target="_blank">';
                                }else{
                                    view += '<a href="javascript:;" href-url="' + vo.url + '">';
                                }
                                view += '<i class="fa ' + vo.icon + '"></i>' + vo.name + '</a></dd>';
                            });
                            view += '<dl>';
                        } else {
                            if (v.target) {
                                view += '<a href="' + v.href + '" target="_blank">';
                            } else {
                                view += '<a href="javascript:;" href-url="' + v.url + '">';
                            }
                            view += '<i class="fa ' + v.icon + '"></i>' + v.name + '</a>';
                        }
                        view += '</li>';
                    });
                } else {
                    layer.msg('接受的菜单数据不符合规范,无法解析');
                }
                // 添加到 HTML
                $(document).find(".layui-nav[lay-filter=" + obj + "]").html(view);
                // 更新渲染
                element.init();
            },'json');
        }
        // 左侧主体菜单 [请求地址,过滤ID,是否展开,携带参数]
        , main: function (addr, obj, treeStatus, data) {
            // 添加HTML
            //this.addHtml(addr, obj, treeStatus, data);
        }
        // 顶部左侧菜单 [请求地址,过滤ID,是否展开,携带参数]
        , top_left: function (addr, obj, treeStatus, data) {
            // 添加HTML
            //this.addHtml(addr, obj, treeStatus, data);
        }
        /*// 顶部右侧菜单
         ,top_right: function(){

         }*/
    };

    // 输出
    exports('vip_nav', mod);
});


