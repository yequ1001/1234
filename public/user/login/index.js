$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });

    layui.use('layer', function() {
        var layer = layui.layer;
    });

    if (!navigator.cookieEnabled) {
        layer.msg(msg_ok_icon +"检测到浏览器cookie未打开，您将不能正常登陆", msg_ng);
    }
});

/**
 * 登录操作
 */
function login()
{
    // 连接服务器，提交登录表单
    send();
}

/**
 * 连接服务器，提交登录表单
 */
function send()
{
    $.post("/user/login",
        {
            username    : $("#input_username").val(),
            password    : $("#input_password").val(),
            captcha     : $("#input_captcha").val(),
        },
        function (data) {
            if (data === true) {
                layer.msg(msg_ok_icon +"登录成功，正在跳转", msg_load);
                setTimeout(function(){
                    location.href = "/";
                }, 1000);
            } else if (data.substr(0,9) == "redirect:") {
                layer.msg(msg_ok_icon +"登录成功，正在返回登录前页面", msg_load);
                setTimeout(function(){
                    location.href = data.substr(9);
                }, 1000);
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
        // 立即响应等待
        layer.msg(msg_load_icon +"正在登录", msg_load);
        //$(".d1").html("<i class='fa fa-spinner fa-pulse'></i> 正在验证");
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
                    $("#TencentCaptcha").html("验证失败，点我再试试");
                }
            });
    }
}