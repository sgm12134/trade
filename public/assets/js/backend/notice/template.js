define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Controller.api.bindevent();

            $(document).on('change', '.switch-input', function () {
                // ajax请求
                var params = {
                    'notice_event_id': $(this).parents('.parent').data('notice_event_id'),
                    'platform': $(this).parents('.parent').data('platform'),
                    'type': $(this).parents('.parent').data('type'),
                    'visible_switch': $(this).val()
                };
                Fast.api.ajax({
                    url: 'notice/template/visible',
                    data: params,
                    loading: false
                });
            });

            // tab自定义
            $('.panel-heading [data-field] a[data-toggle="tab"]').unbind('shown.bs.tab');
            $('.panel-heading [data-field] a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var platform = $(this).data('value');
                var url = 'notice/template?platform='+platform;
                url = Fast.api.fixurl(url);
                Layer.load();
                location.href = url;
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});