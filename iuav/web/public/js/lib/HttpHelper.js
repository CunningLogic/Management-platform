(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        window.HttpHelper = factory(jQuery);
    }

}(function($) {

    //var DEBUG = true;
    //
    //function _getUrl(url) {
    //    if (/^\//.test(url)) {
    //        url = url.substring(1);
    //    }
    //
    //    var urlArr = url.split('/');
    //    return DEBUG ? '/index.php?g=' + urlArr[0] + '&m=' + urlArr[1] + '&a=' + urlArr[2] : url;
    //}


    function _ajaxSubmit(type, url, params, callback) {
        $.ajax({
            url: url,
            type: type,
            data: params,
            dataType: 'json',
            success: function(data, status, o) {
                callback && callback['success'](data, status, o);
            },
            error: function() {
                callback && callback['error']();
            }
        });
    }

    function _get(url, params, callback) {
        _ajaxSubmit('GET', url, params, callback);
    }

    function _post(url, params, callback) {
        _ajaxSubmit('POST', url, params, callback);
    }

    function _multiplePost(url, params, callback, fileElementId) {
        requirejs(['ajaxFileUpload'], function($) {

            params['ajax'] = 1;

            $.ajaxFileUpload({
                url: _getUrl(url),
                secureuri: false,
                fileElementId: fileElementId || 'file',
                dataType: 'json',
                data: params,
                success: function(data, status, o) {
                    callback && callback['success'](data, status, o);
                },
                error: function() {
                    callback && callback['error']();
                }
            });

        });
    }

    return {
        get: _get,
        post: _post,
        multiplePost: _multiplePost
    };
}));
