$(document).ready(function(){
    layui.use('layer', function(){
        var layer = layui.element;
    });
    layui.use('element', function(){
        var element = layui.element;
    });
    if (GET("keywords") != null) {
        $(".p-keywords").html("在新论坛成立之前，您作为创始人兼首席管理员，写下第一篇迎客帖吧。预祝您未来的日子一帆风顺！");
        $(".input-keywords").val(GET("keywords"));
    }
});

function send()
{
    layer.msg(msg_load_icon +"发送中", msg_load);

    $.post("/bbs/write",
        {
            title : $(".input-title").val(),
            content : $(".div-content").html(),
            bind : $(".input-bind").val(),
            type : $(".input-type").val(),
            keywords : $(".input-keywords").val(),
        },
        function(data){
            if (!isNaN(data)) {
                layer.msg(msg_load_icon +"发表成功，正在跳转", msg_load);
                if (GET("type") == "save" || GET("keywords") != null) {
                    location.href = "/bbs/"+ data +".html";
                } else if (GET("type") == "append") {
                    location.href = "/bbs/read/"+ $(".input-type").val() +"/"+ $(".input-bind").val() +".html";
                }
            } else {
                console.log(data);
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}

var faceArr;
function face()
{
    $d = layer.open({
        type: 1,
        title: false,
        area: '80%',
        offset: ['80px', '10%'],
        shadeClose: true,
        anim: 5,
        content: $("#div_faceList").html(),
    });
    layer.style($d, {
        background: '#FFFFFF',
        padding: '8px',
    });
}

function insertFace($face)
{
    layer.closeAll();
    insertHtml("<img src='/public/static/face/"+$face+"' style='height:"+ $face.substr(2,2) +"px' />", ".div-content");
}

/**
 * 在光标位置插入字符，只适用于可编辑的div
 */
function insertHtml(html, div) {
    var sel, range;
    sel = window.getSelection();
    $(div).focus();
    if (sel.getRangeAt && sel.rangeCount) {
        range = sel.getRangeAt(0);
        range.deleteContents();
        var el = document.createElement("div");
        el.innerHTML = html;
        var frag = document.createDocumentFragment(), node, lastNode;
        while ((node = el.firstChild)) {
            lastNode = frag.appendChild(node);
        }
        range.insertNode(frag);
        // 保留所选内容
        if (lastNode) {
            range = range.cloneRange();
            range.setStartAfter(lastNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }
}