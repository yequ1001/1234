$(document).ready(function(){

});

/**
 * 隐私模式切换
 */
var state;
function privacy($this)
{
    if (state == null) {
        $(".span-content").hide();
        state = true;
        $($this).html("<i class='fa fa-eye-slash'></i> 取消隐藏");
    } else {
        $(".span-content").show();
        state = null;
        $($this).html("<i class='fa fa-eye'></i> 隐藏记事");
    }
}
