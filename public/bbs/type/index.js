$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });

    if ($("#diy_header").html().replace(/\s*/g,"") == "") {
        $("#diy_header").hide();
    }
    if ($(".div-top").html().replace(/\s*/g,"") == "") {
        $(".div-top").hide();
    }
    if ($("#diy_footer").html().replace(/\s*/g,"") == "") {
        $("#diy_footer").hide();
    }
	$("#diy_header").css("fontSize", "16");
});