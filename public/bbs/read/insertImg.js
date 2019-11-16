// 重命名上传图像的名称
var imgName;
var imgSize;

$(document).ready(function(){
    /** 用户点击插入图片按钮触发的事件 */
    $(".button-file").click(function () {
        $("#input_file").click();
    });

    /** 本地文件选择框内容变动触发的事件 */
    $("#input_file").change(function(){
        //alert(0);
        if ($("#input_file").val() != null && $("#input_file").val() !== "") {
            layer.msg(msg_load_icon +"正在压缩", msg_load);
            UploadFile();
        }
    });
});

/**
 * 向服务器发送文件
 */
function UploadFile() {
    // 上传文件的名称（不含后缀）
    imgName = Date.parse(new Date()).toString().substring(0, 10) + (Math.random() * (99999 - 10000) + 10000).toString().substring(0, 5);
    // 用户选择的本地图形对象
    $localFile = document.getElementById("input_file").files[0];
    // 本地图形对象的尺寸，单位KB
    imgSize = $localFile.size / 1024;
    //大于150KB，进行压缩上传
    if(imgSize > 150){
        imgName += ".jpg";
        readImg( $localFile, function($base64DataURL){
            // 把携带图像的表单发送给服务器
            $imgBlob = convertBase64UrlToBlob($base64DataURL);
            uploadImg($imgBlob, imgName);
        });
    }
    else{
        imgName += "."+ $localFile.type.replace("image/","");
        /** 读取器：读取用户选择的图像内容 */
        $ready = new FileReader();
        $ready.readAsDataURL($localFile);
        $ready.onload = function(){
            // 图像内容
            $imgResult = this.result;
            // 本地预览图
            //$("#previewImg").attr("src", $imgResult);
        };

        // 把携带图像的表单发送给服务器
        uploadImg($localFile, imgName);
    }
}

/**
 * 将以base64的图片url数据转换为Blob
 */
function convertBase64UrlToBlob($base64DataURL){
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
 * 参数1：图片文件
 * 参数2：容器或者回调函数
*/
function readImg($localFile, $objDiv){
    /** 读取器：读取用户选择的图像内容 */
    $ready = new FileReader();
    $ready.readAsDataURL($localFile);
    $ready.onload = function(){
        // 图像内容
        $imgResult = this.result;
        // 本地预览图
        //$("#previewImg").attr("src", $imgResult);
        // 压缩图片
        imgCompress($imgResult, $objDiv);
    };
}

/**
 * 图片压缩函数
 * 将Data URL格式的图像内容写入到新的图片文件中
 */
function imgCompress($imgResult, callback){
    $img        = new Image();
    $img.src    = $imgResult;
    $img.onload = function(){
        // 图像尺寸
        $width  = this.width;
        $height = this.height;
        // 压缩比例
        $compressibility = compressibility($width, $height);
        $width  = $width * $compressibility;
        $height = $height * $compressibility;
        // 生成canvas
        $canvas = document.createElement('canvas');
        // 返回一个用于在画布上绘图的环境
        $canvasEnv = $canvas.getContext('2d');
        // 创建节点 宽度 属性和值
        $attrW = document.createAttribute("width");
        $attrW.nodeValue = $width;
        // 创建节点 高度 属性和值
        $attrH = document.createAttribute("height");
        $attrH.nodeValue = $height;
        // 将节点属性和值导入画布中
        $canvas.setAttributeNode($attrW);
        $canvas.setAttributeNode($attrH);
        // 将用户图形内容绘入画布环境
        $canvasEnv.drawImage(this, 0, 0, $width, $height);
        $base64DataURL = $canvas.toDataURL('image/jpeg', 1);
		if ($base64DataURL.length / 1024 > 150) {
            $base64DataURL = $canvas.toDataURL('image/jpeg', quality());
		}
        // 回调函数返回base64的值
        callback($base64DataURL);
    }
}

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
    $http.open("post", "/bbs/saveImg", true);
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
 * 上传成功响应
 */
function uploadComplete(evt) {
    // 服务接收完文件返回的结果
    if (evt.target.responseText === "OK") {
        layer.closeAll();
        // 在光标位置插入图片，图片名字：imgName
        insertHtml("<br/><img class='diy-img' src='/_data/bbs/_temp/"+ imgName +"' /><br/><br/>", ".div-comment");
    } else {
        layer.msg(msg_ng_icon + evt.target.responseText, msg_ng);
    }
}

/**
 * 上传失败响应
 */
function uploadFailed(evt) {
    alert("上传失败！");
}

/**
 * 设置图像尺寸压缩量
 */
function compressibility($width, $height) {
    // 设置图形最大允许宽度
    $maxWidth = 1000;
    // 如果图形宽度大于最大允许宽度
    if ($width > $maxWidth) {
        $compressibility = $maxWidth / $width;
    } else {
        $compressibility = 1;
    }
    return $compressibility;
}

/**
 * 设置图像显示质量
 */
function quality() {
    if (imgSize > 150) {
        $z = 150 / imgSize;
		// 不建议低于0.15，否则图像过于粗糙
		if ($z < 0.15) {
			$z = 0.15;
		}
		// 不建议高于0.95，否则压缩图有可能比原图还大
		else if ($z > 0.95) {
			$z = 0.95;
		}
    } else {
        $z = 1;
    }
    return $z;
}