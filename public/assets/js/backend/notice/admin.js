define(['jquery', 'bootstrap', 'frontend', 'form', 'template'], function ($, undefined, Frontend, Form, Template) {
    var Controller = {
        index: function () {
            // 标记为已读
            $(document).on('click', '.mark-event', function () {
                Fast.api.ajax($(this).data('url'), function () {
                    location.reload();
                });
            });

            $(document).on('click','.btn-refresh', function () {
                Layer.load();
                location.reload();
            });

            // 重新获取左侧消息数量
            Fast.api.ajax({
                url: 'notice/admin/statistical',
                loading: false,
                method: 'post',
            }, function (data, res) {
                Backend.api.sidebar({
                    'notice/admin': data.num,
                });
                return false;
            }, function () {
                return false;
            });
        }
    };
    return Controller;
});
