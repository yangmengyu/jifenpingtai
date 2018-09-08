define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'scoreproduct/index',
                    add_url: 'scoreproduct/add',
                    edit_url: 'scoreproduct/edit',
                    del_url: 'scoreproduct/del',
                    multi_url: 'scoreproduct/multi',
                    table: 'scoreproduct',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'ID',
                sortName: 'ID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'ID', title: __('Id')},
                        {field: 'name', title: __('Name'), searchList: {"unicom_maidelong":__('Name unicom_maidelong'),"unicom_woerma":__('Name unicom_woerma'),"mobile_maidelong":__('Name mobile_maidelong'),"mobile_woerma":__('Name mobile_woerma')}, formatter: Table.api.formatter.normal},
                        {field: 'score', title: __('Score')},
                        {field: 'return', title: __('Return')},
                        {field: 'sms', title: __('Sms')},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'qrimage', title: __('Qrimage'), formatter: Table.api.formatter.image},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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