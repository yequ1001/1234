$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
    layui.use('form', function(){
        var form = layui.form;
        form.on('select()', function(data){
            location.href = data.value;
        });
    });

    if(!$(".pagination")[0]) {
        $("#div_page_turning").hide();
    }

    $(".pagination li:first-child a").attr('href','/admins/depot/message?page=1');
    $(".pagination li:last-child a").attr('href','/admins/depot/message?page='+ $(".hidden-pages").val());
});

function reply($id)
{
    layer.prompt({
        formType: 2,
        value: $(".reply-"+ $id).text(),
        title: '输入回复内容',
        offset: '60px',
        area: ['250px', '60px'] //自定义文本域宽高
    }, function(value, index, elem){
        $.post("/admins/depot/message",
            {
                id: $id,
                content : value,
            },
            function(data,status){
                if (data === true) {
                    layer.msg(msg_ok_icon + "回复成功", msg_ok);
                    $(".reply-"+ $id).text(value);
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            });
        layer.close(index);
    });
}

function del($id)
{
    layer.confirm('确定要删除这条留言？', {offset: '15%'}, function(index){
        $.ajax({
            url: '/admins/depot/message?id='+ $id,
            type: 'DELETE',
            success: function(data) {
                if (data === true) {
                    layer.msg(msg_ok_icon + "删除成功", msg_ok);
                    $(".item-"+ $id).css("display", "none");
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        });
        layer.close(index);
    });
}

function batchDelete()
{
    layer.confirm('确定要删除这些留言？', {offset: '15%'}, function(index){
        $.ajax({
            url: '/admins/depot/message?start='+ $(".input-min").val() +'&end='+ $(".input-max").val(),
            type: 'DELETE',
            success: function(data) {
                if (data === true) {
                    layer.msg(msg_ok_icon + "删除成功", msg_ok);
                    location.reload();
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                }
            }
        });
        layer.close(index);
    });
}