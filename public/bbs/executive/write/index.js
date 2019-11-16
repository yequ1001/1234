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

    $input_hidden_write = $(".input-hidden-write").val();
    $("[name='write'][value='"+ $input_hidden_write +"']").prop("checked", "checked");
    $input_hidden_reply = $(".input-hidden-reply").val();
    $("[name='reply'][value='"+ $input_hidden_reply +"']").prop("checked", "checked");
});

function send()
{
    layer.confirm('确定要执行当前更改吗？', {offset: '110px'}, function(index){
        layer.msg(msg_load_icon +"正在设置", msg_load);
                $.post(location.pathname,
                    {
                        type  : $(".input-hidden-type").val(),
                        authority_write : $('input[name="write"]:checked').val(),
                        authority_reply : $('input[name="reply"]:checked').val(),
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