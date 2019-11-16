$(document).ready(function(){
    //设置默认的用户称呼
    $("#nickname").val("新会员"+ parseInt(Math.random() * 8999 + 1000) );

    layui.use('layer', function() {
        var layer = layui.layer;
    });
});

/**
 * 发送数据
 */
function postData()
{
    // 立即响应等待
    layer.msg(msg_load_icon +"正在提交注册", msg_load);

    // 连接服务器，提交登录表单
    send();
}

/**
 * 连接服务器，发送/接收数据
 */
function send()
{
    $.post("/user/register",
        {
            username            : $("#username").val(),
            nickname            : $("#nickname").val(),
            password            : $("#password").val(),
            password_confirm    : $("#password_confirm").val(),
            mobi                : $("#mobi").val(),
            captcha             : $("#captcha").val(),
        },
        function(data){
            if (data === true) {
                layer.msg(msg_ok_icon +"注册成功，正在前往登录页面", msg_ok);
                setTimeout(function(){
                    location.href = "/user/login";
                }, 800);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}

function help($type)
{
	$("#p_help").fadeIn(400);
    $("#p_help").outerWidth($("#page_main").width());
    switch ($type) {
        case "username":
            $("#p_help").text("只能用字母、数字，且以字母开头，字数4~11");
            break;
        case "nickname":
            $("#p_help").text("只能使用汉字、字母和数字，字数1~8");
            break;
        case "password":
            $("#p_help").text("字母数字组合，长度4~11位");
            break;
        case "password_confirm":
            $("#p_help").text("需要和 密码1 完全一样");
            break;
        case "mobi":
            $("#p_help").text("输入11位手机号码");
            break;
        case "captcha":
            $("#p_help").text("输入短信收到的验证码");
            break;
    }
}

/**
 * 发送短信验证码
 */
window.callback = function(res){
    // res（用户主动关闭验证码）= {ret: 2, ticket: null}
    // res（验证成功） = {ret: 0, ticket: "String", randstr: "String"}
    if(res.ret === 0){
        layer.msg(msg_load_icon +"正在申请验证码", msg_load);
        $("#TencentCaptcha").hide();
        $.post("/api/tencentCaptcha",
            {
                ticket: res.ticket,
                randstr : res.randstr,
            },
            function(data){
                if (data === true) {
                    sms();
                } else {
                    $("#TencentCaptcha").show();
                    console.log(data);
                }
            });
    }
};

/**
 * 申请短信验证
 */
function sms()
{
    $.post("/user/register/sms",
        {
            mobi : $("#mobi").val(),
            username : $("#username").val(),
            nickname : $("#nickname").val(),
        },
        function(data,status){
            console.log(data);
            if (data === true || data.substr(0,4) === "HTTP") {
                layer.closeAll();
                if (data.substr(-6) === "false}") {
                    alert("短信发送失败，请留言提醒管理员，谢谢");
                } else {
                    alert("发送成功，请留意验证码短信；如果长时间没有收到信息，请检查垃圾短信或者重新发送。");
                }
                $("#TencentCaptcha").show();
            } else {
                layer.msg(msg_ng_icon +data, msg_ng);
                $("#TencentCaptcha").show();
            }
        });
}