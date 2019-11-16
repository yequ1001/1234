$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('form', function(){
        var form = layui.form;
    });
    $("textarea").txtaAutoHeight();
});

function insertCode()
{
    $("textarea").insertAtCaret("\n[code]\n\n[/code]\n");
}

function insertHtml()
{
    $("textarea").insertAtCaret("[html][/html]");
}

/**
 * 在光标位置插入字符，调用方式：
 * $("#textareaId").insertAtCaret("新表情");
 */
(function($){
    $.fn.extend({
        insertAtCaret: function(myValue){
            $t = $(this)[0];
            if (document.selection) {
                // this.focus();
                // sel = document.selection.createRange();
                // sel.text = myValue;
                // this.focus();
            } else if ($t.selectionStart || $t.selectionStart === 0) {
                // 这是网上复制的代码，好像只有当前从句在运作，其它从句暂没发现起作用
                var startPos = $t.selectionStart;
                var endPos = $t.selectionEnd;
                var scrollTop = $t.scrollTop;
                $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                this.focus();
                $t.selectionStart = startPos + myValue.length;
                $t.selectionEnd = startPos + myValue.length;
                $t.scrollTop = scrollTop;
            } else {
                // this.value += myValue;
                // this.focus();
            }
        }
    })
})(jQuery);

/**
 * 提交表单
 */
function save()
{
    layer.msg(msg_load_icon +"正在提交", msg_load);
    $title = $(".input-title").val();
    $title = safe_str($title);
    $content = $("textarea").val();
    $content = safe_str($content);
    $.post("/depot/developer/write",
        {
            id : $(".input-id").val(),
            title : $title,
            content : $content,
            type : $(".select-type").val(),
            belong : $(".select-belong").val(),
        },
        function(data,status){
            if (data === true) {
                layer.msg(msg_ok_icon +"提交成功", msg_ok);
                history.go(-1);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}