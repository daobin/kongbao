//头部导航下拉效果
$('#header .nav-bar>ul>li').hover(
    function () {
        $(this).children('a').css('background', '#333');
        $(this).children('ul').show();
    },
    function () {
        $(this).children('a').css('background', '');
        $(this).children('ul').hide();
    }
);

//标签导航和导航内容展示
$('.J_nav_tab_btn>li').click(function () {
    if ($(this).hasClass('active')) {
        return false;
    }

    $('.J_nav_tab_btn>li').removeClass('active');
    $(this).addClass('active');

    $('.J_nav_tab_content>div').removeClass('active');
    $('.J_nav_tab_content>div').eq($(this).index()).addClass('active');
});

//只允许数字，不包括点号
$(document).on('keyup', '.dv_int', function () {
    $(this).val($(this).val().replace(/[^\d]+/g, ''));
});

//只允许字母、数字、下划线、中横线、点号
$(document).on('keyup', '.dv_word', function () {
    $(this).val($(this).val().replace(/[^\w-_\.]+/g, ''));
});

