var address_search = {
    search: $.trim($('#J_search_keywords').val()),
    page: $.trim($('#J_search_page').val())
};
Address.get_shipping_address(address_search, $('#J_address_list'));

$('#J_province').change(function () {
    var province = $.trim($(this).val());
    $('#J_province option').each(function () {
        if (province == $.trim($(this).attr('value'))) {
            province = $.trim($(this).data('provinceid'))
        }
    });

    if (province == '') {
        $('#J_city').html('<option data-cityid="" value="">城市</option>');
        $('#J_district').html('<option value="">区县</option>');
        return false;
    }

    Address.get_cities_by_province_id(province, $('#J_city'));
});
$('#J_city').change(function () {
    var city = $.trim($(this).val());
    $('#J_city option').each(function () {
        if (city == $.trim($(this).attr('value'))) {
            city = $.trim($(this).data('cityid'))
        }
    });

    if (city == '') {
        $('#J_district').html('<option value="">区县</option>');
        return false;
    }

    Address.get_districts_by_ciry_id(city, $('#J_district'));
});
$('#J_district').change(function () {
    var district = $.trim($(this).val());
    $('#J_district option').each(function () {
        if (district == $.trim($(this).attr('value'))) {
            district = $.trim($(this).data('districtid'))
        }
    });

    if (district == '') {
        return false;
    }

    $('#J_district_id').val(district);
});

$('#address_form').validationEngine('attach', {
    onValidationComplete: function (form, status) {
        if (status == true) {
            //表单验证通过
            $('#address_form').ajaxSubmit({
                url: '/ajax/index.address/addShippingAddress',
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    layer.alert(res.msg);
                    if (res.status == 'success') {
                        $('#address_form').resetForm();

                        var address_search = {
                            search: $.trim($('#J_search_keywords').val()),
                            page: $.trim($('#J_search_page').val())
                        };
                        Address.get_shipping_address(address_search, $('#J_address_list'));
                    }
                },
                error: function (data, status, e) {
                    console.log('********** Ajax Submit Error **********');
                }
            });
        }
    }
});

$('#J_search_btn').click(function(){
    var keywords = $.trim($('#J_search_keywords_tmp').val());
    if(keywords == '' && $.trim($('#J_search_keywords').val()) == ''){
        layer.alert('请输入姓名，电话或地址');
        return false;
    }
    $('#J_search_keywords').val(keywords);
    $('#J_search_page').val('1');

    var address_search = {
        search: $.trim($('#J_search_keywords').val()),
        page: $.trim($('#J_search_page').val())
    };
    Address.get_shipping_address(address_search, $('#J_address_list'));
});

$('#J_address_list').on('click', '.J_default', function () {
    var address_search = {
        search: $.trim($('#J_search_keywords').val()),
        page: $.trim($('#J_search_page').val())
    };
    Address.set_default_address($(this).data('id'), address_search, $('#J_address_list'));
});

$('#J_address_list').on('click', '.J_delete', function () {
    var address_search = {
        search: $.trim($('#J_search_keywords').val()),
        page: $.trim($('#J_search_page').val())
    };
    Address.delete_address($(this).data('id'), address_search, $('#J_address_list'));
});

$('#J_address_list').on('click', '.J_page_click', function () {

    $('#J_search_page').val($(this).data('page'));

    var address_search = {
        search: $.trim($('#J_search_keywords').val()),
        page: $.trim($('#J_search_page').val())
    };
    Address.get_shipping_address(address_search, $('#J_address_list'));
});
