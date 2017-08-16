(function() {

    var PUBLIC_JS_PATH = '/public/js',
        PUBLIC_CSS_PATH = '/public/css';

    var EXTERNAL_JS_PATH = PUBLIC_JS_PATH + '/externals',
        COMPONENT_JS_PATH = PUBLIC_JS_PATH + '/components',
        LIB_JS_PATH = PUBLIC_JS_PATH + '/lib';

    var CLIPBOARD_PATH = EXTERNAL_JS_PATH + '/ZeroClipboard';

    requirejs.config({
        baseUrl: '?v=17',
        waitSeconds: 0,
        paths: {
            'jquery': EXTERNAL_JS_PATH + '/jquery-1.10.1.min',
            'template': EXTERNAL_JS_PATH + '/template',
            'bootstrap': EXTERNAL_JS_PATH + '/bootstrap.min',
            'simpleCalendar': COMPONENT_JS_PATH + '/Calendar/SimpleCalendar',

            // date time picker
            'dtp': EXTERNAL_JS_PATH + '/bootstrap.datetimepicker.min',
            'moment': EXTERNAL_JS_PATH + '/moment.min',

            'zeroClipboard': CLIPBOARD_PATH + '/ZeroClipboard.min'
        },
        map: {
            '*': {
                'css': EXTERNAL_JS_PATH + '/require-css.min.js'
            }
        },
        shim: {
            'bootstrap': {
                deps: ['jquery'],
                exports: '$'
            },

            'dtp': {
                deps: ['bootstrap', 'moment', 'css!' + PUBLIC_CSS_PATH + '/externals/bootstrap.datetimepicker.min'],
                exports: '$'
            }
        }
    });

    //requirejs(['template', 'simpleCalendar'], function(template, SimpleCalendar) {
    //    new SimpleCalendar({
    //        tpl: 'tpl_simpleCaledar'
    //    });
    //});

    requirejs(['dtp'], function() {
        var getUrlParams = function() {
            var paramsStr = document.location.search.substr(1);
            var params = {};
            if (paramsStr) {
                var paramsAttr = paramsStr.split('&');
                for (var i = 0; i < paramsAttr.length; i++) {
                    var paramAttr = paramsAttr[i].split('=');
                    params[paramAttr[0]] = paramAttr[1];
                }
            }
            return params;
        };

        var params = getUrlParams();

        var $searchForm = $('#searchForm');
        var $datePicker = $('#datePicker');

        $datePicker.datetimepicker({
            format: 'YYYY-MM-DD',
            locale: 'zh-cn'
        });

        if (!$.isEmptyObject(params)) {
            $searchForm.find('[name=typename]').val(params['typename']);
            $searchForm.find('[name=typevalue]').val(params['typevalue']);
            $datePicker.data('DateTimePicker').date(params['begin']);
        }

        var convertObjectToUrlParams = function(object) {
            var urlParams = '';
            for (var k in object) {
                urlParams += k + '=' + object[k] + '&';
            }
            return urlParams.substring(0, urlParams.length - 1);
        };

        $searchForm.on('submit', function() {
            var params = {};

            $(this).find('[name]').each(function() {
                var name = $(this).attr('name'),
                    value = $(this).val();

                if (value) {
                    if (name == 'date') {
                        params['begin'] = params['end'] = value;
                    } else {
                        params[name] = value;
                    }
                }
            });

            location.href = location.pathname + '?' + convertObjectToUrlParams(params);

            return false;
        });
    });

    requirejs(['bootstrap', 'zeroClipboard'], function($, ZeroClipboard) {
        var $modal = $('#infoModal');
        var $title = $modal.find('.info-title'),
            $data = $modal.find('.info-data'),
            $copy = $modal.find('.copy');

        var $copies = $('.copy');

        var clip = new ZeroClipboard($copies);

        // Initialized Copy Buttons
        $copies.popover({
            container: 'body',
            placement: 'top',
            trigger: 'focus',
            content: window.LANGUAGE_DATA['iuav_copy_success']
        });

        clip.on('ready', function() {
            this.on('aftercopy', function(e) {
                $(e.target).popover('show');
            });
        });

        clip.on('error', function() {
            $copies.remove();
            ZeroClipboard.destroy();
        });

        $modal.on('show.bs.modal', function(e) {
            var $button = $(e.relatedTarget);
            var code = $button.data('code'),
                text = $button.text();

            $title.text(text);
            $data.text(code);

            $copy.attr('data-clipboard-text', code);
        });
    });
})(require, requirejs);