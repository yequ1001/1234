//如果页面接收了对话数据，则会设为true
var bool;

$(document).ready(function(){
    layui.use('layer', function(){
        var layer = layui.layer;
    });

    layui.use('element', function(){
        var element = layui.element;
    });

    layui.use('form', function(){
        var form = layui.form;
        form.on('select()', function(data){
            location.href = data.value;
        });
    });

    layui.use('slider', function(){
        var slider = layui.slider;
        //渲染
        slider.render({
            elem: '#skip2',  //绑定元素
            type: 'vertical',
        });
    });
	
    //发言输入框
    $textArea = $("textarea");
    //发言模态框
    $form = $("#funDiv");
    //发言输入框字数统计
    $textCount = $("#textCount");
    //对话列表
    $listDiv = $("#list");
    //当前页码
    $page = parseInt(GET("page")?GET("page"):1);
    //页顶翻页导航
    $skipDiv = $("#skip");
    //对方用户ID
    $from = parseInt(window.location.pathname.substring(14));
    //定时器
    $timer = null;
    //总页数
    $pages = null;

    //变量默认值设置
    bool = false;

    //读取聊天数据（只有页数在第一页才执行）
    read();

    //
    $listDiv.css("minHeight", $(window).height()-65);
});

/**
 * 翻页导航
 */
function skip(){
    // 显示切换
    $skipDiv.slideToggle();
    // 是否显示自定义跳页按钮
    $arr = ['page', 'skip'];
    if ($pages < 10) {
        $arr = ['page'];
    }
    // 分页导航
    layui2.use('laypage', function(){
        var laypage = layui.laypage;
        //完整功能
        laypage.render({
            elem: 'skip',
            count: $items,
            limit: $item,
            groups: 4,
            curr: $page,
            layout: $arr,
            jump: function(obj, first){
                //首次不执行
                if(!first){
                    location.href = "/user/message/"+ $from +"?page="+ obj.curr;
                }
            }
        });
    });
}

/**
 * 在线闪烁
 */
//function twinkle($class)
//{
//    setInterval(function(){
//        $($class).toggleClass("twinkle");
//    }, 600);
//}
function twinkle($class) {
    $twinkleTimer = setTimeout(function () {
        $($class).css("textShadow", "0 0 14px #FFFFFF");
        clearTimeout($twinkleTimer);
        _twinkle($class);
    }, 600);
}
function _twinkle($class) {
    $twinkleTimer = setTimeout(function () {
        $($class).css("textShadow", "0 0 0");
        clearTimeout($twinkleTimer);
        twinkle($class);
    }, 600);
}

/**
 * 返回当前日期
 * 格式：yyyy-mm-dd
 */
function date() {
    var now = new Date();
    return now.getFullYear() + "-" +((now.getMonth()+1)<10?"0":"")+(now.getMonth()+1)+"-"+(now.getDate()<10?"0":"")+now.getDate();
}

/**
 * 打开表单
 */
function openForm() {
    layer.open({
        title: '对ta说',
        content: $("#div_form"),
        type: 1,
        area: '300px',
        maxWidth: '800px',
        offset: '20%',
        success: function(layero, index){
            //使用文本框获得焦点
            $textArea.focus();
        },
        cancel: function(index, layero){
            layer.close(index);
            $textArea.val("");   //清空输入框
            $textArea.blur();    //使用文本框失去焦点
            $textCount.text("0");
        }
    });

    //统计输入框的字数
    $textArea.on('input propertychange', function(e){
        $textCount.text($textArea.val().length);
    });
}

/**
 * 发送对话
 */
