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

function show($class)
{
    layer.open({
        title: '编辑预览',
        type: 1,
        shadeClose: true,
        offset:  ['50px', '5%'],
        area:  ['90%', '360px'],
        content: $($class).html(),
    });
}

function send($class)
{
    layer.confirm('确定要执行当前更改吗？', {offset: '110px'}, function(index){
        layer.msg(msg_load_icon +"正在设置", msg_load);
        switch ($class) {
            case ".textarea-header":
                $.post(location.pathname,
                    {
                        header : $($class).html(),
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
                break;
            case ".textarea-footer":
                $.post(location.pathname,
                    {
                        footer : $($class).html(),
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
                break;
        }
        layer.close(index);
    });
}