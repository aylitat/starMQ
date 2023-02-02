/**
 *
 * @param message 描述
 * @param type 类型(info/success/warning/danger)
 * @param align 显示位置(top/buttom)
 */
notify = function (message, type = 'info', align = 'right') {
    switch (type) {
        case 'info':
            icon = 'fa fa-info me-1';
            break;
        case 'success':
            icon = 'fa fa-check me-1';
            break;
        case 'warning':
            icon = 'fa fa-exclamation-triangle me-1';
            break;
        case 'danger':
            icon = 'fa fa-times me-1';
            break;
    }
    Codebase.helpers('jq-notify', {
        align: align,             // 'right', 'left', 'center'
        from: 'top',                // 'top', 'bottom'
        type: type,               // 'info', 'success', 'warning', 'danger'
        icon: icon,    // Icon class
        message: message,
    });
}

reload = function () {
    $("#nav-main a").each(function () {
        var pageUrl = window.location.href.split(/[#]/)[0]; // window.location.href.split(/[?#]/)[0];
        if (this.href == pageUrl && window.location.href.split(/[/]/)[4]) {
            $(this).parent().parent().parent().addClass("open");
            $(this).addClass("active");
        } else {
            $(this).removeClass("active");
        }
    });
};
reload();


function order(id, type) {
    var msg;
    var url;
    if (type == "delete") {
        var msg = "你确定要删除该条订单吗?";
        var url = "/Order/del";
    } else if (type == "budan") {
        var msg = "你确定要补单该条订单吗?";
        var url = "/Order/budan";
    }
    layer.confirm(msg, {
        btn: ['确定', '取消'], closeBtn: 0,
    }, () => {
        $.ajax({
            url: url, cache: false, type: "POST", data: {
                id: id
            }, dataType: "json", success: (res) => {
                if (res.code == 200) {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    notify(res.msg, "success");
                    layer.closeAll();
                } else {
                    notify(res.msg, "danger");
                    layer.closeAll();
                }
            }, error: (XMLHttpRequest, textStatus, errorThrown) => {
                layer.closeAll();
                notify(errorThrown, "danger");
            }
        });
        return false;
    });
}

function setpay() {
    $("#sub").html('<i class="fa fa-spinner fa-spin"></i>正在提交');
    $("#sub").attr('disabled', true);
    let set_data = {};
    set_data.wxpay = $("#wxpay").val();
    set_data.zfbpay = $("#alipay").val();
    $.ajax({
        url: "/Set/pay", data: set_data, cache: false, type: "POST", dataType: "json", success: (res) => {
            $("#sub").html('提交');
            $("#sub").attr('disabled', false);
            if (res.code == 200) {
                setTimeout(() => {
                    location.reload();
                }, 2000);
                notify(res.msg, "success");
            } else {
                notify(res.msg, "danger");
            }
        }
    });
}

function Sysset() {
    $("#sub").html('<i class="fa fa-spinner fa-spin"></i>正在提交');
    $("#sub").attr('disabled', true);
    let set_data = {};
    set_data.close = $("#close").val();
    set_data.notifyUrl = $("#notifyUrl").val();
    set_data.returnUrl = $("#returnUrl").val();
    set_data.payQf = $("#payQf").val();
    $.ajax({
        url: "/Set/data", data: set_data, cache: false, type: "POST", dataType: "json", success: (res) => {
            $("#sub").html('提交');
            $("#sub").attr('disabled', false);
            if (res.code == 200) {
                setTimeout(() => {
                    location.reload();
                }, 2000);
                notify(res.msg, "success");
            } else {
                notify(res.msg, "danger");
            }
        }
    });
}

var clipboard = new ClipboardJS('.copy');
clipboard.on('success', (e) => {
    notify("复制成功", "success");
});
clipboard.on('error', (e) => {
    document.querySelector('.copy');
    notify("复制失败", "warning");
});


function delLog ()
{
    layer.confirm("确定要清除所有未支付订单吗？", {
        btn: ['确定', '取消'], closeBtn: 0,
    },()=>{
        $.ajax({
            url:"/Order/delorder",
            type:"POST",
            cache:false,
            dataType:"json",
            success:res =>{
                if (res.code == 200) {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    layer.closeAll();
                    notify(res.msg, "success");
                } else {
                    notify(res.msg, "danger");
                    layer.closeAll();
                }
            }
        });
    });
}

function rekey()
{
    layer.confirm("确定要重置密钥吗？", {
        btn: ['确定', '取消'], closeBtn: 0,
    },()=>{
        $.ajax({
            url:"/Set/rekey",
            type:"POST",
            cache:false,
            dataType:"json",
            success:res =>{
                if (res.code == 200) {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    layer.closeAll();
                    notify(res.msg, "success");
                } else {
                    notify(res.msg, "danger");
                    layer.closeAll();
                }
            }
        });
    });
}



