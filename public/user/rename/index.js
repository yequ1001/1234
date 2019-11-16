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

    layui.use('layer', function(){
        var layer = layui.layer;
    });
});

function rename() {

    layer.confirm('下次允许改名是60天后，阁下想好了吗？', {
        offset: ['110px','10%'],
        area: '80%',
        btn: ['想好了', '取消']
    }, function(index, layero){
        layer.msg(msg_load_icon +"正在更新", msg_load);
        $.post("/user/rename",
            {
                newNickname : $(".input-nickname").val(),
            },
            function(data){
                if(data === true){
                    layer.msg(msg_ok_icon +"改名成功", msg_ok);
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        );
    }, function(index){
        //按钮【按钮二】的回调
    });
}