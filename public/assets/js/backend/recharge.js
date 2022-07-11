define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'recharge/index' + location.search,
                    add_url: 'recharge/add',
                    edit_url: 'recharge/edit',
                    del_url: 'recharge/del',
                    multi_url: 'recharge/multi',
                    import_url: 'recharge/import',
                    table: 'recharge',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.username', title: __('User_id')},
                        {field: 'amount', title: __('Amount'), operate: 'LIKE'},
                        {field: 'tx_id', title: __('转出地址')},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'state', title: __('状态'), formatter: Table.api.formatter.label,searchList: {
                                1:'待审核',
                                2:'通过',
                                3:'拒绝',
                            }},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('操作时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'remark', title: __('Remark')},
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
        refuse:function (){
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
