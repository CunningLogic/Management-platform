(function(require, requirejs) {

    var PUBLIC_JS_PATH = '/public/js',
        EXTERNAL_JS_PATH = PUBLIC_JS_PATH + '/externals',
        COMPONENT_JS_PATH = PUBLIC_JS_PATH + '/components';

    var LIB_JS_PATH = PUBLIC_JS_PATH + '/lib';

    var CLIPBOARD_PATH = EXTERNAL_JS_PATH + '/ZeroClipboard';

    requirejs.config({
        urlArgs: 'v=18',
        baseUrl: PUBLIC_JS_PATH,
        waitSeconds: 0,
        paths: {
            'jquery': EXTERNAL_JS_PATH + '/jquery-1.10.1.min',
            'template': EXTERNAL_JS_PATH + '/template',
            'bootstrap': EXTERNAL_JS_PATH + '/bootstrap.min',
            'simpleCalendar': COMPONENT_JS_PATH + '/Calendar/SimpleCalendar',

            'httpHelper': LIB_JS_PATH + '/HttpHelper',

            'geoData': COMPONENT_JS_PATH + '/Geo/GeoData',
            'geo': COMPONENT_JS_PATH + '/Geo/Geo',
            'geoProvider': COMPONENT_JS_PATH + '/Geo/GeoProvider',

            'zeroClipboard': CLIPBOARD_PATH + '/ZeroClipboard.min'
        },
        shim: {
            'bootstrap': {
                deps: ['jquery'],
                exports: '$'
            },

            'geoData': {
                exports: 'geoData'
            }
        }
    });


    requirejs(['bootstrap'], function($) {
        var $viewModal = $('#viewModal');
        $viewModal.on('show.bs.modal', function(e) {
            var $target = $(e.relatedTarget);
            var src = $target.data('src');
            $(this).find('.modal-body').html('<img src="' + src + '" />');
        });
    });

    requirejs(['form/ActiveForm'], function(ActiveForm) {
        new ActiveForm();
    });


})(require, requirejs);
