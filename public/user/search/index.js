$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });

    layui.use('layer', function(){
        var layer = layui.layer;
    });

    select();
});

function select() {
    //当用户点击提交按钮时，立即打开模态框并提示信息
    $("#div_list").html("<p class='load'><i class='fa fa-refresh fa-spin'></i> 正在查询</p>");
    $.post("/user/search",
        {
            keyWord : $("input").val(),
            keyType : $("select").val()
        },
        function(data){
            console.log(data);
            if (data.length == 0) {
                $("#div_list").html("<p>　　　没有找到相关的人！</p>");
                return;
            }
            $("#div_list").html("");
            for ($i = 0; $i < data.length; $i++) {
                $id         = data[$i]["id"];
                $nickname   = data[$i]["nickname"];
                $face       = data[$i]["face"];
                $autograph  = data[$i]["autograph"];
                $days       = data[$i]["days"];
                if ($autograph === null) {
                    $autograph = "";
                }
                $html = "<div class='div-item' onclick='location.href = \"/user/card/"+ $id +"\"'>" +
                        "<div class='div-img'>" +
                        "<img src='" + $face + "' />" +
                        "</div>" +
                        "<div class='div-nickname'>" +
                        "<p class='nickname'>" + $nickname + " <span class='layui-badge layui-bg-orange'>Lv."+ $days +"</span></p>" +
                        "<p class='autograph'>" + $autograph + "</p>" +
                        "</div>" +
                        "</div>";
                $("#div_list").append($html);
            }
        }
    );
}