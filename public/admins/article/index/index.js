/**
 * Created by YEQU1001 on 2019/4/20.
 */
$(document).ready(function(){
    var editor = new baidu.editor.ui.Editor();
    editor.render("contents");
    // 编辑器标题字数统计
    textCount("#div_title_input", "#div_title_count");
});

/**
 * 提交表单
 */
function send()
{
    // 禁用提交按钮，避免用户重复点击
    $("#but_send").attr("disabled", 'disabled').html("　<i class='fa fa-refresh fa-spin'></i> 发送中....　");
    // 获取表单文章内容
    $content = $("#ueditor_0").contents().find("body").html();
    // 获取表单文章来源
    $source = $("input[name='div_source_input']:checked").val();
    if ($source === "reprint") {
        $source = $("#div_source_textarea").val();
    }
    // 提交表单
    $.post("/admins/article/save",
        {
            title   : $("#div_title_input").val(),
            content : $content,
            type    : $("#div_type_select").val(),
            source  : $source,
        },
        function(data){

            if (data === "OK") {
                clean();
            } else {
                alert(data);
            }
            // 重新激活发送按钮
            $("#but_send").removeAttr("disabled").text("　　提　交　　");
        });
}

/**
 * 清空表单
 */
function clean()
{
    $("#ueditor_0").contents().find("body").html("");
    $("#div_title_input").val("");
}