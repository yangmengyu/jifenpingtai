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
            $('.btn-score').click(function () {
                var qrimage = $(this).data('qrimage');
                var sms = $(this).data('sms');
                var score = $(this).data('score');
                layer.open({
                    type: 1
                    ,area: ['350px', '500px']
                    ,title: '兑换'+score+'积分'
                    ,shade: 0.6
                    ,maxmin: true
                    ,anim: 1
                    ,content: '<div style="padding:50px;">' +
                    '<p class="text-center"><img width="200" src="'+qrimage+'" alt=""></p>'+
                    '注：扫码后点击发送，兑换短信发出后会收到一条确认短信，根据对应的短信输入对应的数据回复确认，即完成兑换,若发送失败请手动发短信<span class="text-danger">' +
                    sms +
                    '</span>到' +
                    '<span class="text-danger">10658999</span>' +
                    '</div>'
                });
            });
            $('#getsmscode').click(function () {
                var mobile = $('#mobile').val();
                var channel = $(this).data('channel');
                $.post('/index/score/add',{mobile:mobile,channel:channel},function (res) {
                    $('#LoginKey').val(res.data.LoginKey);
                    layer.msg(res.msg);
                });
            });
            Form.api.bindevent($("#add-form"));
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});