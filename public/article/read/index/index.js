$(document).ready(function(){

});

function reply_send()
{
    $.post("/article/reply",
        {
            id          : window.location.pathname.substring(9),
            content     : $("#textarea_reply").val(),
            captcha     : $("#input_captcha").val(),
        },
        function(data,status){
            if (data === "OK") {
                $("#img_captcha").click();
                $(".close").click();
                $("#textarea_reply").val("");
                $("#input_captcha").val("");
                alert("评论成功！");
                window.location.reload();
            } else {
                $("#img_captcha").click();
                $("#input_captcha").val("");
                alert(data);
            }
        });
}