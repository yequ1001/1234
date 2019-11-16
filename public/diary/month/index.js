$(document).ready(function(){
    layui.use('layer', function() {
        var layer = layui.layer;
    });
    layui.use('form', function() {
        var form = layui.form;
        form.on('select()', function(){
            location.href = "/diary/month/"+ $('#sel_years option:selected').val() +"/"+ $('#sel_month option:selected').val();
        });
    });
    scrollPosition(".light");
    btn1 = false;
    btn2 = false;
});

/**
 * 将今天的日记滚动到屏幕中间来
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
    jQuery('html, body').animate({
        scrollTop: tScrollTop
    }, 1000);
}

function save()
{
    $(".aa").hide();
    layer.msg(msg_load_icon +"正在保存", msg_load);

    // 收集表单内容1
    var i = 1;
    var diary_content = "";
    $(".layui-timeline-item .div_content").each(function(){
        $str = $(this).html();
        $s = $str.replace(/<[^>]*>/g, "");
        if ($s.length > 300) {
            layer.msg(msg_ng_icon +"第"+ i +"天的日记超过了300字<br/>已有"+ $str.length +"字", msg_ng);
            // 抛出异常，终止执行
            throw SyntaxError();
        }
        diary_content += $str +"|";
        i ++;
    });

    // 收集表单内容2
    i = 1;
    var diary_content2 = "";
    $(".layui-timeline-item .div-content2").each(function(){
        $str = $(this).html();
        $str = $str.replace("|", " ");
        if ($str.length > 20) {
            layer.msg(msg_ng_icon +"第"+ i +"天的附属信息超过了20字<br/>已有"+ $str.length +"字", msg_ng);
            // 抛出异常，终止执行
            throw SyntaxError();
        }
        diary_content2 += $str +"|";
        i ++;
    });

    // 向服务器提交表单
    $.post("/diary/month",
        {
            content     : diary_content,
            content2    : diary_content2,
            date        : $(".layui-timeline").data("date"),
        },
        function(data,status){
            if (data === true) {
                layer.msg(msg_ok_icon +"当月记事保存成功", msg_ok);
            } else {
                layer.msg(msg_ng_icon + data, msg_ng);
            }
        }
    );
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
 * “选项”按钮
 */
$show = false;
function show_op()
{
    if ($show == false) {
        $show = true;
        $("#show_op").html("收起 <i class='fa fa-angle-double-up'></i>");
        $(".layui-btn-container").fadeIn(300);
    } else {
        $show = false;
        $("#show_op").html("功能 <i class='fa fa-angle-double-down'></i>");
        $(".layui-btn-container").fadeOut(300);
    }
}

/**
 * 当日记退出编辑失去光标后，
 * 将文本格式化
 */
