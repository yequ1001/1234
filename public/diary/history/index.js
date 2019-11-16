$(document).ready(function(){
    layui.use('element', function() {
        var element = layui.element;
    });
    layui.use('layer', function() {
        var layer = layui.layer;
    });
});

function openHistory($id)
{
	$(".item-"+ $id +" p").css("color", "#CBCBCB");
    layer.open({
        type: 1,
        title : $('.title-'+ $id).text(),
        area: ['90%', '75%'],
        offset: ['50px', '5%'],
        scrollbar: false,
        shadeClose: true,
        content: $(".id-"+ $id).html()
    });
}