(function(factory) {

    if (typeof define == 'function' && define.amd) {
        define(['jquery', 'geoProvider'], factory);
    } else {
        window.Geo = factory(jQuery, GeoProvider);
    }

}(function($, GeoProvider) {

    var UNKNOWN_STREET = {
        text: '暂不填写'
    };

    function updateSelect(data, $select, showEmpty) {
        $select.html('');

        if (showEmpty) {
            var $option = $('<option></option>');
            $option.val('').text('');
            $select.append($option);
        }

        if (data) {
            for (var i = 0; i < data.length; i++) {
                var d = data[i];
                var $option = $('<option></option>');

                if (!!d.id) {
                    $option.data('id', d.id);
                }
                $option.val(!!d.id ? d.text : '0').text(d.text);

                $select.append($option);
            }

            $select.trigger('change');
        }
    }

    function changeSelection(val, $select) {
        if (!!val) {
            $select.val(val).trigger('change');
        }
    }

    function Geo($geo, showEmpty) {
        this.$provinceSelect = $geo.find('[geo-type=province]');
        this.$citySelect = $geo.find('[geo-type=city]');
        this.$districtSelect = $geo.find('[geo-type=district]');
        this.$streetSelect = $geo.find('[geo-type=street]');

        this.showEmpty = showEmpty;

        this._init();
    }

    Geo.prototype = {
        setData: function(data) {
            var province = data['province'],
                city = data['city'],
                district = data['district'],
                street = data['street'];

            changeSelection(province, this.$provinceSelect);
            changeSelection(city, this.$citySelect);
            changeSelection(district, this.$districtSelect);
            changeSelection(street, this.$streetSelect);
        },

        _init: function() {
            this._bindEvent();
            updateSelect(GeoProvider.getProvinces(), this.$provinceSelect, this.showEmpty);
        },

        _bindEvent: function() {
            this._bindProvinceSelectEvent();
            this._bindCitySelectEvent();
            this._bindDistrictSelectEvent();
        },

        _bindProvinceSelectEvent: function() {
            var _this = this;

            this.$provinceSelect.on('change', function() {
                var cities = GeoProvider.getCitiesByProvinceId($(this).children('option:selected').data('id'));
                updateSelect(cities, _this.$citySelect, _this.showEmpty);
            });
        },

        _bindCitySelectEvent: function() {
            var _this = this;

            this.$citySelect.on('change', function() {
                var districts = GeoProvider.getDistrictsByCityId($(this).children('option:selected').data('id'));
                updateSelect(districts, _this.$districtSelect);
            });
        },

        _bindDistrictSelectEvent: function() {
            var _this = this;

            this.$districtSelect.on('change', function() {
                var districtId = $(this).children('option:selected').data('id');

                GeoProvider.getStreetsByDistrictId(districtId, function(streets) {
                    streets.push(UNKNOWN_STREET);
                    updateSelect(streets, _this.$streetSelect);
                });
            });
        }
    };

    return Geo;
}));
