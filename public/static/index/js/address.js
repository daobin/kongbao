var Address = Address || {};
(function (addr) {
    addr.get_cities_by_province_id = function (province_id, show_elem) {
        $.ajax({
            type: 'get',
            dataType: 'json',
            url: '/ajax/address/getCities',
            data: {"province_id": province_id},
            success: function (res) {
                if (res.status == 'success') {
                    show_elem.html(res.data);
                    $('#district').html('<option value="">区县</option>');
                    if ($('#J_province_id').length > 0) {
                        $('#J_province_id').val(province_id);
                    }
                } else {
                    layer.alert(res.msg);
                }
            }
        });
    };

    addr.get_districts_by_ciry_id = function (city_id, show_elem) {
        $.ajax({
            type: 'get',
            dataType: 'json',
            url: '/ajax/address/getDistricts',
            data: {"city_id": city_id},
            success: function (res) {
                if (res.status == 'success') {
                    show_elem.html(res.data);
                    if ($('#J_city_id').length > 0) {
                        $('#J_city_id').val(city_id);
                    }
                } else {
                    layer.alert(res.msg);
                }
            }
        });
    };

    addr.get_shipping_address = function (params, show_elem) {
        if ($('#J_address_list').length > 0) {
            $('.J_address').remove();
            show_elem.children('table').append('<tr><td colspan="5" style="text-align: center;">' +
                '<img src="/public/static/index/image/loading.gif" />' +
                '</td></tr>');
        }

        setTimeout(function () {
            $.ajax({
                type: 'get',
                dataType: 'json',
                url: '/ajax/index.address/getShippingAddress',
                data: params,
                success: function (res) {
                    if (res.status == 'success') {
                        show_elem.html(res.data);

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
    };

    addr.set_default_address = function (address_id, params, show_elem) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: '/ajax/index.address/setDefaultShippingAddrss',
            data: {address_id: address_id},
            success: function (res) {
                if (res.status == 'success') {
                    if (params != undefined && show_elem != undefined) {
                        addr.get_shipping_address(params, show_elem);
                    } else {
                        layer.alert(res.msg);
                    }
                } else {
                    layer.alert(res.msg);
                }
            }
        });
    };

    addr.delete_address = function (address_id, params, show_elem) {
        layer.confirm("是否确认删除？", function (i) {
            layer.close(i);
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '/ajax/index.address/deleteShippingAddress',
                data: {address_id: address_id},
                success: function (res) {
                    if (res.status == 'success') {
                        addr.get_shipping_address(params, show_elem);
                    } else {
                        layer.alert(res.msg);
                    }
                }
            });
        });
    };
})(Address);