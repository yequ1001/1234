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
    layui.use('laydate', function(){
        var laydate = layui.laydate;

        //执行一个laydate实例
        laydate.render({
            elem: '#test1',
            type: 'datetime',
            min: 0,
            max: 365,
            calendar: true,
    });
    });

    $input_hidden_fine = $(".input-hidden-fine").val();
    $("[name='s1'][value='"+ $input_hidden_fine +"']").prop("checked", "checked");

});

function send()
{
    layer.confirm('确定要执行当前更改吗？', {offset: '110px'}, function(index){
        layer.msg(msg_load_icon +"正在设置", msg_load);
        $.post(location.pathname,
            {
                state : $("input[name='s1']:checked").val(),
                blacklist : $("#test1").val(),
                user : $(".input-hidden-user").val(),
                nickname : $(".input-hidden-nickname").val(),
                face : $(".input-hidden-face").val()
            },
            function(data){
                console.log(data);
                if (data === true) {
                    layer.msg(msg_ok_icon +"操作成功", msg_ok);
                    // 返回帖子
                    //location.href = location.pathname.replace(/\/operate/g, ".html");
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        );

        layer.close(index);
    });
}