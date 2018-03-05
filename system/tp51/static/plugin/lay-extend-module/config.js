/**
 * cookie 插件
 */
(function(w){var Cookies;Cookies={set:function(key,value,day,path,domain){day=day||0.5;path=path||"/";domain=domain||document.domain;document.cookie=key+"="+escape(value)+";expires="+expire(day)+";path="+path+";domain="+domain},get:function(key){return getCookies(key)},remove:function(key,path,domain){path=path||"/";domain=domain||document.domain;document.cookie=key+"=;"+";expires="+expire(-1)+";path="+path+";domain="+domain},clear:function(){clearCookies()},isset:function(key){var _cookies=allCookies(),r=false;for(var i in _cookies){if(trim(_cookies[i][0])===key){r=true;break}}return r},stringify:function(data){return JSON.stringify(data)},parse:function(data){if(typeof(data)!="undefined"&&data!="undefined"&&data.length>2){return JSON.parse(data)}else{return{}}},trim:function(string){return trim(string)},dump:function(data){console.log(data)}};function expire(day){var exp=new Date();exp.setTime(exp.getTime()+day*24*3600*1000);return exp.toUTCString()}function allCookies(){var _cookies;_cookies=document.cookie;_cookies=_cookies.split(";");for(var i in _cookies){_cookies[i]=_cookies[i].split("=")}return _cookies}function getCookies(key){var _cookies=allCookies(),o={};for(var i in _cookies){o[trim(_cookies[i][0])]=_cookies[i][1]}return unescape(o[key])}function clearCookies(){var _cookies=allCookies();for(var i in _cookies){document.cookie=_cookies[i][0]+"="+unescape(_cookies[i][1])+";expires="+expire(-1)}}function trim(string){return string.replace(/(^\s*)|(\s*$)/,"")}w.cookies=Cookies})(window);


/**
 * layui配置
 * */
layui.config({
    base: '/static/plugin/lay-extend-module/' , // 模块目录

}).use(['form','element','linkHandler'],function(){

    layui.form.render();
    layui.linkHandler.init();

});



/**
 * 全局函数
 * */


//打印函数
function p(s){
    console.log(s);
}

//layer 加载页面
function layer_loadPage(el ,callback ){

    var url = _getUrl(el);
    var title = el.text();
    var option = {
        type: 2
        ,anim: 4
        ,maxmin: true
        ,area: ['80%', '46rem']
        ,title: title
        ,content: url
    };



    if(typeof callback == 'object'){
        //对象合并
        option = $.extend(option,callback)
    }else if(typeof callback == 'function'){
        var obj = {
            end:callback
        };

        option = $.extend(option,obj);
    }




    parent.layer.open(option);
}

//获取元素的url
function _getUrl(el){
    if(el[0].tagName == 'A'){
        return el.attr('href');
    }else{
        return el.attr('data-url');
    }
}

//获取元素的url
function _setUrl(el ,url){
    if(el[0].tagName == 'A'){
        el.attr('href',url);
    }else{
        el.attr('data-url',url);
    }
    //标记已经设置过url
    el.addClass('has-set-url');
}

function reload_table(msg) {

    var table_id;

    try{
        var tables = [];
        $.each(window.component_set,function (i,n) {
            tables.push(n);
        });
        //根据按钮的 table_index 索引 获取 table的 id
        table_id = tables[msg.table_index].uniqid_id;
    }catch (e){
        table_id = 'listTable'; //如果上面报错直接固定值默认 listTable
    }

    window.table.reload(table_id);

}