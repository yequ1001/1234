$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('form', function(){
        var form = layui.form;
        form.on('select()', function(data){
            $.post("/admins/links/recommend",
                {
                    recommend : data.value
                },
                function(data,status){
                    if (data === true) {
                        layer.msg(msg_ok_icon +"设置成功", msg_ok);
                    } else {
                        console.log(data);
                        layer.msg(msg_ng_icon + data, msg_ng);
                    }
                });
        });
    });

    /**
     * 分页
     */
    if(!$(".pagination")[0]) {
        $("#div_page_turning").hide();
    }

    $(".pagination li:first-child a").attr('href','/depot/message?page=1');
    $(".pagination li:last-child a").attr('href','/depot/message?page='+ $(".hidden-pages").val());

});

function update($id)
{
    layer.msg(msg_load_icon +"正在处理，可能需要点时间", msg_load);
    $.post("/admins/links",
        {
            id  : $id,
            url_main : $(".item-"+ $id +" .input-url-main").val(),
        },
        function(data){
            if (data === true) {
                location.reload();
            } else {
                console.log(data);
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}

function del($id)
{
    layer.confirm('确定删除 '+ $(".item-"+ $id +" .a-link").text() +'？', function(index){
        layer.msg(msg_load_icon +"删除中", msg_load);
        $.ajax({
            url: '/admins/links',
            type: 'DELETE',
            data: {id: $id},
            success: function(data) {
                if (data === true) {
                    layer.msg(msg_ok_icon +"删除成功", msg_ok);
                    $(".item-"+ $id).remove();
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        });
        layer.close(index);
    });
}

/**
 * 使元素textarea高度自适应
 * 使用方法：
 * 在元素textarea中添加属性值autoHeight="true"即可，示例：
 * <textarea autoHeight="true"></textarea>
 */
$(function(){
    $.fn.autoHeight = function(){
        function autoHeight(elem){
            elem.style.height = 'auto';
            elem.scrollTop = 0; //防抖动
            elem.style.height = elem.scrollHeight + 'px';
        }
        this.each(function(){
            autoHeight(this);
            $(this).on('keyup', function(){
                autoHeight(this);
            });
        });
    }
    $('textarea[autoHeight]').autoHeight();
})