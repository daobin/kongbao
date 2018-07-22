$('#J_date_start, #J_date_end').datepicker({
    dateFormat: 'yy-mm-dd'
});

$('#J_btn_search').click(function () {
    Finance.get_fund_record({
        "date_start": $.trim($('#J_date_start').val()),
        "date_end": $.trim($('#J_date_end').val()),
        "group_text": $.trim($('#J_group_text').val()),
    }, $('#J_list_recharge_rewards'));
});

Finance.get_fund_record({}, $('#J_list_recharge_rewards'));

$('#J_list_recharge_rewards').on('click', '.J_page_click', function () {
    Finance.get_fund_record({
        "page": $.trim($(this).data('page'))
    }, $('#J_list_recharge_rewards'));
});
