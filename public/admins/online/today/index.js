$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('layer', function(){
        var layer = layui.layer;
    });
});

function ua($id)
{
    alert($(".div-item-"+ $id +" input").val());
}

function ip($id, $ip)
{
    $(".div-item-"+ $id +" .a-ip").html($ip +' <i class="fa fa-spinner fa-pulse"></i>');
    $.post("/i",
        {
            ip : $ip,
        },
        function(data,status){
            layer.closeAll();
            $(".div-item-"+ $id +" .a-ip").text($ip +" "+ data);
        });
}