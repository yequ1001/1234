$(document).ready(function(){
    layui.use('element', function() {
        var element = layui.element;
    });
    layui.use('layer', function(){
        var layer = layui.layer;
    });
    layui.use('form', function(){
        var form = layui.form;
        form.on('select()', function(data){
            $arr = data.value.split('-');
            switch ($arr[0]) {
                case "del":
                    layer.confirm('确定删除？'+ $(".title-"+ $arr[1] +" p:last-child").text(), function(index){
                        del($arr[1]);
                        layer.close(index);
                    });
                    break;
                case "save":
                    save($arr[1]);
                    break;
            }
        });
    });
});

function save($id)
{
    $.post("/admins/diary/history",
        {
            id      : $id,
            date    : $(".title-"+ $id +" .date").text(),
            title   : $(".title-"+ $id +" p:last-child").text(),
            content : $(".content-"+ $id).text(),
        },
        function(data,status){
            if (data === true) {
                layer.msg(msg_ok_icon +"保存成功", msg_ok);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}

function del($id)
{
    $.ajax({
        url: '/admins/diary/history',
        type: 'DELETE',
        data: {
            id : $id
        },
        success: function (data) {
            if (data === true) {
                layer.msg(msg_ok_icon +"删除成功", msg_ok);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    });
}