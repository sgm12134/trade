define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/index' + location.search,
                    // add_url: 'order/add',
                    edit_url: 'order/edit',
                    del_url: 'order/del',
                    // multi_url: 'order/multi',
                    // import_url: 'order/import',
                    table: 'order',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {field: 'state', title: __('Pay_way'), formatter: Table.api.formatter.label,searchList: {
                                1:'银行卡',
                                2:'微信',
                                3:'支付宝',
                            }},


                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'wechat', title: __('Wechat'), operate: 'LIKE'},
                        {field: 'bank', title: __('Bank'), operate: 'LIKE'},
                        {field: 'bankaccount', title: __('Bankaccount'), operate: 'LIKE'},
                        {field: 'collection_code', title: __('Collection_code'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'alipay', title: __('Alipay'), operate: 'LIKE'},
                        {field: 'state', title: __('状态'), formatter: Table.api.formatter.label,searchList: {
                                1:'待审核',
                                2:'已打款',
                                3:'拒绝',
                            }},
                        {field: 'fee', title: __('手续费USDT'), operate: 'LIKE'},
                        {field: 'all', title: __('All'), operate: 'LIKE'},
                        {field: 'usdtnum', title: __('代付USDT数量')},
                        {field: 'amount', title: __('代付人民币数量')},
                        {field: 'usdtprice', title: __('Usdtprice')},
                        {field: 'submit_time', title: __('Submit_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table,width:150,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'agree',
                                    text: __('同意'),
                                    icon: 'fa fa-check',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url: 'recharge/agree',
                                    confirm: '你确定要同意吗?',
                                    success:function(){
                                        table.bootstrapTable('refresh', {});
                                        return true;
                                    },
                                    visible:function (data) {
                                        if(data.state ==1 ){
                                            return  true
                                        }else{
                                            return  false
                                        }
                                    }
                                },
                                {
                                    name: 'refuse',
                                    text: __('拒绝'),
                                    title: __('拒绝'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-close',
                                    url: 'recharge/refuse',
                                    callback: function (data) {

                                    },

                                    success:function(){
                                        table.bootstrapTable('refresh', {});
                                        return true;
                                    },
                                    visible:function (data) {
                                        if(data.state ==1){
                                            return  true
                                        }else{
                                            return  false
                                        }
                                    }
                                },],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
