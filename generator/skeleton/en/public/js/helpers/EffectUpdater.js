Sabel.PHP.EffectUpdater = function(target, effectName, options) {
  this.options = options || {};
  this.target = target;

  this.ef = new Sabel.Effect({
    duration: 300,
    callback: Sabel.Function.bind(this.update, this)
  }).add(new Sabel.Effect[effectName](target, true));

  this.ajaxCallback = Sabel.Function.bind(this.show, this);
};

Sabel.PHP.EffectUpdater.prototype = {
  fire: function(url) {
    this.url = url;
    this.ef.reverse();
  },

  show: function() {
    if (this.options.callback) this.options.callback();
    this.ef.play();
  },

  update: function(isReverse) {
    if (isReverse === false) return;

    new Sabel.Ajax().updater(this.target, this.url, {
      onComplete: this.ajaxCallback
    });
  }
};
