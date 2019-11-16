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
    layer.msg(msg_load_icon +"正在提交", msg_load);
    $url = $(".input-url").val();
    $.post("/links/save",
        {
            name        : $(".input-name").val(),
            type        : $(".div-select select").val(),
            url         : $url,
            mobi        : $(".input-mobi").val(),
            captcha     : $(".input-captcha").val(),
        },
        function (data) {
            if (!isNaN(data)) {
                layer.alert(':) 合作愉快，本站回链地址：<br/><b>https://www.op112.com/'+ data +'</b>',{
                    btn: [],
                    time: 0,
                    area: '70%',
					shadeClose: true,
                    offset: ['22%', '15%'],
                    cancel: function(index, layero){
                        if(confirm('确定要关闭么，网址复制好了吗？')){
                            layer.close(index);
                            location.href = "/";
                        }
                        return false;
                    },
                });
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
            //当服务器返回任何数据时，要清除验证码输入框，且刷新验证码
            $('.input-captcha').val('');
            $('#img_captcha').trigger("click");
        }
    );
}