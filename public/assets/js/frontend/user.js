define(['jquery', 'bootstrap', 'frontend', 'form', 'template','table'], function ($, undefined, Frontend, Form, Template, Table) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        login: function () {
            //本地验证未通过时提示
            $("#login-form").data("validator-options", validatoroptions);

            $(document).on("change", "input[name=type]", function () {
                var type = $(this).val();
                $("div.form-group[data-type]").addClass("hide");
                $("div.form-group[data-type='" + type + "']").removeClass("hide");
                $('#resetpwd-form').validator("setField", {
                    captcha: "required;length(4);integer[+];remote(" + $(this).data("check-url") + ", event=resetpwd, " + type + ":#" + type + ")",
                });
                $(".btn-captcha").data("url", $(this).data("send-url")).data("type", type);
            });

            //为表单绑定事件
            Form.api.bindevent($("#login-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });

            Form.api.bindevent($("#resetpwd-form"), function (data) {
                Layer.closeAll();
            });

            $(document).on("click", ".btn-forgot", function () {
                var id = "resetpwdtpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: __('Reset password'),
                    area: ["450px", "355px"],
                    content: content,
                    success: function (layero) {
                        Form.api.bindevent($("#resetpwd-form", layero), function (data) {
                            Layer.closeAll();
                        });
                    }
                });
            });
            var msg = Config.msg;
            if(msg){
                Toastr.error(msg)
            }
        },
        register: function () {
            //本地验证未通过时提示
            $("#register-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#register-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        changepwd: function () {
            //本地验证未通过时提示
            $("#changepwd-form").data("validator-options", validatoroptions);

            //为表单绑定事件
            Form.api.bindevent($("#changepwd-form"), function (data, ret) {
                setTimeout(function () {
                    location.href = ret.url ? ret.url : "/";
                }, 1000);
            });
        },
        profile: function () {
            // 给上传按钮添加上传成功事件
            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Fast.api.cdnurl(data.url);
                $(".profile-user-img").prop("src", url);
                Toastr.success(__('Upload successful'));
            });
            Form.api.bindevent($("#profile-form"));
            $(document).on("click", ".btn-change", function () {
                var that = this;
                var id = $(this).data("type") + "tpl";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: "修改",
                    area: ["400px", "250px"],
                    content: content,
                    success: function (layero) {
                        var form = $("form", layero);
                        Form.api.bindevent(form, function (data) {
                            location.reload();
                            Layer.closeAll();
                        });
                    }
                });
            });
        },
        index:function () {
            $(document).on("click", ".tixian", function () {
                var id = "tixianhtml";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: '申请提现',
                    area: ["355px", "355px"],
                    content: content,
                    success: function (layero) {
                        Form.api.bindevent($("#tixian-form", layero), function (data,res) {
                            Layer.closeAll();
                            if(res.code === 1){
                                setTimeout(function(){
                                    location.reload();
                                },1500);
                            }

                        });
                    }
                });
            });
            Form.api.bindevent($("#tixian-form"));
        },
        withdraw:function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/withdraw',

                    table: 'withdraw',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showColumns: false,
                showExport: false,
                search:false,
                columns: [
                    [
                        {field: 'id', title: __('Id')},
                        {field: 'amount', title: '金额',operate:'BETWEEN'},
                        {field: 'status', title: '状态', searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'remark', title: '备注'},
                        {field: 'createtime', title: '申请时间', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        /*{field: 'user.username', title: __('User.username')},
                        {field: 'user.nickname', title: __('User.nickname')},*/
                    ]
                ]
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        myuser:function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/myuser',
                    add_url: 'user/add',
                    edit_url: 'user/edit',
                    del_url: 'user/del',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showColumns: false,
                showExport: false,
                columns: [
                    [
                        {field: 'id', title: __('Id'), sortable: true},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        /*{field: 'email', title: __('Email'), operate: 'LIKE'},*/
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        /*{field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image, operate: false},*/
                        /*{field: 'level', title: __('Level'), operate: 'BETWEEN', sortable: true},*/
                        /*{field: 'gender', title: __('Gender'), visible: false, searchList: {1: __('Male'), 0: __('Female')}},*/
                        /*{field: 'score', title: __('Score'), operate: 'BETWEEN', sortable: true},*/
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('Hidden')}},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        balancelog:function () {
            $(document).on("click", ".tixian", function () {
                var id = "tixianhtml";
                var content = Template(id, {});
                Layer.open({
                    type: 1,
                    title: '申请提现',
                    area: ["355px", "355px"],
                    content: content,
                    success: function (layero) {
                        Form.api.bindevent($("#tixian-form", layero), function (data,res) {
                            Layer.closeAll();
                            if(res.code === 1){
                                setTimeout(function(){
                                    location.reload();
                                },1500);
                            }
                        });
                    }
                });
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