function setColor($id)
{
    // 获取用户当前编辑的日记
    var diary;
    diary = $("."+$id).html();
    // 屏蔽用户输入的敏感字符
    diary = diary.replace(/\|/g, "&#921;");
	// 空格转换
	diary = diary.replace(/&nbsp;/g, " ");
    // 将换行（换行符）转换为分隔符
    diary = diary.replace(/<br[^>]*>/gi, "|");
    // 将换行（分割线）转换为分隔符
    diary = diary.replace(/<hr[^>]*>/gi, "hr|");
    // 将换行（　块　）转换为分隔符
    diary = diary.replace(/<div[^>]*>/gi, "|");
    diary = diary.replace(/<\/div>/gi, "|");
    // 将换行（　行　）转换为分隔符
    diary = diary.replace(/<p[^>]*>/gi, "|");
    diary = diary.replace(/<\/p>/gi, "|");
    // 将换行（清单行）转换为分隔符
    diary = diary.replace(/<li[^>]*>/gi, "|");
    diary = diary.replace(/<\/li>/gi, "|");
    // 将换行（表格行）转换为分隔符
    diary = diary.replace(/<tr[^>]*>/gi, "|");
    diary = diary.replace(/<\/tr>/gi, "|");
	// 删除换行实体
    diary = diary.replace(/(\n)+/g, "");
    // 剔除用户输入所有的html元素
    diary = diary.replace(/<[^>]*>/gi, "");
    // 合并空格
    diary = diary.replace(/\s+/g, " ");
    // 删除首尾空白字符
    diary = trim(diary);
    // 删除首尾连续的分隔符和空白字符
    diary = diary.replace(/^\|[\|\s]*/g, "");
    diary = diary.replace(/[\|\s]+$/g, "");
    // 将连续相邻的分隔符合并
    diary = diary.replace(/\|\s+/g, "|");
    diary = diary.replace(/\|+/g, "|");
    // 将每行日记写入临时的数组并遍历使其格式化
    $arr = diary.split("|");
    for(var i = 0; i < $arr.length; i++) {
        // 删除行两端空白字符
        $arr[i] = trim($arr[i]);
		if ($arr[i] == "·" || $arr[i] == "!" || $arr[i] == "×") {
			$arr[i] = "";
		}
        // 将纪录的当前行标记为 重点
        switch ($arr[i].substring(0, 3)) {
            case "!· ":
            case "·! ":
            case "· !":
            case "！· ":
            case "·！ ":
            case "· ！":
            case "!× ":
            case "！× ":
                $arr[i] = "<span style='color: #f0370e'>! " + $arr[i].substr(3) + "</span>";
                break;
        }
        switch ($arr[i].substring(0, 2)) {
            case "!·":
            case "·!":
            case "！·":
            case "·！":
            case "!×":
            case "！×":
                $arr[i] = "<span style='color: #f0370e'>! " + $arr[i].substr(3) + "</span>";
                break;
        }
        switch ($arr[i].substring(0, 1)) {
            case "！":
            case "!":
                $arr[i] = "<span style='color: #f0370e'>! " + $arr[i].substr(1) + "</span>";
                break;
        }
        // 将纪录的当前行标记为 隔离
        switch ($arr[i].substring(0, 3)) {
            case ".· ":
            case "·. ":
            case "· .":
            case "。· ":
            case "·。 ":
            case "· 。":
            case "。× ":
            case ".× ":
            case "。! ":
            case ".! ":
                $arr[i] = "<span style='text-decoration:line-through; color: #BCC1D6'>×" + $arr[i].substr(3) + "</span>";
                break;
        }
        switch ($arr[i].substring(0, 2)) {
            case ".·":
            case "·.":
            case "。·":
            case "·。":
            case "。×":
            case ".×":
            case "。!":
            case ".!":
                $arr[i] = "<span style='text-decoration:line-through; color: #BCC1D6'>×" + $arr[i].substr(3) + "</span>";
                break;
        }
        switch ($arr[i].substring(0, 1)) {
            case "×":
            case ".":
            case "。":
                $arr[i] = "<span style='text-decoration:line-through; color: #BCC1D6'>×" + $arr[i].substr(1) + "</span>";
                break;
        }
        // 格式化当前行：开头添加字符“· ”
        var first_str = $arr[i].substring(0, 1);
        if (first_str !== "·" && first_str !== "<" && first_str !== "!" && $arr[i].length > 0 && first_str !== "×" && $arr[i].toLowerCase() !== "hr") {
            $arr[i] = "· "+ $arr[i];
        }
        // 格式化当前行：纠正开头为“· ”
        if ($arr[i].substring(1, 1) !== " " && $arr[i].substring(0, 1) === "·" && $arr[i].toLowerCase() !== "hr") {
            $arr[i] = "· "+ $arr[i].substring(1);
        }
    }
    // 遍历一次格式化后的日记
    for(var i = 0; i < $arr.length; i++) {
        // 如果首字符不是!号则取消高亮
        first_str = $arr[i].replace(/<[^>]+>/gi, "").substring(0, 1);
        // 添加分割线
        if ($arr[i].toLowerCase() === "hr") {
            $arr[i] = "<hr class='hr' />";
        } else if (first_str !== "!" && first_str !== "×") {
            $arr[i] = $arr[i].replace(/<[^>]+>/gi, "");
        }
        // 将每行日记写入变量diary输出
        if (i == 0) {
            diary = $arr[i];
        } else {
			if ($arr[i-1] == "") {
                diary += $arr[i];
			} else {
                diary += "<br/>"+ $arr[i];
			}
        }
    }
    $("."+$id).html(diary);
}

function inputOn($id)
{
    $(".aa").hide();
    $xy = $("."+ $id).offset();
    $xy["top"] -= 34;
    if ($id.substring(0,1) == "p") {
        $xy["left"] = $(window).width()/2-38.5;
        $("#btn_save1_"+ $id.substring(2)).show().offset($xy);
    } else {
        $xy["left"] = $(window).width()/2-38.5;
        $("#btn_save2_"+ $id.substring(2)).show().offset($xy);
    }
}

function close2()
{
    $(".aa").hide();
}

/**
 * 格式化附属信息
 */
function format($id)
{
    var diary;
    diary = $("."+$id).html();
    diary = diary.replace(/(<div[^>]*>)|(<p[^>]*>)|(<br ?\/?>)/g, "\n");
    diary = diary.replace(/(<\/div>)|(<\/p>)/g, "\n");
    diary = diary.replace(/<[^>]*>/g, "");
    diary = diary.replace(/^\n+/g, "");
    diary = diary.replace(/\n+/g, "<br/>");
    diary = diary.replace(/&nbsp;/g, " ");
    diary = diary.replace(/\|/g, "&#921;");
    diary = trim(diary);
    $("."+$id).html(diary);
}

/**
 * 删除字符串首尾空白字符
 */
function trim(str)
{
    return str.replace(/(^\s*)|(\s*$)/g, "").replace(/(^(&nbsp;)+)|((&nbsp;)+$)/g, "");
}

/**
 * 隐私模式切换
 */
var state;
function privacy($this)
{
    if (state == null) {
        $("[class *= 'p-']").hide();
        $("[class *= 'f-']").css({"textAlign":"left", "fontSize":"15px"});
        state = true;
        $($this).text("取消隐藏");
    } else {
        $("[class *= 'p-']").show();
        $("[class *= 'f-']").css({"textAlign":"center", "fontSize":"12px"});
        state = null;
        $($this).text("隐藏记事");
    }
}
