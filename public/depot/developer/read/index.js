$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('form', function(){
        var form = layui.form;
    });

    $i = 0;
    $("code").each(function() {
        $text = $(this).html();
        $text = $text.replace(/<br\/?>/gi, "");
        $(this).html($text);
    });

    $("code").each(function() {
        $text = $(this).html();
        $text = $text.replace(/<br\/?>/gi, "\n");
        $text = $text.replace(/<[^>]+>/g, "");
        $(this).after('<textarea id="textarea_copy_'+ $i +'">'+ $text +'</textarea><a class="a-copy layui-btn layui-btn-primary" data-clipboard-action="copy" data-clipboard-target="#textarea_copy_'+ $i +'" onclick="copy(\'#textarea_copy_'+ $i +'\')">一键复制</a><br/>');
        $i ++;
    });

    // 代码视图显示行数
    lineNumber();

});

// 清除文本选中状态
var clearSlct= "getSelection" in window ? function(){
    window.getSelection().removeAllRanges();
} : function(){
    document.selection.empty();
};

function lineNumber() {
    $("pre code").each(function(){
        $text = $(this).html();
        $text = $.trim($text);
        $arr = $text.split("\n");
        $newText = "";
        $i = 1;
        console.log($arr);
        $.each($arr, function(index, value){
            $nbsp = value.match(/^ +/);
            if ($nbsp != null) {
                $nbsp = $nbsp[0].replace(/  /g, " "); // 此行将显示视图每行代码开头4空格减到2空格便于浏览
                $nbsp = $nbsp.replace(/ /g, "&nbsp;");
                value = value.replace(/^ +/, $nbsp);
            }
            if ($i < 10) {
                $newText += "<small>0"+ $i +"</small>"+ value +"<br/>";
            } else {
                $newText += "<small>"+ $i +"</small>"+ value +"<br/>";
            }
            $i ++;
        });
        $(this).html($newText);
    });
}

// 一键复制辅助按钮
function copy($obj) {
    layer.msg(msg_load_icon +"正在复制", msg_load);
    // $obj必须是键盘可输入类型的元素，比如input和textarea
    $obj = $($obj);
    $obj.blur(); // 使输入框失去焦点
}

function del($id)
{
    layer.confirm('您确定要删除这篇文章？', function(index){
        $.ajax({
            url: '/depot/developer/write',
            type: 'DELETE',
            data: {id: $id},
            success: function(data) {
                if (data == true) {
                    layer.msg(msg_ok_icon +"删除成功", msg_ok);
                    history.go(-1);
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        });
        layer.close(index);
    });
}