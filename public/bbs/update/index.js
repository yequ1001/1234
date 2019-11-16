$(document).ready(function(){
    layui.use('layer', function(){
        var layer = layui.element;
    });
    layui.use('element', function(){
        var element = layui.element;
    });
});

function send()
{
    $.post("/bbs/update",
        {
            title : $(".input-title").val(),
            content : $(".textarea-content").val(),
            id : $(".input-id").val(),
        },
        function(data){
            if (data === true) {
                location.href = history.back(-1);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}