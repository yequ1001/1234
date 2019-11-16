/**
 * Created by YEQU1001 on 2019/4/20.
 */
$(document).ready(function(){
    var editor = new baidu.editor.ui.Editor();
    editor.render("contents");
    // 编辑器标题字数统计
    textCount("#div_title_input", "#div_title_count");

    //获取id
    $id = window.location.pathname.substring(23);

    $.get("/admins/"+ $id, function(data,status){
        $("#div_title_input").val(data.title);
        $("#ueditor_0").contents().find("body").html(data.content);
        $source = data.source;
        if ($source != "reprint") {
            $("#div_source_textarea").val($source);
            $('input:radio[name=div_source_input]')[1].checked = true;
        } else {
            $('input:radio[name=div_source_input]')[0].checked = true;
        }
        $("#div_type_select").get(0).value = data.type;
    });
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
    $.post("/admins/article/update/"+ window.location.pathname.substring(23),
        {
            title   : $("#div_title_input").val(),
            content : $content,
            type    : $("#div_type_select").val(),
            source  : $source,
        },
        function(data){

            if (data === "OK") {
                alert("更新成功！");
            } else {
                alert(data);
            }
            // 重新激活发送按钮
            $("#but_send").removeAttr("disabled").text("　　提　交　　");
        });
}
