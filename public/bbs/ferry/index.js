$(document).ready(function(){
    layui.use('layer', function() {
        var layer = layui.layer;
        if ($(".input-error").val() != 0) {
            layer.alert($(".input-error").val(), {area: '80%',offset: ['120px', '10%'],closeBtn: 0}, function(index){
                history.back(-1);
                layer.close(index);
            });
        }
    });

    layui.use('element', function() {
        var layer = layui.element;
    });

    layui.use('form', function() {
        var layer = layui.form;
    });
	
	$(".a-create").attr("href", "/bbs/write?keywords="+ $(".div-tips").data("keywords")); 
});

function send()
{
    layer.msg(msg_load_icon +"正在提交", msg_load);

    $.post("/bbs",
        {
            name : $(".input-keywords").val(),
        },
        function (data) {
            if (data === true) {
                alert('OK');
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}