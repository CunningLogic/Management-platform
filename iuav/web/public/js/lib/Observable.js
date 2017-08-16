(function(factory) {

    if (typeof define === 'function' && define.amd) {
        define(['observer', '$class'], factory);
    } else {
        window.Observable = factory(Observer, ClassExtend);
    }

}(function(Observer, $class) {

    var Observable = $class.extend({
        init: function() {
            this.observersList = [];
            this.changed = false;
        },


        addObserver: function(observer) {
            if (observer instanceof Observer) {
                this.observersList.push(observer);
            }
        },

        deleteObserver: function(observer) {

            for (var i = 0; i < this.observersList.length; i++) {
                var obs = this.observersList[i];

                if (obs == observer) {
                    this.observersList.slice(i, 1);
                    break;
                }
            }

        },

        clearChanged: function() {
            this.changed = false;
        },

        setChanged: function() {
            this.changed = true;
        },

        notifyObservers: function(args) {
            if (!this.changed) {
                return;
            }

            this.clearChanged();
            for (var i = 0; i < this.observersList.length; i++) {
                var obs = this.observersList[i];
                obs.update(this, args);
            }
        }
    });

    return Observable;
}));