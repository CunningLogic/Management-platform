(function() {

    var PUBLIC_JS_PATH = '/public/js',
        PUBLIC_CSS_PATH = '/public/css';

    var EXTERNAL_JS_PATH = PUBLIC_JS_PATH + '/externals',
        COMPONENT_JS_PATH = PUBLIC_JS_PATH + '/components',
        LIB_JS_PATH = PUBLIC_JS_PATH + '/lib';

    requirejs.config({
        urlArgs: 'v=17',
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

            // date time picker
            'dtp': EXTERNAL_JS_PATH + '/bootstrap.datetimepicker.min',
            'moment': EXTERNAL_JS_PATH + '/moment.min'
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
            },

            'geoData': {
                exports: 'geoData'
            }
        }
    });


    //requirejs(['template', 'simpleCalendar'], function(template, SimpleCalendar) {
    //    new SimpleCalendar({
    //        tpl: 'tpl_simpleCaledar'
    //    });
    //});

    requirejs(['bootstrap'], function($) {
        var $applyButton = $('#applyButton');
        var $applyModal = $('#applyModal');
        var initForm;

        $applyButton.on('click', function() {
            $applyModal.modal('show');

            if (!initForm) {
                requirejs(['form/ApplyAccountForm'], function(Form) {
                    new Form();
                });
            }
        });
    });
})(require, requirejs);