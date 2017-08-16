(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        window.Observer = factory();
    }

}(function() {

    // Interface
    function Observer(opts) {
        this.opts = opts || {};

        this.init();
    }
    Observer.prototype = {
        init: function() {
            if (!this.opts.update) {
                throw new Error('Observer.update should be implemented.');
            } else {
                this.update = this.opts.update;
            }
        }
    }

    return Observer;

}));