$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('layer', function() {
        var layer = layui.layer;
    });
    layui.use('form', function() {
        var form = layui.form;
    });
});

function search()
{
    $("#div_list").html("<p class='load'><i class='fa fa-refresh fa-spin'></i> 正在查询</p>");

    // 连接服务器
    send();
}

function send()
{
    $.post("/diary/search",
        {
            key     : $(".input_key").val(),
            year    : $(".select-year").val(),
        },
        function (data) {
            try {
                $("#div_list").html("");
                // 遍历json
                $i = 0;
                $.each(data, function(idx, obj) {
					$html = obj.content.replace(/^· /gi, "");
					$html = $html.replace(/>· /gi, ">");
					$html = $html.replace(/>! /gi, ">");
					$html = $html.replace(/>× /gi, ">");
                    $html = "<h1>"+ obj.date +"</h1><div class='content'>"+ $html +"</div>";
					$("#div_list").append("<div class='item kyy' onclick=\"location.href='/diary/month/"+ obj.date.substring(0,4) +"/"+ obj.date.substring(5,7) +"'\">"+ $html +"</div>");
                    $i ++;
                });
                if ($i === 0) {
                    $("#div_list").html("<div style='margin: 30px 5% 0 5%; color: #8a8a8a'>没有搜索到相关联的结果，<br/><br/>您可以尝试简化关键词或者检查错别字重试！</div>");
                }
            } catch(e) {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}