function post() {
    layer.msg(msg_load_icon +"正在发送",msg_load);

    //向服务器POST数据
    $.post("/user/message/save",
        {
            to              : $("#to").val(),
            content         : $textArea.val(),
        },
        function(data){
            if(data === "OK"){
                layer.msg(msg_ok_icon +"发送成功", msg_ok);
                // 关闭表单
                $textArea.val("");   //清空输入框
                $textArea.blur();    //使用文本框失去焦点
                $textCount.text("0");
                layer.closeAll();
                // 和对方首次发言后需要刷新页面才能正常显示对话列表
                //if ($("#list").data("final-time") === "") {
                //    location.reload();
                //}
                clearInterval($timer); // 这里清除旧的定时器，避免重复获取到同一条对话记录
                read();
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        });
}

/**
 * 获取用户消息
 */
function read() {
    $.post("/user/message/read",
        {
            him         : $from,
            finalTime   : $listDiv.data("final-time"),
            page        : $page,
        },
        function(data){
            // 将从服务器接收的数据打印在浏览器控制台，用于分析调试
            console.log(data);

            // 从服务器获取到的对话信息
            $onlineState    = data[0]; // 在线状态
            $speakArr       = data[1]; // 对话数据
            $pages          = data[2]; // 总页数
            $item           = data[3]; // 每页对话上限
            $items          = data[4]; // 对话总数
            $fromNickname   = data[5]; // 对话目标人
            $toNickname     = data[6]; // 当前用户
            $fromFace       = data[7]; // 对话目标人的头像
            $toFace         = data[8]; // 当前用户的头像

            // 变量再加工
            if ($pages <= 1) {
                $pages = 1;
            }
            if ($page > $pages) {
                $page = $pages;
            }

            // 页码定位显示
            $("#pages, .pages").text( $page +"/"+ $pages);

            // 在线状态指示灯
            $people = $fromNickname;
            if ($people.length > 6) {
                $people = $people.substr(0, 6) +"..";
            }
            $people = "<span style='font-size: 14px'>"+ $people +"</span>";
            if ($onlineState == 1){
                $("#onlineState").html("&nbsp;&nbsp;<i class='onlineTag_1 fa fa-user'></i> "+ $people);
                twinkle(".onlineTag_1");    //闪烁
            } else {
                $("#onlineState").html("&nbsp;&nbsp;<i class='onlineTag_0 fa fa-user-times'></i> <span style='color: #c8c8c8'>"+ $people +"</span>");
            }

            // 初始化
            $list = "";
            $finalTime = "";

            // 注意：如果数据库连接断开，data.length会错误的获取数据有104行，请避开这个数字
            if ($speakArr.length > 0 && $speakArr.length !== 104) {
                bool = true;
                for(var i = 0; i < $speakArr.length; i ++){
                    //每个循环都会往页面增加新数据，所以同时需要删除老数据，避免页面膨胀
                    if ($listDiv.data("final-time") !== "" &&  $listDiv.children(".item").length > $item) {
                        $listDiv.children().first().remove();
                    }

                    //设置变量
                    $data_id            = $speakArr[i].id;
                    $data_to            = $speakArr[i].to;
                    $data_form          = $speakArr[i].form;
                    $data_time          = $speakArr[i].time;
                    $data_content       = $speakArr[i].content;

                    //生成时间隔离线
                    if (typeof $timeStr !== "undefined") {
                        //非首次循环会执行此处代码
                        $str1 = Date.parse($data_time.replace(/-/gi,"/"));
                        $str2 = Date.parse($timeStr.replace(/-/gi,"/"));
                        if ( ($str1 - $str2) / 60000 > 3) {

                            //以下变量$s只在这个if从句用，名字就随便取了，太伤脑筋
                            $s = $data_time.replace(new RegExp(date()+"",'g'),"");
                            $s = $s.substr(0, $s.length - 3);
                            $l = "<p class='time-line'>"+ $s +"</p>";

                            // 纪录当前循环的时间
                            $timeStr = $data_time;
                        }
                    } else {
                        //首次循环会执行此处代码
                        $s = $data_time.replace(new RegExp(date()+"",'g'),"");
                        $s = $s.substr(0, $s.length - 3);
                        $l = "<p class='time-line'>"+ $s +"</p>";

                        // 纪录当前循环的时间
                        $timeStr = $data_time;
                    }
                    //生成对话气泡
                    if ($data_form != $from || $data_to == $from){
                        $list +=
                            $l +
                            "<div class='item-right item'>"+
                            "<div class='content'>" +
                            "<p><span class='nickname'>"+ $toNickname +"</span></p>" +
                            "<p class='tag-right'>"+
                            $data_content +
                            "</p>" +
                            "</div>" +
                            "<div class='face'>" +
                            "<img src='"+ $toFace +"' />" +
                            "</div>" +
                            "</div>";
                    } else{
                        $list +=
                            $l +
                            "<div class='item-left item'>"+
                            "<div class='face'>" +
                            "<a href='/user/card/"+$data_form+"'><img src='"+ $fromFace +"' /></a>" +
                            "</div>" +
                            "<div class='content'>" +
                            "<p><a href='/user/card/"+$data_form+"'><span class='nickname'>"+ $fromNickname +"</span></a></p>" +
                            "<p class='tag-left'>"+
                            $data_content +
                            "</p>" +
                            "</div>" +
                            "</div>";
                    }
                    // 清空$l
                    $l = "";
                    $s = "";
                    // 将最新的对话时间纪录在本地
                    $finalTime = $data_time;
                }
                //首次：加载全部对话
                if ($listDiv.data("final-time") === ""){
                    $listDiv.html($list);
                    $list = "";
                    //页面被赋值后，长度会发生变化，如果是第一页，就将界面定位到页底
                    if ($page == 1) {
                        $(document).scrollTop( $(document).height()-$(window).height() );
                    }
                }
                //再次：只加载新的对话
                else {
                    //滚动开关：默认为开
                    $scrollOpen = true;
                    $scrollSpace = $(document).height()-$(window).height();
                    if ($scrollSpace - 50 > $(document).scrollTop()) {
                        $scrollOpen = false;
                    }
                    $("#list").append($list);
                    if ($scrollOpen) {
                        $scrollSpace = $(document).height()-$(window).height();
                        $(document).scrollTop($scrollSpace);
                    }
                }
                //本地更新最新对话时间
                $listDiv.data("final-time", $finalTime);
            } else if (!bool) {
                $("#list").html(
                    "<p style='padding: 80px 10%' class='center'>" +
                    "没有新的对话记录<br/><br/>点击下方圆形半透明的图标输入对话。" +
                    "</p>"
                );
            }
            //定时读取服务器对话数据
            if ($page == 1) {
                clearInterval($timer);
                $timer = setInterval(function () {
                    read();
                }, 6000);
            }
        });
}
