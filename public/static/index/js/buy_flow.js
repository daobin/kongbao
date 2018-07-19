$('#J_tab_type li').click(function () {
    if ($(this).hasClass('curr')) {
        return false;
    }

    $('#J_tab_type li').removeClass('curr');
    $(this).addClass('curr');

    var show_option = $('.J_price_type_'+$.trim($(this).data('typeid')));
    $('#J_select_flow option').addClass('hidden');
    show_option.removeClass('hidden');
    $('#J_select_flow').val(show_option.attr('value'));
    $('#J_tip_price').html(show_option.data('tip'));
});

$('#J_select_flow').change(function(){
    $('#J_select_flow option').each(function () {
        if ($(this).prop('selected')) {
            $('#J_tip_price').html($(this).data('tip'));
            $('#J_unit_price').val($(this).data('price'));
        }
    });
});

$('#J_form_submit').click(function(){
    $('#J_form_submit').prop('disabled', true);

    var shop_name = $.trim($('input[name=shop_name]').val());
    var shop_keywords = $.trim($('input[name=shop_keywords]').val());
    var shop_url = $.trim($('input[name=shop_url]').val());
    if(shop_name == '' || shop_keywords == '' || shop_url == ''){
        layer.alert('来路信息未填写完整');
        $('#J_form_submit').prop('disabled', false);
        return false;
    }
    shop_keywords = shop_keywords.replace(/[,，]+/g, '，');
    $('input[name=shop_keywords]').val(shop_keywords);
    shop_keywords = shop_keywords.split('，');
    if(shop_keywords.length > 5){
        layer.alert('关键词最多可以添加5个，用逗号隔开');
        $('#J_form_submit').prop('disabled', false);
        return false;
    }

    $("#J_flow_form").ajaxSubmit({
        url: '/ajax/index.order/buyFlow',
        type: 'post',
        dataType: 'json',
        success: function (res) {
            layer.alert(res.msg);

            if (res.status == 'success') {
                $("#J_flow_form").resetForm();
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


