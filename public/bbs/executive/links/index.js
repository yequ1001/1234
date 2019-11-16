$(document).ready(function(){
    layui.use('layer', function(){
        var layer = layui.element;
    });
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('form', function(){
        var form = layui.form;
    });

});

function send()
{
    $name = "";
    $url = "";
    $(".input-name").each(function() {
        $name += $(this).val() +"|";
    });

    $(".input-url").each(function() {
        $url += $(this).val() +"|";
    });

    layer.confirm('确定要执行当前更改吗？', {offset: '110px'}, function(index){
        layer.msg(msg_load_icon +"正在设置", msg_load);
        $.post(location.pathname,
            {
                name : $name,
                url : $url,
            },
            function(data){
                console.log(data);
                if (data === true) {
                    layer.msg(msg_ok_icon +"设置成功", msg_ok);
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        );
        layer.close(index);
    });
}
