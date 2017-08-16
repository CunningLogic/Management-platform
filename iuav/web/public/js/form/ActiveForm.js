(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(['bootstrap', 'httpHelper', 'template', 'geo', 'form/IdentityCode'], factory);
    } else {
        window.ActiveForm = factory(jQuery, HttpHelper, template, Geo, IdentityCode);
    }

}(function($, HttpHelper, template, Geo, IdentityCodeValidate) {

    var hasErrors = false;

    function validateEssentialFormObject($formControl) {
        var $formGroup = $formControl.closest('.form-group').removeClass('has-error');
        if ($formGroup.hasClass('essential') && !$formControl.val()) {
            $formGroup.addClass('has-error');
            hasErrors = true;
        }
    }

    function getParamsFromFormObject($form) {
        var params = {};

        $form.find('[name]').each(function() {
            var name = $(this).attr('name'),
                value = $(this).val();

            params[name] = value;
            validateEssentialFormObject($(this));
        });

        return params;
    }

    function appendHtml($node, tpl, tplData) {
        var html = template(tpl, tplData);
        $node.append(html);
    }

    function setHtml($node, tpl, tplData) {
        var html = template(tpl, tplData);
        $node.html(html);
    }

    function ActiveForm() {
        this.$form = $('#activeForm');

        this.$deviceSectionForm = this.$form.children('[data-section=device]');
        this.$infoSectionForm = this.$form.children('[data-section=info]');

        this.infoType = this.$infoSectionForm.find('[name=user_type]:checked').val();

        this.$deviceGroups = $('#deviceGroups');

        this._init();
    }
    ActiveForm.prototype = {

        _init: function() {
            this.$infoSectionForm.find('.geo').each(function() {
                new Geo($(this));
            });

            this._initDeviceGroup();
            this._initInfoTypeForm();

            this._bindEvent();
        },

        _initDeviceGroup: function() {
            var _this = this;

            var MAX_DEVICE_GROUP_COUNT = 5;
            var count = 1,
                increaseCount = 1;

            appendHtml(this.$deviceGroups, 'tpl_deviceGroup', {
                allowRemove: false,
                localId: 'local_' + increaseCount++
            });

            $('#addDevice').on('click', function() {
                if(count >= MAX_DEVICE_GROUP_COUNT) {
                    alert('激活信息不能超过5台');
                    return;
                }

                count++;
                appendHtml(_this.$deviceGroups, 'tpl_deviceGroup', {
                    allowRemove: true,
                    localId: 'local_' + increaseCount++
                });
            });

            this.$deviceGroups.on('click', '[action-type=remove]', function() {
                count--;
                $(this).closest('.device-group').remove();
            });
        },

        _bindEvent: function() {
            this._bindSubmitInfoFormEvent();
            this._bindUserTypeEvent();
        },

        _getParamsFromDeviceSectionForm: function() {
            var arr = [];

            this.$deviceSectionForm.find('.device-group').each(function() {
                var $group = $(this);
                arr.push(getParamsFromFormObject($group));
            });

            return { info : JSON.stringify(arr) };
        },

        _getParamsFromInfoSectionForm: function() {
            var params = getParamsFromFormObject(this.$infoSectionForm.children('[data-type=' + this.infoType + ']'));

            if (this.infoType == 'company') {
                params['telephone'] = params['telephone_1'] + '-' + params['telephone_2'];
            }

            params['user_type'] = this.infoType;
            params['account'] = this.$infoSectionForm.find('[name=account]').val();
            params['is_mall'] = 0;

            validateEssentialFormObject(this.$infoSectionForm.find('[name=account]'));

            return params;
        },

        _validate: function(params) {
            var $idcardInput = this.$form.find('[name=idcard]'),
                $idcardInputGroup = $idcardInput.closest('.form-group').removeClass('has-error');

            if (window.CURRENT_COUNTRY == 'cn') {
                var msg = IdentityCodeValidate(params['idcard']);
                if (msg != '') {
                    $idcardInputGroup.addClass('has-error');
                    this._showErrorMsg(msg);
                    return false;
                }
            }
            return true;
        },

        _bindSubmitInfoFormEvent: function() {
            var _this = this;

            _this.$form.on('submit', function() {
                hasErrors = false;

                var deviceSectionFormParams = _this._getParamsFromDeviceSectionForm.call(_this);
                var infoSectionFormParams = _this._getParamsFromInfoSectionForm.call(_this);
                var params = $.extend({}, deviceSectionFormParams, infoSectionFormParams);

                if (hasErrors) {
                    return false;
                }

                if (!_this._validate.call(_this, params)) {
                    return false;
                }

                HttpHelper.post('/adminagent/active', params, {
                    success: function(resp) {
                        var status = parseInt(resp['status']);

                        _this._handleDeviceSectionFormError.call(_this, resp['error_data']);

                        switch (status) {
                            case 200:
                                _this._showActiveSuccessView(resp['data'], params['realname']);
                                break;
                            case 1007:
                                if (confirm(resp['extra']['msg'])) {
                                    location.href = '/adminagent/login';
                                }
                                break;
                            default:
                                _this._showErrorMsg.call(_this, resp['extra']['msg']);
                        }
                    },

                    error: function() {
                        _this._showErrorMsg.call(_this, '服务器异常');
                    }
                });

                return false;
            });
        },

        _handleDeviceSectionFormError: function(errorData) {
            this.$deviceGroups.find('.device-group').each(function() {
                var $checkedInputs = $(this).find('[input-check]').removeClass('has-error');
                var $formResultWrap = $(this).find('.form-result-wrap').hide(),
                    $formResult = $formResultWrap.find('.form-result').text('');

                var localId = $(this).data('id');
                var ed = errorData && errorData[localId];
                if (!!ed) {
                    $checkedInputs.addClass('has-error');
                    $formResult.text(ed['error']);
                    $formResultWrap.show();
                }
            });
        },

        _showErrorMsg: function(msg) {
            var _this = this;

            if (!_this.$formResult) {
                _this.$formResult = $('#formResult');
            }

            if (_this.timeout) {
                clearTimeout(_this.timeout);
            }

            _this.$formResult.text(msg).fadeIn('normal');
            _this.timeout = setTimeout(function() {
                _this.$formResult.fadeOut('fast', function() {
                    $(this).text('');
                });
            }, 3000);
        },

        _showActiveSuccessView: function(data, username) {
            var $mainBody = $('#mainBody');

            setHtml($mainBody, 'tpl_activeSuccess', {
                list: data,
                username: username
            });

            requirejs(['zeroClipboard'], function(ZeroClipboard) {
                var $copies = $mainBody.find('.copy');
                var clip = new ZeroClipboard($copies);

                // Initialized Copy Buttons
                $copies.popover({
                    container: 'body',
                    trigger: 'focus',
                    content: window.LANGUAGE_DATA['iuav_copy_success']
                });

                clip.on('ready', function() {
                    this.on('aftercopy', function(e) {
                        $(e.target).popover('show');
                    });
                });

                clip.on('error', function() {
                    $mainBody.find('.copy').remove();

                    ZeroClipboard.destroy();
                });
            });
        },

        _initInfoTypeForm: function() {
            this.$form
                .find('[data-type=' + this.infoType + ']').show()
                .siblings('[data-type]').hide();
        },

        _bindUserTypeEvent: function() {
            var _this = this;

            _this.$form.find('input[name=user_type]').on('change', function() {
                _this.infoType = $(this).val();
                _this._initInfoTypeForm.call(_this);
            });
        }

    };

    return ActiveForm;
}));