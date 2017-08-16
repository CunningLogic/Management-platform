(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        window.IdentityCode = factory();
    }

}(function() {

    var city = {
        11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古",
        21: "辽宁", 22: "吉林", 23: "黑龙江",
        31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东",
        41: "河南", 42: "湖北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南",
        50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏",
        61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆",
        71: "台湾",
        81: "香港", 82: "澳门", 91:"国外" };


    var validateFormat = function(code) {
        return code && /^\d{6}(18|19|20)?\d{2}(0[1-9]|1[0-2])(0[1-9]|[1-2]\d|3[0-1])\d{3}(\d|X)$/i.test(code);
    };

    var validateAddressCode = function(code) {
        return city[code.substr(0, 2)];
    };

    var validateLastCode = function(code) {
        if (code.length == 18) {
            code = code.split('');

            //∑(ai×Wi)(mod 11)
            var factor = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2], // 加权因子
                parity = [ 1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2 ]; // 校验位

            var sum = 0, ai = 0, wi = 0;

            for (var i = 0; i < 17; i++) {
                ai = code[i];
                wi = factor[i];
                sum += ai * wi;
            }
            return parity[sum % 11] == code[17];
        }

        return false;
    };

    var validate = function(code) {
        if (!validateFormat(code)) {
            return '身份证号码格式错误';
        }

        if (!validateAddressCode(code)) {
            return '身份证号码地址编码错误';
        }

        if (!validateLastCode(code)) {
            return '身份证号码校验位错误';
        }

        return '';
    };

    return validate;
}));