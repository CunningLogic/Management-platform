(function() {

    var PUBLIC_JS_PATH = '/public/js',
        EXTERNAL_JS_PATH = PUBLIC_JS_PATH + '/externals';

    requirejs.config({
        baseUrl: '',
        waitSeconds: 0,
        paths: {
            'jquery': EXTERNAL_JS_PATH + '/jquery-1.10.1.min',
            'md5': EXTERNAL_JS_PATH + '/md5'
        },
        shim: {
            'md5': {
                exports: 'MD5'
            }
        }
    });

    requirejs(['jquery'], function($) {
        $.fn.yiiCaptcha = function (method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if (typeof method === 'object' || !method) {
                return methods.init.apply(this, arguments);
            } else {
                $.error('Method ' + method + ' does not exist on jQuery.yiiCaptcha');
                return false;
            }
        };

        var defaults = {
            refreshUrl: undefined,
            hashKey: undefined
        };

        var methods = {
            init: function (options) {
                return this.each(function () {
                    var $e = $(this);
                    var settings = $.extend({}, defaults, options || {});
                    $e.data('yiiCaptcha', {
                        settings: settings
                    });

                    $e.on('click.yiiCaptcha', function () {
                        methods.refresh.apply($e);
                        return false;
                    });

                });
            },

            refresh: function () {
                var $e = this,
                    settings = this.data('yiiCaptcha').settings;

                $.ajax({
                    url: $e.data('yiiCaptcha').settings.refreshUrl,
                    dataType: 'json',
                    cache: false,
                    success: function (data) {
                        $e.attr('src', data.url);
                        $('body').data(settings.hashKey, [data.hash1, data.hash2]);
                    }
                });
            },

            destroy: function () {
                return this.each(function () {
                    $(window).unbind('.yiiCaptcha');
                    $(this).removeData('yiiCaptcha');
                });
            },

            data: function () {
                return this.data('yiiCaptcha');
            }
        };
    });

    requirejs(['jquery', 'md5'], function($, md5) {
        $('#w0-image').yiiCaptcha({
            "refreshUrl": "\/site\/captcha?refresh=1",
            "hashKey":"yiiCaptcha\/site\/captcha"
        }).trigger('click.yiiCaptcha');

        var $loginForm = $('#loginForm');
        $loginForm.on('submit', function() {
            var $passwordInput = $(this).find('#password');

            var username = $(this).find('#username').val(),
                password = $passwordInput.val();

            if (!username || !password) {
                alert('请输入用户名或密码');
                return false;
            } else {
                password = md5(username + password);
                $passwordInput.val(password);
                return true;
            }
        });
    });

})(require, requirejs);