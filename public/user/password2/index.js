$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });

    layui.use('form', function(){
        var form = layui.form;
        form.on('select()', function(data){
            location.href = data.value;
        });
    });

    layui.use('layer', function() {
        var layer = layui.layer;
    });
});

function update()
{
    layer.msg(msg_load_icon +'正在更新', msg_load);

    $.post("/user/password2",
        {
            password : $(".password").val(),
            password2 : $(".password2").val(),
        },
        function(data){
            console.log(data);
            if (data === true) {
                layer.msg(msg_ok_icon +'更新成功！', msg_ok);
            } else {
                layer.msg(msg_ng_icon +data, msg_ng);
            }
        }
    );
}