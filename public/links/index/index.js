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

    $('[js-tab=2]').tab({
        mouse: 'over',   //切换方式：over，click
        autoPlay: true,  //播放方式：false，true
        curDisplay: 1,     //当前第一个打开
        changeMethod: 'horizontal'  //切换选项：默认default，horizontal，vertical，opacity这4种方式
    });

    $('[js-tab=3]').tab({
        curDisplay: 2,
        changeMethod: 'horizontal'
    });

});

/**
 * 流加载
 */
function flow($type)
{
    // 遍历收藏列表，并添加到数组$link_arr，用于识别外链是否已被用户收藏
    $link_arr = [];
    $(".div-collection a").each(function(){
        $link_arr.push($(this).data("url"));
    });

    layui.use('flow', function(){
        //var $ = layui.jquery; //不用额外加载jQuery，flow模块本身是有依赖jQuery的，直接用即可。
        var flow = layui.flow;
        flow.load({
            elem: '#div_'+$type,
            isAuto: false,
            end: ' ',
            done: function(page, next){ //到达临界点（默认滚动触发），触发下一页
                var lis = [];
                //以jQuery的Ajax请求为例，请求下一页数据（注意：page是从2开始返回）
                $.get('/links/type/'+ $type +'?page='+page, function(res){
                    //假设你的列表返回在data集合中
                    layui.each(res.data, function(index, item){
                        // 收藏
                        $class = "";
                        if ($link_arr.indexOf(item.url) > -1) {
                            $class = "fa-heart";
                        } else {
                            $class = "fa-heart-o";
                        }
                        // 推荐
                        $recommend = "";
                        switch (item.recommend) {
                            case '人气':
                                $recommend = " <span class='layui-badge layui-bg-orange'>人气</span>";
                                break;
                            case '精品':
                                $recommend = " <span class='layui-badge'>精品</span>";
                                break;
                            case '推荐':
                                $recommend = " <span class='layui-badge layui-bg-blue'>推荐</span>";
                                break;
                        }
                        lis.push('<div class="div-item">');
                        lis.push('<p class="p-link p-link-'+item.id+'"><a href="/links/go/'+item.id+'?url='+item.url+'" target="_blank"><img class="logo" src="'+item.logo+'" onerror="this.src=\'/public/static/img/default_ico.ico\'"/> '+item.name+'</a>'+$recommend+'<span class="collect click" onclick="collect('+item.id+')"><i class="fa '+$class+'"></i> 收藏</span></p>');
                        lis.push('<p class="p-description">'+ item.description +'</p>');
                        lis.push('</div>');
                    });
                    //执行下一页渲染，第二参数为：满足“加载更多”的条件，即后面仍有分页
                    //pages为Ajax返回的总页数，只有当前页小于总页数的情况下，才会继续出现加载更多
                    next(lis.join(''), page < res.last_page);
                });
            }
        });
    });
}

/**
 * 打开保存链接的表单
 */
function openForm()
{
    layer.open({
        type: 1,
        title: false,
        area: '80%',
        offset: '15%',
        content: $(".div-form"),
        shadeClose: true,
    });
}

/**
 * 保存链接
 */
function save()
{
    layer.msg(msg_load_icon +"正在添加", msg_load);
    $.post("/links/my",
        {
            title   : $(".input-title").val(),
            url     : $(".input-url").val(),
        },
        function(data,status){
            if (data === true) {
                layer.msg(msg_ok_icon +"添加成功", msg_ok);
                location.reload()
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}

/**
 * 打开更新链接的表单
 */
var aid;
var updateForm;
function openUpdateForm($id, $readOnly)
{
    updateForm = layer.open({
        type: 1,
        title: false,
        area: '80%',
        offset: '15%',
        content: $(".div-updateForm"),
        shadeClose: true,
    });
    if ($readOnly) {
        $(".div-updateForm .update").hide();
    } else {
        $(".div-updateForm .update").show();
    }
    $(".div-updateForm .input-title").val($(".a-"+ $id).text());
    $(".div-updateForm .input-url").val($(".a-"+ $id).data('url'));
    aid = $id;
}

/**
 * 链接跳转，并统计计数
 */
function goto()
{
    $.get("/links/my/"+ aid,function(data){
        if (data != true) {
            layer.msg(msg_ng_icon + data, msg_ng);
        }
    });
    window.open("http://"+ $(".a-"+ aid).data('url'));
}

/**
 * 更新链接
 */
function update()
{
    $.post("/links/my/"+ aid,
        {
            title   : $(".div-updateForm .input-title").val(),
            url     : $(".div-updateForm .input-url").val(),
        },
        function(data){
            if (data === true) {
                layer.msg(msg_ok_icon + "修改成功", msg_ok);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}

/**
 * 删除链接
 */
function del()
{
    $.ajax({
        url: "/links/my/"+ aid,
        type: 'DELETE',
        success: function(data) {
            if (data === true) {
                layer.msg(msg_ok_icon + "删除成功", msg_ok);
                $(".a-"+ aid).hide();
                $(".p-url-"+ aid).hide();
                layer.close(updateForm);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    });
}

/**
 * 一键收藏
 */
function collect($id)
{
    // 如果没有收藏
    if ($(".p-link-"+$id+" .fa-heart-o").length > 0) {
        $(".p-link-"+$id+" .fa").removeClass("fa-heart-o").addClass("fa-spinner fa-spin");
        $.post("/links/collect",
            {
                title : $(".p-link-"+$id+" a").text(),
                url   : $(".p-link-"+$id+" a").attr("href"),
                type  : 'save',
                logo  : $(".p-link-"+$id+" img").attr("src"),
            },
            function(data,status){
                if (data === true) {
                    $(".p-link-"+$id+" .fa").removeClass("fa-spinner fa-spin").addClass("fa-heart");
                } else {
                    if (data == "login") {
                        layer.msg(msg_ng_icon +"当前操作需要登录", msg_ng);
                        location.href = "/user";
                    } else {
                        layer.msg(msg_ng_icon + data, msg_ng);
                        $(".p-link-"+$id+" .fa").removeClass("fa-spinner fa-spin").addClass("fa-heart-o");
                    }
                }
            });
    }
    // 如果已经收藏
    else {
        $(".p-link-"+$id+" .fa").removeClass("fa-heart").addClass("fa-spinner fa-spin");
        $.post("/links/collect",
            {
                title : $(".p-link-"+$id+" a").text(),
                url   : $(".p-link-"+$id+" a").attr("href"),
                type  : 'delete'
            },
            function(data,status){
                if (data === true) {
                    $(".p-link-"+$id+" .fa").removeClass("fa-spinner fa-spin").addClass("fa-heart-o");
                } else {
                    layer.msg(msg_ng_icon + data, msg_ng);
                    $(".p-link-"+$id+" .fa").removeClass("fa-spinner fa-spin").addClass("fa-heart");
                }
        });
    }
}