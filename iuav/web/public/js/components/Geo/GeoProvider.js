(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(['httpHelper', 'geoData'], factory);
    } else {
        window.GeoProvider = factory(HttpHelper, geoData);
    }

}(function(HttpHelper, geoData) {

    var provinces = geoData['provinces'];
    var provinceIdToCities = geoData['provinceIdToCities'],
        cityIdToDistricts = geoData['cityIdToDistricts'];

    var districtIdToStreets = {};

    var reqQueues = []; // [{ districtId: callback }]
    var activeReq;

    var GeoProvider = {};
    GeoProvider.getProvinces = function() {
        return provinces;
    };
    GeoProvider.getCitiesByProvinceId = function(provinceId) {
        return provinceIdToCities[provinceId];
    };
    GeoProvider.getDistrictsByCityId = function(cityId) {
        return cityIdToDistricts[cityId];
    };
    GeoProvider.getStreetsByDistrictId = function(districtId, callback) {
        reqQueues.push({
            id: districtId,
            callback: callback
        });

        if (!activeReq) {
            (function loop() {
                activeReq = reqQueues.shift() || null;

                if (activeReq != null) {
                    getStreets(activeReq.id, function(data) {
                        activeReq.callback && activeReq.callback(data);
                        loop();
                    });
                }
            })();
        }
    };

    function getStreets(id, callback) {
        if (!!districtIdToStreets[activeReq.id]) {
            callback && callback($.extend([], districtIdToStreets[activeReq.id]));
            return ;
        }

        HttpHelper.post('/apiadminagent/getstreet', {
            area_no: id
        }, {
            success: function(resp) {
                var status = resp['status'];
                if (status == 200) {
                    var data = resp['data'];
                    var streetsArr = [];
                    for (var i = 0 ; i < data.length; i++) {
                        streetsArr.push({
                            id: data[i]['street_no'],
                            text: data[i]['name']
                        });
                    }
                    districtIdToStreets[id] = streetsArr;
                }

                callback && callback($.extend([], streetsArr));
            }
        });
    }

    return GeoProvider;
}));
