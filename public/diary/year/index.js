$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;

    });
	layui.use('layer', function() {
        var layer = layui.layer;

		if ($(window).width() > 970 && GET("confirm") != "no") {
			layer.confirm('检测到您的设备是大屏幕，建议使用电脑模式浏览，是否开启电脑模式？', {offset: '210px', title:'电脑模式'},function(index){
				//do something
				location.href = "/diary/year_pc/"+GET("y");
				layer.close(index);
			}); 
		}
    });
	
	
});
