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

    /** 用户点击插入图片按钮触发的事件 */
    $(".a-select").click(function () {
        $("#file").click();
    });

    $img = new Image();
    var clipArea = new bjj.PhotoClip("#clipArea", {
        size: [260, 260], // 截取框的宽和高组成的数组。默认值为[260,260]
        outputSize: [169, 169], // 输出图像的宽和高组成的数组。默认值为[0,0]，表示输出图像原始大小
        //outputType: "jpg", // 指定输出图片的类型，可选 "jpg" 和 "png" 两种种类型，默认为 "jpg"
        file: "#file", // 上传图片的<input type="file">控件的选择器或者DOM对象
        view: "#view", // 显示截取后图像的容器的选择器或者DOM对象
        ok: "#clipBtn", // 确认截图按钮的选择器或者DOM对象
        loadStart: function(file) {}, // 开始加载的回调函数。this指向 fileReader 对象，并将正在加载的 file 对象作为参数传入
        loadComplete: function(src) {}, // 加载完成的回调函数。this指向图片对象，并将图片地址作为参数传入
        loadError: function(event) {}, // 加载失败的回调函数。this指向 fileReader 对象，并将错误事件的 event 对象作为参数传入
        clipFinish: function(dataURL) {
            layui.use('layer', function(){
                var layer = layui.layer;
                layer.confirm('确认裁剪并上传？', {
                    area: '70%',
                    offset: '25%',
                    btn: ['确认', '取消']
                }, function(index, layero){
                    //当用户点击提交按钮时，立即打开模态框并提示信息
                    layer.msg(msg_load_icon +"正在压缩省流上传", msg_load);
                    // 上传文件的名称（不含后缀）
                    $imgName = Date.parse(new Date()).toString().substring(0, 10) + (Math.random() * (99999 - 10000) + 10000).toString().substring(0, 5);
                    $imgBlob = convertBase64UrlToBlob(dataURL);
                    uploadImg($imgBlob, $imgName + ".jpg");
                }, function(index){
                    //按钮【按钮二】的回调
                });

            });
        },
        // 裁剪完成的回调函数。this指向图片对象，会将裁剪出的图像数据DataURL作为参数传入
    });
});

/**
 * 图像上传服务器
 * 参数1：需要上传的对象
 * 参数2：上传的文件重命名
 */
function uploadImg($objOrBlob, $imgName) {
    // 创建数据表单
    $form = new FormData();
    $form.append("file", $objOrBlob, $imgName);
    // 创建http请求
    $http = new XMLHttpRequest();
    // 以post方式异步发送服务器请求
    $http.open("post", "/user/face", true);
    // 请求完成执行的函数uploadComplete()
    $http.onload  = uploadComplete;
    // 请求失败执行的函数uploadFailed()
    $http.onerror = uploadFailed;
    // 上传进度调用方法实现
    // $http.upload.onprogress = progressFunction;
    // 上传开始执行的方法
    $http.upload.onloadstart = function(){
        // 设置上传开始时间
        ot = new Date().getTime();
        // 设置上传开始时，上传的文件大小为0
        oloaded = 0;
    };
    // 开始上传，发送form数据
    $http.send($form);
}

/**
 * 将以base64的图片url数据转换为Blob
 */
function convertBase64UrlToBlob($base64DataURL)
{
    $arr        = $base64DataURL.split(',');
    $mime       = $arr[0].match(/:(.*?);/)[1];
    $bstr       = atob($arr[1]);
    $length     = $bstr.length;
    $u8arr      = new Uint8Array($length);
    while ($length--) {
        $u8arr[$length] = $bstr.charCodeAt($length);
    }
    return new Blob([$u8arr], {type:$mime});
}

/**
 * 上传成功响应
 */
function uploadComplete(evt)
{
    if (evt.target.responseText === "OK") {
        location.href = "/user";
    } else {
        $("body").html(evt.target.responseText);
    }
}

/**
 * 上传失败响应
 */
function uploadFailed(evt)
{
    alert("上传失败！");
}

function openOperation()
{
    layer.tips($("#div_operation").html(), '#p_operation i', {
        tips: 3,
        time: 20000,
        closeBtn: 1,
        area: '300px',
    });
}