Sabel.PHP.AjaxPager = function(replaceId, pagerClass) {
	this.replaceId     = Sabel.get(replaceId, false);
	this.pagerSelector = "." + (pagerClass || "sbl_pager") + " a";

	this.ef = new Sabel.PHP.EffectUpdater(this.replaceId, "Slide", {
		callback: Sabel.Function.bind(this.init, this)
	});

	this.init();
	this.history = new Sabel.History(Sabel.Function.bind(this.callback, this));
};

Sabel.PHP.AjaxPager.prototype = {
	init: function() {
		var self = this;
		Sabel.find(this.pagerSelector).observe("click", function(evt) {
			try {
				if (this.pathname.lastIndexOf(this.search) > -1) {
					var path = "/" + this.pathname.replace(/^\//, "");
				} else {
					var path = "/" + this.pathname.replace(/^\//, "") + this.search;
				}
				self.history.load(path);
			}catch(e) {}
			Sabel.Event.preventDefault(evt);
		});
	},

	callback: function(uri) {
		this.ef.fire(uri);
	}
};

