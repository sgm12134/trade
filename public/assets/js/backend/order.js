define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'order/index' + location.search,
                    // add_url: 'order/add',
                    // edit_url: 'order/edit',
                    // del_url: 'order/del',
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
                        // {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'user.username', title: __('用户'), operate: 'LIKE'},
                        {field: 'order_no', title: __('Order_no'), operate: 'LIKE'},
                        {field: 'pay_way', title: __('Pay_way'), formatter: Table.api.formatter.label,searchList: {
                                1:'银行卡',
                                2:'微信',
                                3:'支付宝',
                            }},
                        {field: 'username', title: __('收款人')},
                        
                        {field: 'ccounts', title: __('收款账户'), operate: false,formatter: function (value,row,index) {
                                return row.bankaccount+row.wechat+row.alipay;

                            }},


                        // {field: 'bankaccount', title: __('Bankaccount'), operate: 'LIKE'},
                        {field: 'bankaddress', title: __('收款图/银行支行'),formatter: function (value,row,index) {
                            if(row.pay_way !=1){
                                return "<a href='javascript:void(0)' data-tips-image='"+value+"'><img src='"+row.collection_code+"' class='img-sm img-center' ></a>";
                            }else {
                                return  value
                            }

                            }},
                        {field: 'payment_voucher', title: __('付款凭证'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'amount', title: __('代付金额')},
                        {field: 'usdtprice', title: __('实时u价')},
                        {field: 'allusdt', title: __('USDT总金额')},
                        {field: 'fee', title: __('交易手续费')},
                        {field: 'admin.username', title: __('委托打款'), operate: 'LIKE'},
                        {field: 'entrust_money', title: __('委派佣金')},
                        {field: 'order_transaction_profit', title: __('订单交易手续费')},

                        {field: 'remark', title: __('Remark'), operate: 'LIKE'},

                        // {field: 'usdtnum', title: __('代付USDT数量')},
                        // {field: 'usdtprice', title: __('Usdtprice')},
                        {field: 'submit_time', title: __('Submit_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},



                        // {field: 'wechat', title: __('Wechat')},
                        // {field: 'alipay', title: __('Alipay'), operate: 'LIKE'},
                        // {field: 'collection_code', title: __('Collection_code'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'state', title: __('状态'), formatter: Table.api.formatter.label,searchList: {
                                1:'待审核',
                                2:'打款中',
                                3:'已打款',
                                4:'下发失败',
                            }},
                        {field: 'operate', title: __('Operate'), table: table,width:150,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'agree',
                                    text: __('已付款'),
                                    title: __('已付款'),
                                    icon: 'fa fa-check',
                                    classname: 'btn btn-xs btn-success btn-magic btn-dialog',
                                    url: 'order/agree',
                                    callback: function (data) {
                                        table.bootstrapTable('refresh', {});
                                        return true;
                                    },

                                    visible:function (data) {
                                        if(data.state ==2 || data.state ==1 ){
                                            return  true
                                        }else{
                                            return  false
                                        }
                                    }
                                },

                                {
                                    name: 'entrust',
                                    text: __('分配打款员'),
                                    title: __('分配打款员'),
                                    classname: 'btn btn-xs btn-success btn-magic btn-dialog',
                                    icon: 'fa fa-check',
                                    url: 'order/entrust',
                                    callback :function(){
                                        table.bootstrapTable('refresh', {});
                                    },

                                    visible:function (data) {
                                        if(data.admin_id ==0 ){
                                            return  true
                                        }else{
                                            return  false
                                        }
                                    }
                                },
                                {
                                    name: 'refuse',
                                    text: __('下发失败'),
                                    title: __('下发失败'),
                                    classname: 'btn btn-xs btn-primary  btn-magic btn-dialog',
                                    icon: 'fa fa-close',
                                    url: 'order/refuse',
                                    callback: function (data) {
                                        table.bootstrapTable('refresh', {});
                                        return true;
                                    },
                                    visible:function (data) {
                                        if(data.state ==2 || data.state ==1){
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
        entrust:function (){
            Controller.api.bindevent();
        },
        agree: function () {
            Controller.api.bindevent();
        },
        refuse: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            accounts: function (d) {
                console.log(d)
                return 111;
            }
        }
    };
    return Controller;
});
