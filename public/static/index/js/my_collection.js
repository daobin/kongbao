var o_status = $.trim($('#J_o_status').val());

Order.get_collection({"o_status": o_status}, $('#J_list_collection'));

$('#J_tab_o_status li').click(function(){
    if($(this).hasClass('curr')){
        return false;
    }

    $('#J_tab_o_status li').removeClass('curr');
    $(this).addClass('curr');

    o_status = $.trim($(this).data('ostatus'));
    $('#J_o_status').val(o_status);
    Order.get_collection({"o_status": o_status}, $('#J_list_collection'));
});

$('#J_list_collection').on('click', '.J_page_click', function () {
    Order.get_collection({
        "o_status": o_status,
        "page": $.trim($(this).data('page'))
    }, $('#J_list_collection'));
});
