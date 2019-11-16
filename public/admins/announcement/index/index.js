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
    layer.msg(msg_load_icon + "发送中", msg_load);
    $.post("/admins/announcement",
        {
            user : $(".input-user-id").val(),
            content : $("textarea").val(),
        },
        function (data) {
            if (data === true) {
                layer.closeAll();
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}