define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'withdraw/index',
                    add_url: 'withdraw/add',
                    edit_url: 'withdraw/edit',
                    del_url: 'withdraw/del',
                    multi_url: 'withdraw/multi',
                    table: 'withdraw',
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
                        {field: 'user.nickname', title: __('User_id')},
                        {field: 'amount', title: __('Amount')},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'type', title: __('Type'), searchList: {"alipay":__('Type alipay'),"bank":__('Type bank')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        /*{field: 'user.username', title: __('User.username')},
                        {field: 'user.nickname', title: __('User.nickname')},*/
                        {field: 'operate', title: __('Operate'), table: table, buttons: [
                            {name: 'name1', text: '审核', title: '确认提现', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'withdraw/shenhe',hidden:function (data,row,index) {
                                if(data.status == '0'){
                                    return false;
                                }else{
                                    return true;
                                }
                            }, callback:function(data){}},
                            {name: 'name2', text: '查看', title: '查看', icon: 'fa fa-list', classname: 'btn btn-xs btn-success btn-dialog', url: 'withdraw/detail',hidden:function (data,row,index) {
                                if(data.status == '0'){
                                    return true;
                                }else{
                                    return false;
                                }
                            }, callback:function(data){}},
                            {name: 'del', text: '',confirm:'确认删除该条信息?', title: '删除', icon: 'fa fa-trash', classname: 'btn btn-xs btn-danger btn-ajax', url: 'withdraw/del',success:function(data, ret){
                                if(ret.code == 1){$(".btn-refresh").trigger("click");}
                            },error:function(){}}
                        ], events: Table.api.events.operate, formatter: Table.api.formatter.buttons}
                    ]
                ]
            });

            
            // 绑定TAB事件
            $('.panel-heading a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var field = $(this).closest("ul").data("field");
                var value = $(this).data("value");
                var options = table.bootstrapTable('getOptions');
                options.pageNumber = 1;
                options.queryParams = function (params) {
                    var filter = {};
                    if (value !== '') {
                        filter[field] = value;
                    }
                    params.filter = JSON.stringify(filter);
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
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
        shenhe: function () {
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