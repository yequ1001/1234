var msg_load, msg_load_icon, msg_ok, msg_ok_icon, msg_ng, msg_ng_icon;

$(document).ready(function() {

    // 自动调整body覆盖屏幕高度
    //alert($("#page_main").width());
    $("#page_main").css("minHeight", $(window).height());
    $(".auto-width").outerWidth($("#page_main").width());

    // 界面尺寸动态适应
    $(window).resize(function(){
        // 自动调整body覆盖屏幕高度
        $("#page_main").css("minHeight", $(window).height());
        $(".auto-width").outerWidth($("#page_main").width());
    });

    // 消息提醒
    $message_count = $("#div_message_count").text();
    if ($message_count > 0) {
        layui.use('layer', function(){
            var layer = layui.layer;
            var message = layer.open({
                type: 1,
                area: '70%',
                title: false,
                offset: ['25%', '15%'],
                shade: 0,
                content: '<a onclick="iframe()" style="color: #ED980F; display: block; padding: 15px 0;" id="a_message"><i class="fa fa-envelope"></i> 您收到'+ $message_count +'条站内消息 !</a>',
                cancel: function(index, layero){
                    $.get("/user/message/neglect",function(data,status){
                        if (data !== true) {
                            layer.msg(msg_ng_icon + data, msg_ng);
                        }
                    });
                }
            });
            layer.style(message, {
                background: "rgba(255,240,213,0.95)",
                boxShadow: "0 0 30px rgba(255,255,255,0.6)",
                textAlign: "center",
            });
            // 新消息图标闪烁效果
            twinkle();
        });
    }
    // 设置layui默认参数
    msg_load = {
        time: 0,
        area: '70%',
        offset: ['25%', '15%'],
        shade: 0.3,
        closeBtn: 1,
    };
    msg_load_icon = '<i class="fa fa-refresh fa-spin"></i><br/>';

    msg_ok = {
        time: 500,
        area: '70%',
        offset: ['25%', '15%'],
        shade: 0.3,
        anim: -1,
    };
    msg_ok_icon = '<i class="fa fa-smile-o"></i><br/>';

    msg_ng = {
        time: 0,
        area: '70%',
        offset: ['25%', '15%'],
        closeBtn: 1,
        shade: 0.3,
        anim: -1,
        shadeClose: true,
    };
    msg_ng_icon = '<i class="fa fa-frown-o"></i><br/>';

});

/**
 * 新消息图标闪烁效果
 */
function twinkle()
{
    setInterval(function(){
        $("#a_message .fa-envelope").toggleClass("fa-envelope-open-o");
    }, 800);
}

function iframe()
{
    // 判断终端类型
    var u = navigator.userAgent, app = navigator.appVersion;
    var isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    if (!isIOS) {
        //这个不是ios操作系统
        layer.closeAll();
        layer.open({
            title: '<i class="fa fa-signal"></i> 临时会话窗口',
            offset: '5%',
            type: 2,
            shade: false,
            area: ['90%', '90%'],
            maxmin: true,
            content: '/user?iframe=true',
            zIndex: layer.zIndex, //重点1
            success: function(layero){
                layer.setTop(layero); //重点2
            }
        });
    } else {
        location.href = '/user?referer='+ location.pathname + location.search;
    }
}

/**
 * 字数统计
 */
function textCount($from, $to)
{
    $from = $($from);
    $($to).text($from.val().length);
    $from.on('input propertychange', function(e){
        $($to).text($from.val().length);
    });
}

/**
 * 获取URL参数
 */
function GET(name){
    var reg = new RegExp( "(^|&)"+ name +"=([^&]*)(&|$)" );
    var r = window.location.search.substr(1).match(reg);
    if( r != null ){
        return decodeURI(r[2]);
    }else{
        return null;
    }
}

/**
 * 实现textarea高度根据内容自适应
 * 调用方法：
 * $(function () {
 *     $("#blog2Form textarea").txtaAutoHeight();
 * });
 */
$.fn.extend({
    txtaAutoHeight: function () {
        return this.each(function () {
            var $this = $(this);
            if (!$this.attr('initAttrH')) {
                $this.attr('initAttrH', $this.outerHeight());
            }
            setAutoHeight(this).on('input', function () {
                setAutoHeight(this);
            });
        });
        function setAutoHeight(elem) {
            var $obj = $(elem);
            if (elem.scrollHeight > 450) {
                return $obj.css({ height: 450, 'overflow-y': 'auto' });
            } else {
                return $obj.css({ height: $obj.attr('initAttrH'), 'overflow-y': 'hidden' }).height(elem.scrollHeight);
            }
        }
    }
});

/**
 * 安全字符
 * 这里转换/屏蔽一些敏感字符串
 */
function safe_str($str)
{
    // 如果本文含有base64_decode字符串，https发送会403出错（不确定所有的服务器是否都有此现象，百度找不到类似案例），
    // 原因至今不明，所以这里要转换一下（不要使用_转换成实体&#95;的方法），同时后端也要配合还原
    $str = $str.replace(/base64_decode/gi, "base64__decode", $str);
    return $str;
}

