// 签名输入框
var autograph_input;

// 默认的用户签名，即未更新的签名文本
var autograph_default;

$(document).ready(function(){
    layui.use('form', function(){
        var form = layui.form;
        form.on('select()', function(data){
            location.href = data.value;
        });
    });

    layui.use('element', function(){
        var element = layui.element;
    });

    layui.use('layer', function() {
        var layer = layui.layer;
    });

    // 变量赋值
    autograph_input = $("textarea");
    autograph_default = autograph_input.val();

    // 字数统计
    textCount("textarea", "#div_form .span-count");
});

/**
 * 用户提交新签名
 */
function update() {
    layer.msg(msg_load_icon +'正在更新', msg_load);

    // 当用户没有任何更改时不请求数据
    if (autograph_input.val() === autograph_default) {
        layer.msg(msg_ok_icon +"更新成功", msg_ok);
        return false;
    }
    $.post("/user/autograph",
        {
            newAutograph : autograph_input.val(),
        },
        function(data){
            response(data);
        }
    );
}

/**
 * 提交签名响应
 */
function response($data) {
    // 签名更新成功
    if($data === true){
        layer.msg(msg_ok_icon +"更新成功", msg_ok);
        autographTxt = autograph_input.val();
    }
    // 签名更新失败
    else {
        layer.msg(msg_ng_icon + $data, msg_ng);
    }
}