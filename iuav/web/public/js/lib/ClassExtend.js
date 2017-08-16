(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(factory);
    } else {
        window.ClassExtend = factory();
    }

}(function() {

    /**
     *
     * Implement Class Extend
     * @Usage:
     *   var Parent = jClass.extend({ ... });
     *   var Child = Parent.extend({ ... });
     *
     */

    var jClass;

    (function() {
        var initializing = false;

        jClass = function() { };
        jClass.extend = function(prop) {
            var baseClass = null;
            if (this !== jClass) {
                baseClass = this;
            }

            function F() {
                if (!initializing) {
                    if (baseClass) {
                        this._superprototype = baseClass.prototype;
                    }
                    this.init.apply(this, arguments);
                }
            }

            if (baseClass) {
                initializing = true;
                F.prototype = new baseClass();
                F.prototype.constructor = F;
                initializing = false;
            }
            F.extend = arguments.callee;

            for (var name in prop) {
                if (prop.hasOwnProperty(name)) {

                    if (baseClass &&
                        typeof (prop[name]) === "function" &&
                        typeof (F.prototype[name]) === "function" &&
                        /\b_super\b/.test(prop[name])) {

                        F.prototype[name] = (function(name, fn) {
                            return function() {
                                this._super = baseClass.prototype[name];
                                return fn.apply(this, arguments);
                            };
                        })(name, prop[name]);

                    } else {
                        F.prototype[name] = prop[name];
                    }
                }
            }
            return F;
        };

    })();

    return jClass;
}));