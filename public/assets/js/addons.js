define([], function () {
    require.config({
    paths: {
        'async': '../addons/example/js/async',
        'BMap': ['//api.map.baidu.com/api?v=2.0&ak=mXijumfojHnAaN2VxpBGoqHM'],
    },
    shim: {
        'BMap': {
            deps: ['jquery'],
            exports: 'BMap'
        }
    }
});

//修改验证码为检验验证
require.config({
    paths: {
        'geet': '../addons/geet/js/gt'
    }
});
require(['geet'], function (Geet) {
    var geetInit = false;
    $("input[name='captcha']").each(function () {
        var obj = $(this);
        var form = obj.closest('form');
        $("<input type='hidden' name='geeturl' value='" + (form.attr("action") ? form.attr("action") : location.pathname + location.search) + "' />").appendTo(form);
        $("<input type='hidden' name='geetmodule' value='" + Config.modulename + "' />").appendTo(form);
        $("<input type='hidden' name='geetmoduleurl' value='" + Config.moduleurl + "' />").appendTo(form);
        form.attr('action', Fast.api.fixurl('/addons/geet/index/check'));
        obj.parent().removeClass('input-group').addClass('form-group').html('<div id="embed-captcha"><input type="hidden" name="captcha" class="form-control" data-rule="请完成验证码,验证码:required;" /> </div> <p id="wait" class="show">正在加载验证码......</p>');
        var handlerEmbed = function (captchaObj) {
            // 将验证码加到id为captcha的元素里，同时会有三个input的值：geetest_challenge, geetest_validate, geetest_seccode
            geetInit = captchaObj;
            captchaObj.appendTo("#embed-captcha");
            captchaObj.onReady(function () {
                $("#wait")[0].className = "hide";
            });
            captchaObj.onSuccess(function () {
                var result = captchaObj.getValidate();
                if (result) {
                    $('#embed-captcha input[name="captcha"]').val('ok');
                }
            });
            // 更多接口参考：http://www.geetest.com/install/sections/idx-client-sdk.html
        };
        Fast.api.ajax("/addons/geet/index/start", function (data) {
            // 更多配置参数请参见：http://www.geetest.com/install/sections/idx-client-sdk.html#config
            // 使用initGeetest接口
            // 参数1：配置参数
            // 参数2：回调，回调的第一个参数验证码对象，之后可以使用它做appendTo之类的事件
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                product: "embed", // 产品形式，包括：float，embed，popup。注意只对PC版验证码有效
                width: '100%',
                offline: !data.success // 表示用户后台检测极验服务器是否宕机，一般不需要关注
            }, handlerEmbed);
            form.on("error.form", function (e, data) {
                geetInit.reset();
            });
            return false;
        });
    });
});
window.UMEDITOR_HOME_URL = Config.__CDN__ + "/assets/addons/umeditor/";
require.config({
    paths: {
        'umeditor': '../addons/umeditor/umeditor.min',
        'umeditor.config': '../addons/umeditor/umeditor.config',
        'umeditor.lang': '../addons/umeditor/lang/zh-cn/zh-cn',
    },
    shim: {
        'umeditor': {
            deps: [
                'umeditor.config',
                'css!../addons/umeditor/themes/default/css/umeditor.css'
            ],
            exports: 'UM',
        },
        'umeditor.lang': ['umeditor']
    }
});

//修改上传的接口调用
require(['upload', 'umeditor', 'umeditor.lang'], function (Upload, UME, undefined) {
    //监听上传文本框的事件
    $(document).on("edui.file.change", ".edui-image-file", function (e, up, me, input, callback) {
        for (var i = 0; i < this.files.length; i++) {
            Upload.api.send(this.files[i], function (data) {
                var url = data.url;
                me.uploadComplete(JSON.stringify({url: url, state: "SUCCESS"}));
            });
        }
        up.updateInput(input);
        me.toggleMask("Loading....");
        callback && callback();
    });
    //重写编辑器加载
    UME.plugins['autoupload'] = function () {
        var me = this;
        me.setOpt('pasteImageEnabled', true);
        me.setOpt('dropFileEnabled', true);
        var sendAndInsertImage = function (file, editor) {
            try {
                Upload.api.send(file, function (data) {
                    var url = data.url;
                    editor.execCommand('insertimage', {
                        src: url,
                        _src: url
                    });
                });
            } catch (er) {
            }
        };

        function getPasteImage(e) {
            return e.clipboardData && e.clipboardData.items && e.clipboardData.items.length == 1 && /^image\//.test(e.clipboardData.items[0].type) ? e.clipboardData.items : null;
        }

        function getDropImage(e) {
            return e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
        }

        me.addListener('ready', function () {
            if (window.FormData && window.FileReader) {
                var autoUploadHandler = function (e) {
                    var hasImg = false,
                        items;
                    //获取粘贴板文件列表或者拖放文件列表
                    items = e.type == 'paste' ? getPasteImage(e.originalEvent) : getDropImage(e.originalEvent);
                    if (items) {
                        var len = items.length,
                            file;
                        while (len--) {
                            file = items[len];
                            if (file.getAsFile)
                                file = file.getAsFile();
                            if (file && file.size > 0 && /image\/\w+/i.test(file.type)) {
                                sendAndInsertImage(file, me);
                                hasImg = true;
                            }
                        }
                        if (hasImg)
                            return false;
                    }

                };
                me.getOpt('pasteImageEnabled') && me.$body.on('paste', autoUploadHandler);
                me.getOpt('dropFileEnabled') && me.$body.on('drop', autoUploadHandler);

                //取消拖放图片时出现的文字光标位置提示
                me.$body.on('dragover', function (e) {
                    if (e.originalEvent.dataTransfer.types[0] == 'Files') {
                        return false;
                    }
                });
            }
        });

    };
    $(".editor").each(function () {
        var id = $(this).attr("id");
        $(this).removeClass('form-control');
        UME.list[id] = UME.getEditor(id, {
            serverUrl: Fast.api.fixurl('/addons/umeditor/api/'),
            initialFrameWidth: '100%',
            zIndex: 90,
            xssFilterRules: false,
            outputXssFilter: false,
            inputXssFilter: false,
            imageUrl: '',
            imagePath: Config.upload.cdnurl
        });
    });
});
});