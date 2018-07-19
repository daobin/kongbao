var delivery_status = $.trim($('#J_delivery_status').val());

Order.get_express({"delivery_status": delivery_status}, $('#J_list_express'));

$('#J_tab_delivery_status li').click(function(){
    if($(this).hasClass('on')){
        return false;
    }

    $('#J_form_search').resetForm();
    $('#J_tab_delivery_status li').removeClass('on');
    $(this).addClass('on');

    delivery_status = $.trim($(this).data('dstatus'));
    $('#J_delivery_status').val(delivery_status);
    Order.get_express({"delivery_status": delivery_status}, $('#J_list_express'));
});

$('#J_btn_search').click(function(){
    Order.get_express({
        "delivery_status": delivery_status,
        "logistics_number": $.trim($('input[name=logistics_number]').val()),
        "gmt_start": $.trim($('input[name=gmt_start]').val()),
        "gmt_end": $.trim($('input[name=gmt_end]').val())
    }, $('#J_list_express'));
});

$('#J_list_express').on('click', '.J_page_click', function () {
    Order.get_express({
        "delivery_status": delivery_status,
        "logistics_number": $.trim($('input[name=logistics_number]').val()),
        "gmt_start": $.trim($('input[name=gmt_start]').val()),
        "gmt_end": $.trim($('input[name=gmt_end]').val()),
        "page": $.trim($(this).data('page'))
    }, $('#J_list_express'));
});