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
});
