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

    if(!$(".pagination")[0]) {
        $("#div_page_turning").hide();
    }

    $(".pagination li:first-child a").attr('href','/depot/message?page=1');
    $(".pagination li:last-child a").attr('href','/depot/message?page='+ $(".hidden-pages").val());
});

function send()
{
    $.post("/depot/message",
        {
            content  : $("textarea").val(),
        },
        function (data) {
            if (data === true) {
                layer.closeAll();
                location.reload();
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}

window.callback = function(res){
    // res（用户主动关闭验证码）= {ret: 2, ticket: null}
    // res（验证成功） = {ret: 0, ticket: "String", randstr: "String"}
    if(res.ret === 0){
		layer.msg(msg_load_icon +"正在处理", msg_load);
        //console.log(res);
        $.post("/api/tencentCaptcha",
            {
                ticket: res.ticket,
                randstr : res.randstr,
            },
            function(data){
                if (data === true) {
					send();
                } else {
                    console.log(data);
					layer.msg(msg_ng_icon +"验证失败，建议刷新重试", msg_ng);
                }
            });
    }
}

function page_turning()
{
    location.href = "/depot/message?page="+ $("#div_page_turning input").val();
}