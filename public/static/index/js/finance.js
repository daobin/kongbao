var Finance = {
    get_fund_record: function (params, show_elem) {
    $('.J_fund_record').remove();
    show_elem.append('<tr class="J_loading"><td style="text-align: center;" colspan="5">' +
        '<img src="/public/static/index/image/loading.gif" />' +
        '</td></tr>');

    setTimeout(function () {
        $.ajax({
            type: 'get',
            dataType: 'json',
            url: '/ajax/index.finance/getFundRecord',
            data: params,
            success: function (res) {
                if ($('.J_loading').length > 0) {
                    $('.J_loading').remove();
                }
                if (res.status == 'success') {
                    show_elem.append(res.data);
                    if ($('div.page').length > 0) {
                        $('div.page a').each(function () {
                            if ($(this).attr('href') != undefined) {
                                var a_page = $(this).attr('href').replace(/[^\d]+/, '');
                                $(this).attr('data-page', a_page).addClass('J_page_click')
                                    .removeAttr('href').css('cursor', 'pointer');
                            }
                        });
                    }
                } else {
                    layer.alert(res.msg);
                }
            }
        });
    }, 500);

},
};
