$(document).ready(function(){
    layui.use('layer', function() {
        var layer = layui.layer;
    });

    layui.use('element', function() {
        var layer = layui.element;
    });

    layui.use('form', function() {
        var layer = layui.form;
    });
});

function send()
{
    layer.msg(msg_load_icon +"请稍后", msg_load);
}
