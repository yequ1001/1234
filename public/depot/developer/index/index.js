$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('form', function(){
        var form = layui.form;
    });

    if (GET("type") == "server" || GET("type") == null) {
        $(".layui-tab-content .layui-tab-item:first-child").addClass("layui-show");
        $(".layui-tab-title li:first-child").addClass("layui-this");
    } else {
        $(".layui-tab-content .layui-tab-item:last-child").addClass("layui-show");
        $(".layui-tab-title li:last-child").addClass("layui-this");
    }
});
