define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme', 'template'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {

            // 在index页面添加按钮事件
            $(document).on('click', '.btn-tx', function () {
                var url = 'auth/admin/out/ids/'+$(this).attr('data-adminid');//弹出窗口 add.html页面的（fastadmin封装layer模态框将以iframe的方式将add输出到index页面的模态框里）
                Fast.api.open(url, __('提现'));
            });


        }
    };

    return Controller;
});
