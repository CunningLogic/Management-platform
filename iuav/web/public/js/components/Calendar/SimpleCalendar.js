(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'template'], factory);
    } else {
        window.SimpleCalendar = factory(jQuery, template);
    }

}(function($, template) {

    var DEFAULT_SETTINGS = {
        id: 'simpleCalendar',
        tpl: '',
        onCountCallback: null
    };

    var DAYS = ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];

    // Date format
    function dateFormat(dateObject, fmt) {
        var o = {
            "M+" : dateObject.getMonth() + 1,
            "d+" : dateObject.getDate(),
            "h+" : dateObject.getHours(),
            "m+" : dateObject.getMinutes(),
            "s+" : dateObject.getSeconds(),
            "q+" : Math.floor((dateObject.getMonth() + 3) / 3),
            "S"  : dateObject.getMilliseconds()
        };
        if (/(y+)/.test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (dateObject.getFullYear() + "").substr(4 - RegExp.$1.length));
        }
        for (var k in o) {
            if (new RegExp("("+ k +")").test(fmt)) {
                fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
            }
        }
        return fmt;
    }

    function DateObject() {
        var dateObj = new Date();

        this.day = dateObj.getDay();
        this.dayStr = DAYS[this.day];

        this.year = dateFormat(dateObj, 'yyyy');
        this.month = dateFormat(dateObj, 'MM');
        this.date = dateFormat(dateObj, 'dd');

        this.hours = dateFormat(dateObj, 'hh');
        this.minutes = dateFormat(dateObj, 'mm');
        this.seconds = dateFormat(dateObj, 'ss');
    }

    /**
     *  Working on Left-Side
     */
    function SimpleCalendar(inputOpts) {
        var opts = $.extend({}, DEFAULT_SETTINGS, inputOpts);

        this.$target = $('#' + opts.id);
        this.tpl = opts.tpl;
        this.onCountCallback = opts.onCountCallback;

        this.init();
    }

    SimpleCalendar.prototype = {
        init: function() {
            this.dateObj = new DateObject();
            this.updateView();
            this.countDown();
        },

        updateView: function() {
            var html = template(this.tpl, {
                date: this.dateObj
            });
            this.$target.html(html);
        },

        countDown: function() {
            var _this = this;

            var secLeft = 60 - this.dateObj.seconds;
            setTimeout(function() {
                _this.init.call(_this);
            }, secLeft * 1000);
        }
    };

    return SimpleCalendar;
}));