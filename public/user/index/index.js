$(document).ready(function(){
    layui.use('element', function(){
        var element = layui.element;
    });
	
    layui.use('form', function(){
        var form = layui.form;
        form.on('select()', function(data){
            if (data.value != "logout") {
                location.href = data.value;
            } else {
                logout();
            }
        });
    });
	
    // 每页对话数量是15，如果首次进入页面既有15个对话，那么启动流加载，加载更多对啊（如果还有的话）
    if ($("#div_message .div-item").length >= 15) {
        flow();
        $("#p_more").remove();
    } else if ($("#div_message .div-item").length == 0) {
	    $("#p_more").remove();
	} else {
        $("#div_message").append("<p id='p_more' style='text-align: center; color: #e5e5e5;font-size: 12px; margin-top: 10px'>没有更多了</p>");
    }

    // 监听页面滚动
    $i = 0;
    $(this).scroll(function() {
        if($(this).scrollTop() >= 145 && $i == 0) {
            $("#div_nav").toggleClass("b2");
            $i = 1;
        }
        if($(this).scrollTop() < 145 && $i == 1) {
            $("#div_nav").toggleClass("b2");
            $i = 0;
        }
    });

});

/**
 * 流加载
 */
function flow()
{
    layui.use('flow', function(){
        var $ = layui.jquery; //不用额外加载jQuery，flow模块本身是有依赖jQuery的，直接用即可。
        var flow = layui.flow;
        flow.load({
            elem: '#div_message', //指定列表容器
            mb: 100,
            done: function(page, next){ //到达临界点（默认滚动触发），触发下一页
                var lis = [];
                //以jQuery的Ajax请求为例，请求下一页数据（注意：page是从2开始返回）
                $.get('/user/message/list?page='+(page+1), function(res){
                    console.log(res);
                    //假设你的列表返回在data集合中
                    layui.each(res, function(index, item){
                        $str = "<div class=\"div-item\" onclick=\"linkMessage("+item.to+")\">"+
                            "<div class='div-img'>"+
                            "<img src='"+item.toFace+"' />"+
                            "</div>"+
                            "<div class='div-content'>"+
                            "<div class='div-line1'>"+
                            "<span>"+item.toNickname+"</span><span>"+item.onlineTag+"</span>"+
                            "</div>"+
                            "<div class='div-line2'>"+
                            "<span>"+item.content+"</span><span>"+item.time+"</span>"+
                            "</div>"+
                            "</div>"+
                            "</div>";
                        lis.push($str);
                    });

                    //执行下一页渲染，第二参数为：满足“加载更多”的条件，即后面仍有分页
                    //pages为Ajax返回的总页数，只有当前页小于总页数的情况下，才会继续出现加载更多
                    next(lis.join(''), page < res.length);
                    if (page = res.length && page != 1) {
                        $("#div_message .div-item:last").remove();
                    }
                });
            }
        });
    });
}

/**
 * 退出登录
 */
function logout()
{
    layui.use('layer', function(){
        var layer = layui.layer;
        layer.confirm('您确定要退出登录吗？', {
            area: '70%',
            offset: '25%',
            btn: ['确定', '取消']
        }, function(index, layero){
            layer.msg(msg_load_icon +"正在退出登录", msg_load);
            location.href = "/user/logout";
        }, function(index){
            //按钮【按钮二】的回调
        });

    });
}

/**
 * 打开对话
 */
function linkMessage($to)
{
    if (GET("iframe") == "true") {
        location.href = "/user/message/"+ $to +"?iframe=true";
    } else if(GET("referer") != null) {
        location.href = "/user/message/"+ $to +"?referer="+ GET("referer");
    } else {
        location.href = "/user/message/"+ $to;
    }
}