$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('form', function(){
        var form = layui.form;
    });
});

function send()
{
    layer.msg(msg_load_icon +"正在提交", msg_load);
    $.post("/admins/question",
        {
            question    : $(".input-question").val(),
            answer0     : $(".input-0").val(),
            answer1     : $(".input-1").val(),
            answer2     : $(".input-2").val(),
            answer3     : $(".input-3").val(),
            true        : $('input:radio:checked').val(),
        },
        function (data) {
            if (data == true) {
                layer.msg(msg_ok_icon +"添加成功", msg_ok);
                layer.closeAll();
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}