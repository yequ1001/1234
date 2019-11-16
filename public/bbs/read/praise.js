// 绑定的版块名称
var praise_model;
// 绑定的版块id
var praise_modeId;
// 绑定的1级帖Id
var praise_blog1;

/**
 * 用户点赞时
 */
function praise($model, $modelId, $blog1) {
    praise_model = $model;
    praise_modeId = $modelId;
    praise_blog1 = $blog1;
    $.get("/blog/praise/"+praise_model+"/"+praise_modeId+"/"+praise_blog1, function(data){
        response(data)
    });
}

/**
 * 服务器响应后处理
 */
function response($data) {
    if ($data === "OK") {
        $praiseNum = $("#praise-"+ praise_model +"-"+ praise_modeId +" .praiseNum");
        $Num = $praiseNum.text();
        $Num ++;
        $praiseNum.html($Num);
        $("#praise-"+ praise_model +"-"+ praise_modeId).css("color","#F58100");
    } else {
        openModal($data, "no");
    }
}