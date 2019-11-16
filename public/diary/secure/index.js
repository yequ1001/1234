$(document).ready(function(){
    layui.use('layer', function(){
        var layer = layui.element;
    });
    layui.use('element', function(){
        var element = layui.element;
    });

    // 使输入框获得光标
    $("input")[0].focus();
});

/**
 * 发送安全码
 */
function send()
{
    layer.msg(msg_load_icon +"正在提交", msg_load);
    $.post("/diary/secure",
        {
            password2 : $("input").val(),
        },
        function(data,status){
            if (data === true) {
                location.href = "/diary/month";
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}
