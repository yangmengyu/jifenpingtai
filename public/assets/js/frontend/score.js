define(['jquery', 'bootstrap', 'frontend', 'form', 'template','table'], function ($, undefined, Frontend, Form, Template, Table) {
    var validatoroptions = {
        invalid: function (form, errors) {
            $.each(errors, function (i, j) {
                Layer.msg(j);
            });
        }
    };
    var Controller = {
        duihuan:function(){
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'score/duihuan/channel/'+Config.channel+'.html',
                    add_url: 'order/add',
                    edit_url: 'order/edit',
                    del_url: 'order/del',
                    multi_url: 'order/multi',
                    table: 'order',
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
                cardView: cardView(),
                columns: [
                    [
                        {field: 'id', title: 'ID'},
                        //{field: 'channel', title: '兑换通道', searchList: {"mobile_woerma":'移动沃尔玛',"mobile_maidelong":'移动麦德龙','unicom_woerma':'联通沃尔玛','unicom_maidelong':'联通麦德龙'}, formatter: Table.api.formatter.normal},
                        {field: 'user.nickname', title: '用户'},
                        //{field: 'order', title: '订单号'},
                        {field: 'mobile', title: '手机号'},
                        {field: 'amount', title: '金额', operate:'BETWEEN'},
                        {field: 'return_amount', title: '返费', operate:'BETWEEN'},
                        {field: 'area', title: '归属地'},
                        {field: 'status', title: '状态', searchList: {"0":'兑换中',"1":'成功',"2":'失败'}, formatter: Table.api.formatter.status},
                        {field: 'memo', title: '说明'},
                        {field: 'createtime', title: '创建时间', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: '更新时间', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            $('.btn-score').click(function () {
                var qrimage = $(this).data('qrimage');
                var sms = $(this).data('sms');
                var score = $(this).data('score');
                var content = '<div class="panel-body"><p class="text-center"><img width="200" src="'+qrimage+'" alt=""></p>';
                    if(sms == ''){
                        content += '注：扫码后进入联通积分商城，点击‘立刻兑换’（第一次登录需输入手机号和登录密码或使用随机密码登录），获取到验证码兑换';
                    }else{
                        content += '注：扫码后点击发送，兑换短信发出后会收到一条确认短信，根据对应的短信输入对应的数据回复确认，即完成兑换,若发送失败请手动发短信<span class="text-danger">' +
                            sms +
                            '</span>到' +
                            '<span class="text-danger">10658999</span>';
                    }
                    content += '</div>';
                layer.open({
                    type: 1
                    ,area: [Config.mobile?'90%':'400px', '450px']
                    ,title: '兑换'+score+'积分'
                    ,shade: 0.6
                    ,maxmin: true
                    ,anim: 1
                    ,content:content
                });
            });
            $('#getsmscode').click(function () {
                var mobile = $('#mobile').val();
                var channel = $(this).data('channel');
                $.post('/index/score/getSmsCode',{mobile:mobile,channel:channel},function (res) {
                   /* if(res.data.LoginKey){
                        $('#LoginKey').val(res.data.LoginKey);
                    }*/
                    layer.msg(res.msg);
                });
            });
            $('#shuoming').click(function () {
                var channel = $(this).data('channel');
                if(channel == 'mobile_woerma'){
                    var content =  '<div class="panel-body">' +
                        '<p>1.沃尔玛兑换目前支持北京、上海、浙江、安徽、福建、广东、贵州、河北、湖北、湖南、吉林、江苏、内蒙古、山东、山西、陕西、四川、天津、重庆、河南区域。</p>'+
                        '<p>2.以上省份除了河南、四川不支持短信兑换，其余省份用户均可使用扫码或短信指令兑换，且每种面值限兑换一次。如分值较多大客户可以用服务密码加入购物车兑换。</p>'+
                        '<p>3.客户所有积分兑换完成之后按照下方提示输入客户手机号码，获取验证码点击-提交-后台会自动检测此号码里面兑换的所有订单并自动核销。</p>'+
                        '<p>4.如遇系统延时兑换之后卡券没有到账（系统会有红色弹窗提示：还没找到兑换信息，请稍后再次提交）系统自带有记忆登录功能，12小时之内再次提交只需输入客户号码，点击-获取验证码（此时并不会给客户下发验证码，会有弹窗）直接上报成功。</p>'+
                        '<p>5.兑换完成之后请注意查看订单明细后台会在1分钟之内更新状态结果。</p>'+
                        '</div>';
                }else if(channel == 'mobile_maidelong'){
                    var content = '<div class="panel-body">' +
                        '<p>1.麦德龙兑换目前支持安徽、北京市、福建、广东、河南、湖北、湖南、江苏、吉林、江西、宁夏、山东、陕西、上海市、四川、天津市、浙江、重庆市等地区的麦德龙超市门店（除西安浐灞商场外）消费使用。</p>'+
                        '<p>2.以上省份除了河南、四川不支持短信兑换，其余省份用户均可使用扫码或短信指令兑换，且每种面值限兑换一次。如分值较多大客户可以用服务密码加入购物车兑换。</p>'+
                        '<p>3.客户所有积分兑换完成之后按照下方提示输入客户手机号码，获取验证码点击-提交-后台会自动检测此号码里面兑换的所有订单并自动核销。</p>'+
                        '<p>4.如遇系统延时兑换之后卡券没有到账（系统会有红色弹窗提示：还没找到兑换信息，请稍后再次提交）系统自带有记忆登录功能，12小时之内再次提交只需输入客户号码，点击-获取验证码（此时并不会给客户下发验证码，会有弹窗）直接上报成功。</p>'+
                        '<p>5.兑换完成之后请注意查看订单明细后台会在1分钟之内更新状态结果。</p>'+
                        '</div>';
                }else if(channel == 'unicom_woerma'){
                    var content =  '<div class="panel-body">' +
                        '<p>1.沃尔玛兑换目前支持北京、上海、浙江、安徽、福建、广东、贵州、河北、湖北、湖南、吉林、江苏、内蒙古、山东、山西、陕西、四川、天津、重庆、河南区域。</p>'+
                        '<p>2.扫码后进入联通积分商城，点击‘立刻兑换’（第一次登录需输入手机号和登录密码或使用随机密码登录），获取到验证码兑换。</p>'+
                        '<p>3.客户所有积分兑换完成之后按照下方提示输入客户手机号码，获取验证码点击-提交-后台会自动检测此号码里面兑换的所有订单并自动核销。</p>'+
                        '<p>4.如遇系统延时兑换之后卡券没有到账（系统会有红色弹窗提示：还没找到兑换信息，请稍后再次提交）系统自带有记忆登录功能，12小时之内再次提交只需输入客户号码，点击-获取验证码（此时并不会给客户下发验证码，会有弹窗）直接上报成功。</p>'+
                        '<p>5.兑换完成之后请注意查看订单明细后台会在1分钟之内更新状态结果。</p>'+
                        '</div>';
                }else{
                    var content =  '<div class="panel-body">' +
                        '<p>1.麦德龙兑换目前支持安徽、北京市、福建、广东、河南、湖北、湖南、江苏、吉林、江西、宁夏、山东、陕西、上海市、四川、天津市、浙江、重庆市等地区的麦德龙超市门店（除西安浐灞商场外）消费使用。</p>'+
                        '<p>2.扫码后进入联通积分商城，点击‘立刻兑换’（第一次登录需输入手机号和登录密码或使用随机密码登录），获取到验证码兑换。</p>'+
                        '<p>3.客户所有积分兑换完成之后按照下方提示输入客户手机号码，获取验证码点击-提交-后台会自动检测此号码里面兑换的所有订单并自动核销。</p>'+
                        '<p>4.如遇系统延时兑换之后卡券没有到账（系统会有红色弹窗提示：还没找到兑换信息，请稍后再次提交）系统自带有记忆登录功能，12小时之内再次提交只需输入客户号码，点击-获取验证码（此时并不会给客户下发验证码，会有弹窗）直接上报成功。</p>'+
                        '<p>5.兑换完成之后请注意查看订单明细后台会在1分钟之内更新状态结果。</p>'+
                        '</div>';
                }

                layer.open({
                    type: 1 //Page层类型
                    ,area: [Config.mobile?'90%':'400px', '500px']
                    ,title: '使用说明'
                    ,shade: 0.6 //遮罩透明度
                    ,maxmin: true //允许全屏最小化
                    ,anim: 1 //0-6的动画形式，-1不开启
                    ,content:content
                });
            });
            Form.api.bindevent($("#add-form"));
        },
        tmall:function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'score/tmall/channel/'+Config.channel+'.html',
                    add_url: 'order/add',
                    edit_url: 'order/edit',
                    del_url: 'order/del',
                    multi_url: 'order/multi',
                    table: 'order',
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
                cardView: cardView(),
                columns: [
                    [
                        {field: 'id', title: 'ID'},
                        //{field: 'channel', title: '兑换通道', searchList: {"mobile_woerma":'移动沃尔玛',"mobile_maidelong":'移动麦德龙','unicom_woerma':'联通沃尔玛','unicom_maidelong':'联通麦德龙'}, formatter: Table.api.formatter.normal},
                        {field: 'user.nickname', title: '用户'},
                        //{field: 'order', title: '订单号'},
                        {field: 'mobile', title: '手机号'},
                        {field: 'amount', title: '金额', operate:'BETWEEN'},
                        {field: 'return_amount', title: '返费', operate:'BETWEEN'},
                        {field: 'area', title: '归属地'},
                        {field: 'status', title: '状态', searchList: {"0":'兑换中',"1":'成功',"2":'失败'}, formatter: Table.api.formatter.status},
                        {field: 'memo', title: '说明'},
                        {field: 'createtime', title: '创建时间', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: '更新时间', operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                    ]
                ]
            });
            $('#shuoming').click(function () {
                var channel = $(this).data('channel');
                if(channel == 'mobile_tmall'){
                    var content =  '<div class="panel-body">' +
                        '<p>通过换积分的手机号码发送JF至10658999，查询积分后，必须提前输入手机号码（不输号码先兑换无效）根据客户积分值不同选择相对应的面值下单，接下来按系统提示发送相应的短信代码或扫码兑换，并回复确认短信即完成兑换流程。</p>'+
                        '<p>说明：</p>'+
                        '<p>1.移动天猫券无需服务密码每种面值可以叠加兑换5次。</p>'+
                        '<p>2.兑换时间控制在早8点-晚9点之间，其他时间不可兑换。</p>'+
                        '<p>3.进去系统下单的号码一定不要输错，否则兑换失败无法弥补。</p>'+
                        '</div>';
                }

                layer.open({
                    type: 1 //Page层类型
                    ,area: [Config.mobile?'90%':'400px', '500px']
                    ,title: '使用说明'
                    ,shade: 0.6 //遮罩透明度
                    ,maxmin: true //允许全屏最小化
                    ,anim: 1 //0-6的动画形式，-1不开启
                    ,content:content
                });
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            Form.api.bindevent($("#add-form"));
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    function cardView() {
        if(Config.mobile == 1){
            return true;
        }else{
            return false;
        }
    }
    return Controller;
});