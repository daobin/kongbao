var address_search = {
    search: $.trim($('#J_search_keywords').val()),
    page: 1,
    select_all: 1
};
Address.get_shipping_address(address_search, $('#J_select_address'));

$('#J_search_btn').click(function () {
    var keywords = $.trim($('#J_search_keywords').val());
    if (keywords == '') {
        layer.alert('请输入姓名，电话或地址');
        return false;
    }

    var address_search = true;
    $('#J_select_address option').each(function () {
        if (address_search == true) {
            var address = $(this).html();
            if (address.indexOf(keywords) > -1) {
                $('#J_select_address option').removeAttr('selected');
                $(this).attr('selected', 'selected');
                address_search = false;
            }
        }
    });
});

$('#J_set_default_address').click(function () {
    Address.set_default_address($('#J_select_address').val());
});

$('#J_select_express option').each(function () {
    if ($(this).prop('selected')) {
        $('.J_unit_price').html($(this).data('price'));
        $('#J_unit_price').val($(this).data('price'));
    }
});

$('#J_select_express').change(function () {
    $('#J_select_express option').each(function () {
        if ($(this).prop('selected')) {
            $('#J_express_tip').html($(this).data('tip'));
            $('.J_unit_price').html($(this).data('price'));
            $('#J_unit_price').val($(this).data('price'));
        }
    });
});

$('#J_set_default_express').click(function () {
    var price_id = $.trim($('#J_select_express').val());
    if (price_id == '0') {
        layer.alert('请选择需要购买的快递公司物流');
        return false;
    }

    $.ajax({
        type: 'post',
        dataType: 'json',
        url: '/ajax/index.user/setDefaultPrice',
        data: {price_id: price_id, group: 'express'},
        success: function (res) {
            layer.alert(res.msg);
        }
    });
});

//判断是否为数字
function check_float(val) {
    var re = /^[0-9]+.?[0-9]*$/;
    if (!re.test(val.replace(/(^\s*)|(\s*$)/g, ""))) {
        return false;
    } else {
        return true;
    }
}

//乘法运算
function calc_multiplication(arg1, arg2) {
    var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
    try { m += s1.split(".")[1].length } catch (e) { }
    try { m += s2.split(".")[1].length } catch (e) { }
    return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
}

//检查收货地址
function check_delivery_address() {
    $("#ty_addrs").html("");
    var err = 0;
    var num = 0;
    var addrs = $.trim($("#J_delivery_address").val());
    $("#J_delivery_address").val(addrs);
    if (addrs == '') {
        err++;
        layer.alert('请填写收货地址');
        return err;
    }

    addrs = addrs.split("\n");
    if (addrs.length <= 100) {
        $("#tb_addrs").show();
        for (var i = 0; i < addrs.length; i++) {
            addrs[i] = $.trim(addrs[i]);
            if (addrs[i] != "") {
                num++;
                var line = addrs[i].replace(/，/g, ",");
                var items = line.split(",");
                console.log(num + ' >> ' + items + ' << ' + items.length);
                if ($("#ispdd").val() == "0") {
                    if (items.length == 4 && (check_float(items[3]) || items[3].replace(/(^\s*)|(\s*$)/g, "") == "")) {
                        $("#tb").show();
                        $("#pdd").hide();
                        $("#td_total").attr("colspan", "5");
                        $("#ty_addrs").append("<tr>");
                        $("#ty_addrs").append("<td>" + num + "</td>");
                        $("#ty_addrs").append("<td>" + items[0] + "</td>");
                        $("#ty_addrs").append("<td>" + items[1] + "</td>");
                        $("#ty_addrs").append("<td>" + items[2] + "</td>");
                        $("#ty_addrs").append("<td>" + items[3] + "</td>");
                        $("#ty_addrs").append("</tr>");
                    } else {
                        err++;
                        $("#ty_addrs").append("<tr>");
                        $("#ty_addrs").append("<td style='background-color:#ea5252;'>" + num + "</td>");
                        $("#ty_addrs").append("<td style='background-color:#ea5252;color:#fff' colspan='4'>第" + num + "行收货地址格式错误，请修改后重新检查无误再购买</td>");
                        $("#ty_addrs").append("</tr>");
                    }
                } else {
                    $("#tb").hide();
                    $("#pdd").show();
                    $("#td_total").attr("colspan", "6");
                    if (items.length == 5) {
                        $("#ty_addrs").append("<tr>");
                        $("#ty_addrs").append("<td>" + num + "</td>");
                        $("#ty_addrs").append("<td>" + items[0] + "</td>");
                        $("#ty_addrs").append("<td>" + items[1] + "</td>");
                        $("#ty_addrs").append("<td>" + items[2] + "</td>");
                        $("#ty_addrs").append("<td>" + items[3] + "</td>");
                        $("#ty_addrs").append("<td>" + items[4] + "</td>");
                        $("#ty_addrs").append("</tr>");
                    }else {
                        err++;
                        $("#ty_addrs").append("<tr>");
                        $("#ty_addrs").append("<td style='background-color:#ea5252;'>" + num + "</td>");
                        $("#ty_addrs").append("<td style='background-color:#ea5252;color:#fff' colspan='5'>第" + num + "行收货地址格式错误，请修改后重新检查无误再购买</td>");
                        $("#ty_addrs").append("</tr>");
                    }
                }
            }
        }

        //计算价格
        $("#num").text(num);
        var price = $(".J_unit_price:eq(0)").text();
        var result = calc_multiplication(price, num);
        $("#result").text(result);

        if (err > 0) {
            layer.alert('您提交的收货地址有' + err + '处填写错误，请修改');
        }

    } else {
        err++;
        layer.alert("您一次性最多只能提交100条空包订单，超过100单请分开多次下单");
    }

    return err;
}

$('#J_chk_textarea_address').click(function(){
    check_delivery_address();
});

$('#J_form_submit').click(function(){
    $('#J_form_submit').prop('disabled', true);

    if($.trim($('#J_select_address').val()) == '' || $.trim($('#J_select_address').val()) == '0'){
        layer.alert('请选择发货地址');
        $('#J_form_submit').prop('disabled', false);
        return false;
    }

    if($.trim($('#J_select_express').val()) == '0'){
        layer.alert('请选择快递类型');
        $('#J_form_submit').prop('disabled', false);
        return false;
    }

    if(check_delivery_address()){
        return false;
    }

    $("#J_express_form").ajaxSubmit({
        url: '/ajax/index.order/buyExpress',
        type: 'post',
        dataType: 'json',
        success: function (res) {
            layer.alert(res.msg);

            if (res.status == 'success') {
                $("#J_express_form").resetForm();
            }
            $('#J_form_submit').prop('disabled', false);
        },
        error: function (data, status, e) {
            console.log('********** Ajax Submit Error **********');
            $('#J_form_submit').prop('disabled', false);
        }
    });

    return false;
});
