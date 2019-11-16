/**
 * 打开1级帖功能气泡
 */
function openEdit1($blog1) {
    $(".u-edit1-"+ $blog1). fadeToggle(200);
}

function delete1($blog1) {
    openCheck("确认删除？", function () {
        openModal("正在删除", "load");
        $.get("/blog/delete/blog1/"+ $blog1,function(data,status){
            if (data === "OK") {
                openModal("删除成功", "yes");
                setTimeout(function () {
                    window.location.href = "/";
                }, 400);
            } else {
                console.log(data);
                openModal(data, "no");
            }
        });
    });
}

/**
 * 打开2级帖功能气泡
 */
function openEdit2($blog2) {
    $(".u-edit2-"+ $blog2). fadeToggle(200);
}

function delete2($blog2) {
    openCheck("确认删除？", function () {
        openModal("正在删除", "load");
        $.get("/blog/delete/blog2/"+ $blog2,function(data,status){
            if (data === "OK") {
                openModal("删除成功", "yes");
                setTimeout(function () {
                    window.location.reload();
                }, 500);
            } else {
                console.log(data);
                openModal(data, "no");
            }
        });
    });
}