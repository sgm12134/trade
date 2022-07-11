define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'tx/index' + location.search,
                    // add_url: 'tx/add',
                    edit_url: 'tx/edit',
                    del_url: 'tx/del',
                    // multi_url: 'tx/multi',
                    // import_url: 'tx/import',
                    table: 'tx',
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
                        {field: 'amount', title: __('提交数量'), operate: 'LIKE'},
                        {field: 'real_amount', title: __('到账金额'), operate: 'LIKE'},
                        {field: 'address', title: __('提笔地址'), operate: 'LIKE'},
                        {field: 'real_amount', title: __('Real_amount'), operate: 'LIKE'},
                        {field: 'state', title: __('状态'), formatter: Table.api.formatter.label,searchList: {
                                1:'待审核',
                                2:'通过',
                                3:'拒绝',
                            }},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'remark', title: __('Remark'), operate: 'LIKE'},
                        {field: 'operate', title: __('Operate'), table: table,width:150,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'agree',
                                    text: __('同意'),
                                    icon: 'fa fa-check',
                                    classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                    url: 'tx/agree',
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
                                    url: 'tx/refuse',
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
        agree: function () {
            Controller.api.bindevent();
        },
        refuse: function () {
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
