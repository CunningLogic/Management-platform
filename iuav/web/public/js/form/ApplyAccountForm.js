(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'httpHelper', 'template', 'geo'], factory);
    } else {
        window.ApplyAccountForm = factory(jQuery, HttpHelper, template, Geo);
    }

}(function($, HttpHelper, template, Geo) {

    function ApplyAccountForm() {
        var $form = $('#applyAccountForm'),
            $result = $form.find('.form-result');

        function initGeo() {
            $form.find('.geo').each(function() {
                new Geo($(this));
            });
        }

        function bindSubmitEvent() {
            $form.on('submit', function() {
                var url = $(this).attr('action');
                var params = {};
                $(this).find('[name]').each(function() {
                    var name = $(this).attr('name'),
                        value = $(this).val();

                    params[name] = value;
                });


                HttpHelper.post(url, params, {
                    success: function(resp) {
                        if (resp['status'] == 200) {
                            location.href = location.href;
                        } else {
                            $result.text(resp['extra']['msg']).show();
                            setTimeout(function() {
                                $result.fadeOut('fast');
                            }, 3000);
                        }
                    },
                    error: function() {
                        $result.text('服务器出错，请重试').show();
                        setTimeout(function() {
                            $result.fadeOut('fast');
                        }, 3000);
                    }
                });

                return false;
            });
        }

        return function() {
            initGeo();
            bindSubmitEvent();
        }();
    }

    return ApplyAccountForm;
}));