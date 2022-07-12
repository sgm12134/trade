define([], function () {
    require.config({
    paths: {
        'qrcode': '../addons/notice/js/qrcode',
        'HackTimer': '../addons/notice/js/HackTimer.min',
    },
    shim: {
    }
});

function ajaxInit() {
    if (Config.modulename == 'admin') {
        if (!(Config.controllername == 'index' && Config.actionname == 'index' && Config.notice.admin_real == 1)) {
            return false;
        }
    } else if (Config.modulename == 'index'){
        if (Config.notice.user_real != 1) {
            return false;
        }
        if (!indexUrlCheck()) {
            return false;
        }
    } else {
        return false;
    }
    console.log('ajax_init');

    require(['HackTimer'], function (HackTimer) {
        var url = '';
        if (Config.modulename == 'admin') {
            url = 'notice/admin/statistical';
        }
        if (Config.modulename == 'index') {
            url = '/addons/notice/api/statistical';
        }

        // 获取新消息并提示
        function notice() {
            Fast.api.ajax({
                url: url,
                loading: false
            }, function (data, res) {
                if (data.new) {
                    Toastr.info(data.new.content);
                }
                if (Config.modulename == 'admin') {
                    Backend.api.sidebar({
                        'notice/admin': data.num,
                    });
                }
                setTimeout(function () {
                    notice();
                }, 5000);
                return false;
            }, function () {
                return false;
            });
        };

        notice();
    });
};

function wsInit() {
    if (Config.modulename == 'admin') {
        if (!(Config.controllername == 'index' && Config.actionname == 'index' && Config.notice.admin_real == 2)) {
            return false;
        }
    } else if (Config.modulename == 'index'){
        if (!indexUrlCheck()) {
            return false;
        }
        if (Config.notice.user_real != 2) {
            return false;
        }
    } else {
        return false;
    }
    console.log('ws_init');

    let NhWs = {
        ws: null,
        timer: null,
        bindurl: '',
        url: '',
        connect: function () {
            var ws = new WebSocket(this.url);
            this.ws = ws;

            ws.onmessage = this.onmessage;
            ws.onclose = this.onclose;
            ws.onerror = this.onerror;
            ws.onopen = this.onopen;
        },
        onmessage: function (e) {
            // json数据转换成js对象
            var data = e.data;
            try {
                JSON.parse(data);
                data = JSON.parse(data) ? JSON.parse(data) : data;
            } catch {
                console.log('ws接收到非对象数据', data);
                return true;
            }
            console.log('ws接收到数据', data, e.data);

            var type = data.type || '';
            var resdata = data.data ? data.data : {};
            switch(type){
                case 'init':
                    $.ajax(NhWs.bindurl, {
                        data: {
                            client_id: data.client_id
                        },
                        method: 'post'
                    })
                    break;
                case "new_notice":
                    if (Config.modulename == 'admin') {
                        Backend.api.sidebar({
                            'notice/admin': resdata.num,
                        });
                    }
                    Toastr.info(resdata.msg);

                    // 发送ajax到后台告诉已经看过这条消息
                    Fast.api.ajax({
                        url: '/addons/notice/api/cache',
                        data: {
                            time: resdata.time,
                            module: Config.modulename
                        },
                        method: 'post'
                    }, function () {
                        return false;
                    });
            }
        },
        onclose: function () {
            console.log('连接已断开，尝试自动连接');
            setTimeout(function () {
                NhWs.connect();
            }, 5000);
        },

        onopen: function () {
            this.timer = setInterval(function () {
                NhWs.send({"type":"ping"});
            }, 20000);
        },
        onerror: function () {
            console.log('ws连接失败');
            Toastr.error('ws连接失败');
        },

        // 发送数据
        send: function (data) {
            if (typeof data == "object") {
                data = JSON.stringify(data);
            }
            this.ws.send(data);
        },
    };

    if (Config.modulename == 'admin') {
        NhWs.bindurl = Fast.api.fixurl('/addons/notice/ws/bindAdmin');
        // ajax请求获取消息数量等
        Fast.api.ajax({
            url: 'notice/admin/statistical',
            loading: false,
            method: 'post',
        }, function (data, res) {
            if (data.new) {
                Toastr.info(data.new.content);
            }
            Backend.api.sidebar({
                'notice/admin': data.num,
            });
            return false;
        }, function () {
            return false;
        });

    }

    if (Config.modulename == 'index') {
        NhWs.bindurl = Fast.api.fixurl('/addons/notice/ws/bind');
        // ajax请求最新获取消息数量等
        Fast.api.ajax({
            url: '/addons/notice/api/statistical',
            loading: false,
            method: 'post',
        }, function (data, res) {
            if (data.new) {
                Toastr.info(data.new.content);
            }
            return false;
        }, function () {
            return false;
        });
    }
    NhWs.url = Config.notice.wsurl;

    require(['HackTimer'], function (HackTimer) {
        NhWs.connect();
    });
};

function indexUrlCheck() {
    if (Config.modulename == 'index') {
        var url = Config.controllername+'/'+Config.actionname;
        if (Config.notice.user_real_url.indexOf('*') === -1) {
            if (Config.notice.user_real_url.indexOf(url) === -1) {
                return false;
            }
        }
    }

    return true;
};

require([], function (undefined) {
    // ajax轮询
    ajaxInit();

    wsInit();

    // 后台绑定按钮
    if (Config.modulename == 'admin' && Config.controllername == 'general.profile' && Config.actionname == 'index') {
        $('[type="submit"]').before('<button style="margin-right: 5px;" type="button" class="btn btn-primary btn-dialog"  data-url="notice/admin_mptemplate/bind">模版消息(公众号)</button>');
    }

});
window.UEDITOR_HOME_URL = Config.__CDN__ + "/assets/addons/ueditor/";
require.config({
    paths: {
        'ueditor.config': '../addons/ueditor/ueditor.config',
        'ueditor': '../addons/ueditor/ueditor.all.min',
        'ueditor.zh': '../addons/ueditor/i18n/zh-cn/zh-cn',
        'zeroclipboard': '../addons/ueditor/third-party/zeroclipboard/ZeroClipboard.min',
    },
    shim: {
        'ueditor': {
            deps: ['zeroclipboard', 'ueditor.config'],
            exports: 'UE',
            init: function (ZeroClipboard) {
                //导出到全局变量，供ueditor使用
                window.ZeroClipboard = ZeroClipboard;
            },
        },
        'ueditor.zh': ['ueditor']
    }
});
require(['form', 'upload'], function (Form, Upload) {
    var _bindevent = Form.events.bindevent;
    Form.events.bindevent = function (form) {
        _bindevent.apply(this, [form]);
        try {
            //绑定editor事件
            require(['ueditor', 'ueditor.zh'], function (UE, undefined) {
                UE.list = [];
                window.UEDITOR_CONFIG['uploadService'] = function (context, editor) {
                    return {
                        Upload: () => { return Upload },
                        Fast: () => { return Fast },
                    }
                };
                $(Config.ueditor.classname || '.editor', form).each(function () {
                    var id = $(this).attr("id");
                    var name = $(this).attr("name");
                    $(this).removeClass('form-control');
                    UE.list[id] = UE.getEditor(id, {
                        allowDivTransToP: false, //阻止div自动转p标签
                        initialFrameWidth: '100%',
                        initialFrameHeight: 320,
                        autoFloatEnabled: false,
                        baiduMapAk: Config.ueditor.baiduMapAk || '', //百度地图api密钥（ak）
                        // autoHeightEnabled: true, //自动高度
                        zIndex: 90,
                        xssFilterRules: false,
                        outputXssFilter: false,
                        inputXssFilter: false,
                        catchRemoteImageEnable: true,
                        imageAllowFiles: '',//允许上传的图片格式，编辑器默认[".png", ".jpg", ".jpeg", ".gif", ".bmp"]
                    });
                    UE.list[id].addListener("contentChange", function () {
                        $('#' + id).val(this.getContent());
                        $('textarea[name="' + name + '"]').val(this.getContent());
                    })
                });
            })
        } catch (e) {
            console.log('绑定editor事件', e)
        }
    }
});
});