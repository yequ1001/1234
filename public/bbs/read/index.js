$(document).ready(function(){
    layui.use('layer', function(){
        var layer = layui.element;
    });
    layui.use('element', function(){
        var element = layui.element;
    });

    $("ul .active").text("第"+ $("ul .active").text() +"页");

    if (GET("page") > 1) {
        $("#div_content").hide();
    }

    $(".input-pageUrl").val(location.pathname + location.search);
});

function del($id, $type)
{
    layer.confirm('确定要删除当前帖子吗？', {offset:'110px'}, function(index){
        layer.msg(msg_load_icon +"正在删除", msg_load);
        $.post("/bbs/delete",
            {
                id : $id,
            },
            function(data){
                console.log(data);
                if (data === true) {
                    location.href = "/bbs/"+ $type;
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        );

        layer.close(index);
    });
}

function comment($id, $bind)
{
    layer.msg(msg_load_icon +"发送中", msg_load);
    // 获取艾特的人
    $at = "";
    $(".div-comment .layui-btn").each(function(){
        $at += $(this).data("user") +"|";
    });
    $.post("/bbs/comment",
        {
            id : $id,
            object : $bind,
            content : $(".div-comment").html(),
            type : $(".input-type").val(),
            author : $(".input-author").val(),
            at : $at,
            pageUrl : $(".input-pageUrl").val(),
            pageTitle : $(".input-pageTitle").val(),
        },
        function(data){
            if (data === true) {
                location.reload();
            } else {
                console.log(data);
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
}

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

/* 插入图片 */
function insertFace($face)
{
    layer.closeAll();
    insertHtml("<img src='/public/static/face/"+$face+"' style='height:"+ $face.substr(2,2) +"px' />", ".div-comment");
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

/**
 * 艾特@功能
 */
var i = 0;
function at($id, $nickname, $at)
{
    $("."+ $at).html("@Ta OK");
    var sensor = $('<pre>@'+ $nickname +'</pre>').css({display: 'none'});
    $('body').append(sensor);
    var width = sensor.width() +18;
    sensor.remove();
    // 以下的 readonly/> 万万不可更改格式，会使艾特消息提醒出现bug
    $obj = "<input class='input-auto-"+i+" layui-btn font click' onclick_temp=\"at('"+ $id +"', '"+ $nickname +"')\" style='color: #037FFF; width: "+width+"px' data-user='"+ $id +"' value='@"+$nickname+"' readonly=\"readonly\"/>，";
    $(".div-comment").append($obj);
    i ++;
}

var textWidth = function(text){
    var sensor = $('<pre>'+ text +'</pre>').css({display: 'none'});
    $('body').append(sensor);
    var width = sensor.width() +10;
    sensor.remove();
    return width;
};

/**
 * 将目录当前标题滚动到屏幕中间位置
 */
function scrollPosition(pElementId)
{
    if ($(pElementId).length <= 0) {
        return false;
    }
    var tTop = jQuery(pElementId).offset().top;  //得到控件Top
    var tWindowHeight = jQuery(window).height(); //浏览器可视窗口高度
    var tElementHeight = jQuery(pElementId).height(); //控件高度
    var tScrollTop = tTop-tWindowHeight*0.3-tElementHeight*0.5; //让控件中心位于可视窗口3分之1处
    jQuery('#menu').animate({
        scrollTop: tScrollTop
    }, 1000);
}

// 设置帖子最大显示高度
var roll_maxHeight = 250;

$(document).ready(function(){
    // 遍历所有的1、2级帖
    $(".roll").map(function(){
        if ($(this).height() > roll_maxHeight +50) {
            // 获取当前帖子内容的编号
            $id = $(this).data("roll");
            // 设置当前帖子的最大显示高度
            $(this).css("maxHeight", roll_maxHeight);
            // 被折叠时显示展开按钮
            $(".p-roll-"+ $id).html("<a onclick=\"spread('"+$id+"')\" class='click'>展开剩余<i class='fa fa-angle-double-down'></i></a>");
        }
    });
});

/**
 * 展开被折叠的2级帖
 */
function spread($id) {
    // 展开后的高度
    $height = $("."+ $id +" .div-roll").height();
    // 展开动画
    $("."+ $id).animate({maxHeight: $height}, 700);
    // 收起按钮
    $(".p-roll-"+ $id).html("<a onclick=\"fold('"+$id+"')\" class='click'>收起内容<i class='fa fa-angle-double-up'></i></a>");
}

/**
 * 收起被展开的2级帖
 */
function fold($id) {
    // 收起按钮
    $("."+ $id).animate({maxHeight: roll_maxHeight}, 700);
    // 展开按钮
    $(".p-roll-"+ $id).html("<a onclick=\"spread('"+$id+"')\" class='click'>展开剩余<i class='fa fa-angle-double-down'></i></a>");
}

/**
 * 删除评论
 */
function comment_del($id)
{
    layer.confirm('确定删除这条评论吗？',{offset: '110px'}, function(index){
        layer.msg(msg_load_icon +"正在删除", msg_load);
        $.ajax({
            type: 'DELETE',
            url: '/bbs/comment/delete',
            data: {
                id: $id,
            },
            success: function(data){
                if (data === true) {
                    layer.msg(msg_ok_icon +"删除成功", msg_ok);
                    $(".comment-"+ $id +" .div-roll").html("<small style='color: #8E8E8E'>[ 评论已删除 ]</small>");
                    $(".div-item-"+ $id +" .span-comment-del").hide();
                } else {
                    console.log(data);
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            },
            error: function(data){
                console.log(data);
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });

        layer.close(index);
    });
}