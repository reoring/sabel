/**
 * SabelJS 1.2
 * Header
 *
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @copyright  2004-2008 Hamanaka Kazuhiro <Hamanaka.kazuhiro@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

window.Sabel = {};

Sabel.PHP = {};

Sabel.emptyFunc = function() {};

Sabel.QueryObject = function(object) {
	this.data = object;
};

Sabel.QueryObject.prototype = {
	has: function(key) {
		return !!(this.data[key] !== undefined);
	},

	get: function(key) {
		return this.data[key] || null;
	},

	set: function(key, val) {
		this.data[key] = val;
		return this;
	},

	unset: function(key) {
		delete this.data[key];
	},

	serialize: function() {
		var data = this.data, buf = new Array();
		for (var key in data) {
			if (Sabel.Object.isArray(data[key])) {
				Sabel.Array.each(data[key], function(val) {
					buf[buf.length] = key + "=" + encodeURIComponent(val);
				});
			} else {
				buf[buf.length] = key + "=" + encodeURIComponent(data[key]);
			}
		}

		return buf.join("&");
	}
};


Sabel.Uri = function(uri)
{
	uri = uri || location.href;

	var result = Sabel.Uri.pattern.exec(uri);

	if (result === null) {
		var urlPrefix = location.protocol + "//" + location.hostname;

		if (uri[0] === "/") {
			uri = urlPrefix + uri;
		} else {
			var currentPath = location.pathname.substr(0, location.pathname.lastIndexOf("/")+1);
			uri = urlPrefix + currentPath + uri;
		}
		var result = Sabel.Uri.pattern.exec(uri);
	}

	for (var i = 0, len = result.length; i < len; i++) {
		this[Sabel.Uri.keyNames[i]] = result[i] || "";
	}
	this['parseQuery'] = Sabel.Uri.parseQuery(this.query);
};

Sabel.Uri.pattern  = /^((\w+):\/\/(?:(\w+)(?::(\w+))?@)?([^:\/]*)(?::(\d+))?)(?:([^?#]+?)(?:\/(\w+\.\w+))?)?(?:\?((?:[^&#]+)(?:&[^&#]*)*))?(?:#([^#]+))?$/;
Sabel.Uri.keyNames = ['uri', 'url', 'protocol', 'user', 'password', 'domain', 'port', 'path', 'filename', 'query', 'hash'];

Sabel.Uri.parseQuery = function(query)
{
	if (query === undefined) return {};
	var queries = query.split("&"), parsed = {};

	for (var i = 0, len = queries.length; i < len; i++) {
		if (queries[i] == "") continue;
		var q = queries[i].split("=");
		parsed[q[0]] = q[1] || "";
	}

	return new Sabel.QueryObject(parsed);
};

Sabel.Uri.prototype = {
	has: function(key) {
		return this.parseQuery.has(key);
	},

	get: function(key) {
		return this.parseQuery.get(key);
	},

	set: function(key, value)	{
		this.parseQuery.set(key, value);
		return this;
	},

	unset: function(key) {
		this.parseQuery.unset(key);
		return this;
	},

	getQueryObj: function() {
		return this.parseQuery;
	},

	toString: function() {
		var uri = this.url + this.path;

		if (this.filename !== "") uri += "/" + this.filename;
		if ((query = this.parseQuery.serialize())) uri += "?" + query;
		return uri;
	}
};

Sabel.Environment = (function() {
	var scripts = document.getElementsByTagName("script");
	var uri = scripts[scripts.length - 1].src;

	this._env = parseInt(Sabel.Uri.parseQuery(uri.substring(uri.indexOf("?") + 1)));

	return this;
})();
Sabel.Environment.PRODUCTION  = 10;
Sabel.Environment.TEST        = 5;
Sabel.Environment.DEVELOPMENT = 1;

Sabel.Environment.isDevelopment = function() {
	return this._env === Sabel.Environment.DEVELOPMENT;
};

Sabel.Environment.isTest = function() {
	return this._env === Sabel.Environment.TEST;
};

Sabel.Environment.isProduction = function() {
	return this._env === Sabel.Environment.PRODUCTION;
};

Sabel.Window = {
	getWidth: function() {
		if (document.compatMode === "BackCompat" || (Sabel.UserAgent.isOpera && Sabel.UserAgent.version < 9.5)) {
			return document.body.clientWidth;
		} else if (Sabel.UserAgent.isSafari) {
			return window.innerWidth;
		} else {
			return document.documentElement.clientWidth;
		}
	},

	getHeight: function() {
		if (document.compatMode === "BackCompat" || (Sabel.UserAgent.isOpera && Sabel.UserAgent.version < 9.5)) {
			return document.body.clientHeight;
		} else if (Sabel.UserAgent.isSafari) {
			return window.innerHeight;
		} else {
			return document.documentElement.clientHeight;
		}
	},

	getScrollWidth: function() {
		if (document.compatMode === "CSS1Compat") {
			var width = document.documentElement.scrollWidth;
		} else {
			var width = document.body.scrollWidth;
		}
		var clientWidth = Sabel.Window.getWidth();
		return (clientWidth > width) ? clientWidth : width;
	},

	getScrollHeight: function() {
		if (document.compatMode === "CSS1Compat") {
			var height = document.documentElement.scrollHeight;
		} else {
			var height = document.body.scrollHeight;
		}
		var clientHeight = Sabel.Window.getHeight();
		return (clientHeight > height) ? clientHeight : height;
	},

	getScrollLeft: function() {
		if (document.compatMode === "CSS1Compat") {
			return document.documentElement.scrollLeft;
		} else {
			return document.body.scrollLeft;
		}
	},

	getScrollTop: function() {
		if (document.compatMode === "CSS1Compat") {
			return document.documentElement.scrollTop;
		} else {
			return document.body.scrollTop;
		}
	}
};

Sabel.UserAgent = new function() {
	var ua = navigator.userAgent, w = window;
	this.ua = ua;

	this.isIE = false;
	this.isFirefox = false;
	this.isSafari  = false;
	this.isOpera   = false;
	this.isChrome  = false;
	this.isMozilla = false;

	if (w.ActiveXObject) { // ActiveXObjectが存在すればIE
		this.isIE = true;
	} else if (w.opera) {	// window.operaが存在すればOpera
		this.isOpera = true;
		this.version = opera.version();
	} else if (w.execScript) { // execScriptが存在すればChrome (IEも存在するが既にチェック済)
		this.isChrome = true;
	} else if (w.getMatchedCSSRules) { // getMatchedCSSRulesが存在すればSafari3 (Chromeも存在するが既にチェック済)
		//} else if (w.defaultstatus) { // Safari2対応ならこっち
		this.isSafari = true;
	} else if (w.Components) { // Componentsが存在すればGecko
		this.isFirefox = true;
	} else {
		this.isMozilla = /Mozilla/.test(ua);
	}
	if (this.version === undefined) {
		var matches = /(MSIE |Firefox\/|Version\/|Chrome\/)([0-9.]+)/.exec(ua);
		this.version = matches ? matches[2] : "";
	}

	this.isWindows = /Win/.test(ua);
	this.isMac     = /Mac/.test(ua);
	this.isLinux   = /Linux/.test(ua);
	this.isBSD     = /BSD/.test(ua);
	this.isIPhone  = /iPhone/.test(ua);
};

Sabel.Window.lineFeedCode = (Sabel.UserAgent.isIE) ? "\r" : "\n";


Sabel.Object = {
	_cache: new Array(),

	create: function(object, parent) {
		if (typeof object === "undefined") return {};

		object = Object(object);

		switch (typeof object) {
		case "function":
			return object;
		case "object":
			var func = function() {};
			func.prototype = object;
			if (parent) Sabel.Object.extend(func.prototype, parent, true);
			if (!func.prototype.isAtomic) Sabel.Object.extend(func.prototype, this.Methods, true);

			var obj = new func;
			if (obj.isAtomic()) {
				obj.toString = function() { return object.toString.apply(object, arguments); };
				obj.valueOf  = function() { return object.valueOf.apply(object, arguments); };
			}
		}

		return obj;
	},

	extend: function(child, parent, curry, override) {
		for (var prop in parent) {
			if (typeof child[prop] !== "undefined" && override !== true) continue;
			if (typeof parent[prop] !== "function") {
				child[prop] = parent[prop];
			} else {
				child[prop] = (curry === true) ? Sabel.Object._tmp(parent[prop]) : parent[prop];
			}
		}

		return child;
	},

	_tmp: function(method) {
		return this._cache[method] = this._cache[method] || function() {
			var args = new Array(this);
			args.push.apply(args, arguments);
			return method.apply(method, args);
		}
	}
};


Sabel.Object.Methods = {
	isAtomic: function(object) {
		switch (object.constructor) {
		case String:
		case Number:
		case Boolean:
			return true;
		default:
			return false;
		}
	},

	isString: function(object) {
		return object.constructor === String;
	},

	isNumber: function(object) {
		return object.constructor === Number;
	},

	isBoolean: function(object) {
		return object.constructor === Boolean;
	},

	isArray: function(object) {
		return object.constructor === Array;
	},

	isFunction: function(object) {
		return object.constructor === Function;
	},

	clone: function(object) {
		return Sabel.Object.create(object);
	},

	getName: function(object) {
		return object.constructor;
	},

	hasMethod: function(object, method) {
		return (object[method] !== undefined);
	}
};

Sabel.Object.extend(Sabel.Object, Sabel.Object.Methods);
Sabel.Class = function() {
	if (typeof arguments[0] === "function") {
		var superKlass = arguments[0];
	} else {
		var superKlass = function() {};
	}
	var methods = Array.prototype.pop.call(arguments) || Sabel.Object;

	var tmpKlass = function() {};
	tmpKlass.prototype = superKlass.prototype;

	var subKlass = function() {
		this.__super__ = superKlass;
		if (typeof methods.init === "function") {
			methods.init.apply(this, arguments);
		} else {
			this.__super__.apply(this, arguments);
		}
		delete this.__super__;
	}

	subKlass.prototype = new tmpKlass;
	switch (subKlass.prototype.constructor) {
	case String: case Number: case Boolean:
		subKlass.prototype.toString = function() {
			return superKlass.toString.apply(superKlass, arguments);
		};
		subKlass.prototype.valueOf  = function() {
			return superKlass.valueOf.apply(superKlass, arguments);
		};
	}

	if (methods) {
		for (var name in methods) subKlass.prototype[name] = methods[name];

		var ms = ["toString", "valueOf"];
		for (var i = 0, len = ms.length; i < len; i++) {
			if (methods.hasOwnProperty(ms[i]))
				subKlass.prototype[ms[i]] = methods[ms[i]];
		}

		subKlass.prototype.constructor = subKlass;
	}
	return subKlass;
};

Sabel.String = new Sabel.Class(String, {
	init: function(string) {
		this._string = string;
	},

	toString: function() {
		return this._string;
	},

	valueOf: function() {
		return this._string;
	},

	_set: function(string) {
		this._string = string;
		return this;
	},

	chr: function() {
		return this._set(String.fromCharCode.apply(String, this._string.split(',')));
	},

	explode: function(delimiter) {
		return this._string.split(delimiter);
	},

	htmlspecialchars: function(quote_style) {
		var string = this._string.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

		switch (quote_style) {
		case 3: case "ENT_QUOTES":
			string = string.replace(/'/g, "&#039;");
		case 2: case "ENT_COMPAT":
			string = string.replace(/"/g, "&quot;");
		case 0: case "ENT_NOQUOTES":
		}
		return this._set(string);
	},

	lcfirst: function() {
		var str = this._string;
		return this._set(str.charAt(0).toLowerCase() + str.substring(1));
	},

	ltrim: function() {
		return this._set(this._string.replace(/^\s+/, ""));
	},

	nl2br: function() {
		return this._set(this._string.replace(/(\r?\n)/g, "<br/>$1"));
	},

	ord: function() {
		return this._set(this._string.charCodeAt(0));
	},

	rtrim: function() {
		return this._set(this._string.replace(/\s+$/, ""));
	},

	repeat: function(multiplier) {
		var tmp = "";
		for (var i = 0; i < multiplier; i++) {
			tmp += this._string;
		}
		return this._set(tmp);
	},

	shuffle: function() {
		var tmp = this._string.split("");
		var i = tmp.length;

		while (i) {
			var j = Math.floor(Math.random() * i);
			var t = tmp[--i];
			tmp[i] = tmp[j];
			tmp[j] = t;
		}
		return tmp.join("");
	},

	sprintf: function(/* mixed args */) {
		var args = arguments;

		var i = 0, v, o;

		var pattern = /%(?:([0-9]+)\$)?(-)?([0]|\'.)?([0-9]*)(?:\.([0-9]+))?([bcdfFosxX])/g;
		var replaced = this.replace(pattern, function(all, key, sign, padding, alignment, precision, match) {
			v = (key) ? args[--key] : args[i++];

			if (precision) precision = parseInt(precision);
			switch (match) {
			case "b":
				v = v.toString(2);
				break;
			case "c":
				v = String.fromCharCode(v);
				break;
			case "f": case "F":
				if (precision) v = parseFloat(v).toFixed(precision);
				break;
			case "o":
				v = v.toString(8);
				break;
			case "s":
				v = v.substring(0, precision || v.length);
				break;
			case "x":
				v = v.toString(16);
				break;
			case "X":
				v = v.toString(16).toUpperCase();
				break;
			}

			if (alignment) {
				var len = alignment - v.toString().length;
				padding = (padding) ? padding.charAt(padding.length - 1) : " ";
				var t = new Sabel.String(padding).repeat(len);

				v = (sign === "-") ? v + t : t + v;
			}

			return v;
		});

		return replaced;
	},

	str_pad: function(pad_length, pad_string /* = " " */, pad_type /* = 1 */) {
		var string = this._string
		var repeat = (pad_length - string.length);
		var left = right = 0;
		pad_string = new Sabel.String(pad_string || " ");

		switch (pad_type) {
			case 0:
			left = repeat;
			break;
			case 2:
			left = Math.floor(repeat / 2);
			right = Math.ceil(repeat / 2);
			break;
			case 1:
			default:
			right = repeat;
			break;
		}

		if (left > 0) {
			string = pad_string.repeat(left).substr(0, left) + string;
		}

		if (right > 0) {
			string += pad_string.repeat(right).substr(0, right);
		}

		return this._set(string);
	},

	trim: function() {
		var str = this._string;
		return this._set(str.replace(/(^\s+|\s+$)/g, ""));
	},

	format: function(obj) {
		var replaceFunc = function(target, key) {
			return (obj[key] !== undefined) ? obj[key] : "";
		};
		return this._string.replace(/%(\w+)%/g, replaceFunc).replace(/#\{(\w+)\}/g, replaceFunc);
	},

	ucfirst: function() {
		var str = this._string;
		return this._set(str.charAt(0).toUpperCase() + str.substring(1));
	},


	capitalize: function() {
		this._set(this._string.toLowerCase());
		return this.ucfirst();
	},

	camelize: function() {
		var str = this._string;
		return this._set(str.replace(/-([a-z])/g, function(dummy, match) {
			return match.toUpperCase();
		}));
	},

	decamelize: function() {
		return this._set(this._string.replace(/\w[A-Z]/g, function(match) {
			return match.charAt(0) + "-" + match.charAt(1).toLowerCase();
		}));
	},

	truncate: function(length, truncation) {
		truncation = truncation || "";

		return this._set(this._string.substring(0, length) + truncation);
	},

	clean: function() {
		return this._set(this._string.replace(/\s{2,}/g, " "));
	},

	toInt: function() {
		return parseInt(this._string, 10);
	},

	toFloat: function() {
		return parseFloat(this._string);
	}
});

Sabel.String.prototype.chop = Sabel.String.prototype.rtrim;
Sabel.String.prototype.times = Sabel.String.prototype.repeat;

var methods = [
	"anchor", "big", "blink", "bold", "charAt", "charCodeAt", "concat",
	"decodeURI", "decodeURI_Component", "encodeURI", "encodeURI_Component",
	"enumerate", "escape", "fixed", "fontcolor", "fontsize", "fromCharCode",
	"getProperty", "indexOf", "italics", "lastIndexOf", "link", "localeCompare",
	"match", "replace", "resolve", "search", "slice", "small", "split", "strike",
	"sub", "substr", "substring", "sup", "toLocaleLowerCase", "toLocaleUpperCase",
	"toLowerCase", "toSource", "toUpperCase", "unescape", "uneval"
];
for (var i = 0, len = methods.length; i < len; i++) {
	var method = methods[i];
	Sabel.String.prototype[method] = (function(method) {
		return function() {
			return this._set(method.apply(this, arguments));
		}
	})(String.prototype[method]);
};

Sabel.Number = function(number) {
	return Sabel.Object.create(number, Sabel.Number)
};

Sabel.Number._units = ["", "k", "M", "G", "T", "P", "E", "Z", "Y"];
Sabel.Number.toHumanReadable = function(number, unit, ext) {
	if (typeof number !== "number") throw "number is not Number object.";
	var i = 0;

	while (number > unit) {
		i++;
		number = number / unit;
	}

	return number.toFixed(1) + Sabel.Number._units[i];
};

//Sabel.Number.numberFormat

Sabel.Number.between = function(number, min, max) {
	return number >= min && number <= max;
};

Sabel.Array = function(iterable) {
	if (typeof iterable === "undefined") {
		iterable = new Array();
	} else if (iterable.constructor === String) {
		iterable = new Array(iterable);
	} else if (iterable.toArray) {
		iterable = iterable.toArray();
	} else {
		var buf = new Array();
		Sabel.Array.each(iterable, function(v) { buf[buf.length] = v; });
		iterable = buf;
	}

	return Sabel.Object.extend(iterable, Sabel.Array, true);
};

Sabel.Array.each = function(array, callback) {
	for (var i = 0, len = array.length; i < len; i++) {
		var r = callback(array[i], i);
		if (r === "BREAK") break;
	}
	return array;
};

Sabel.Array.map = function(array, callback) {
	var results = new Array();
	for (var i = 0, len = array.length; i < len; i++) {
		results[i] = callback(array[i]);
	}
	return results;
};

Sabel.Array.concat = function(array, iterable) {
	if (iterable.length === undefined) return array;

	if (iterable.toArray) iterable = iterable.toArray();

	Sabel.Array.each(iterable, function(data) {
		array[array.length] = data;
	});
	return array;
};

Sabel.Array.inject = function(array, method) {
	var buf = new Array();
	Sabel.Array.each(array, function(data) {
		if (method(data) === true) buf[buf.length] = data;
	});
	return buf;
};


Sabel.Array.callmap = function(/*array, method, args */) {
	var args   = Sabel.Array(arguments);
	var array  = args.shift(), method = args.shift();
	for (var i = 0, len = array. length; i < len; i++) {
		array[i][method].apply(array[i], args);
	}
	return array;
};

Sabel.Array.include = function(array, value) {
	for (var i = 0, len = array.length; i < len; i++) {
		if (array[i] === value) return true;
	}
	return false;
};

Sabel.Array.sum = function(array) {
	var result = 0;
	for (var i = 0, len = array.length; i < len; i++) {
		result += array[i];
	}
	return result;
};

Sabel.Array.unique = function(array) {
	var result = new Array();
	Sabel.Array.each(array, function(val) {
		if (result.indexOf(val) === -1) {
			result.push(val);
		}
	});
	return result;
};

Sabel.Object.extend(Sabel.Array, Sabel.Object.Methods);
Sabel.Function = function(method) {
	return Sabel.Object.create(method, Sabel.Function);
};

Sabel.Function.bind = function() {
	var args   = Sabel.Array(arguments);
	var method = args.shift(), object = args.shift();

	return function() {
		return method.apply(object, args.concat(Sabel.Array(arguments)));
	}
};

Sabel.Function.bindWithEvent = function() {
	var args   = Sabel.Array(arguments);
	var method = args.shift(), object = args.shift();

	return function(event) {
		return method.apply(object, [event || window.event].concat(args));
	}
};

Sabel.Function.delay = function(method, delay) {
	var args = Sabel.Array(arguments);
	method = args.shift();
	delay  = args.shift() || 1000;
	scope  = args.shift() || method;
	setTimeout(function() { method.apply(scope, args); }, delay);
};

Sabel.Function.curry = function() {
	var args = Sabel.Array(arguments), method = args.shift();

	return function() {
		return method.apply(method, args.concat(Sabel.Array(arguments)));
	}
};

Sabel.Function.restraint = function(method, obj) {
	var methodArgs = Sabel.Function.getArgumentNames(method);
	var arglen     = methodArgs.length;

	var args = new Array(arglen);
	for (var i = 0; i < arglen; i++) {
		args[i] = obj[methodArgs[i]];
	}

	return function() {
		var ary = Sabel.Array(arguments);
		for (var i = 0; i < arglen; i++) {
			if (args[i] === undefined) args[i] = ary.shift();
		}
		method.apply(method, args);
	}
};

Sabel.Function.getArgumentNames = function(method) {
	var str = method.toString();
	argNames = str.match(/^[\s]*function[\s\w]*\((.*)\)/)[1].split(",");
	for (var i = 0, len = argNames.length; i < len; i++) {
		new Sabel.String(argNames[i]).trim();
	}
	return (argNames[0] === "") ? new Array() : argNames;
};

Sabel.Object.extend(Sabel.Function, Sabel.Object.Methods);


Sabel.Dom = {
	getElementById: function(element, extend) {
		if (typeof element === "string") {
			element = document.getElementById(element);
		}

		return (element) ? (extend === false) ? element : Sabel.Element(element) : null;
	},

	getElementsByClassName: function(className, element, ext) {
		element = (element) ? Sabel.get(element, false) : document;

		if (element.getElementsByClassName) {
			return element.getElementsByClassName(className);
		} else {
			var elms = element.getElementsByTagName("*");
			var pat = new RegExp("(?:^|\\s)" + className + "(?:\\s|$)");

			var buf = (ext) ? Sabel.Elements() : new Array();
			Sabel.Array.each(elms, function(elm) {
				if (pat.test(elm.className)) buf.push(elm);
			});
			return buf;
		}
	},

	getElementsBySelector: function(selector, root) {
		var h = Sabel.Dom.Selector.handlers;
		root = root || document;
		var elms = [root], founds = new Array(), prev;

		if (document.querySelectorAll) {
			try {
				Sabel.Array.each(root.querySelectorAll(selector), function(el) {
					founds.push(el);
				});
				return new Sabel.Elements(founds);
			} catch (e) {}
		}

		while (selector && selector !== prev) {
			if (selector.indexOf(',') === 0) {
				founds = founds.concat(elms);
				elms = [document];
				selector = selector.replace(/^,/, "");
			}
			prev = selector;

			ms = Sabel.Dom.Selector.pattern.exec(selector);
			ms[2] = (ms[2] || "*").toUpperCase();
			if (ms[1]) {
				elms = h.combinator(elms, ms[1], ms[2]);
				if (ms[3] /* ID */)
					elms = h.id(elms, ms[3]);
			} else {
				if (ms[3] /* ID */) {
					elms = h.id(elms, ms[3], ms[2]);
				} else if (ms[2] /* TAG */) {
					elms = h.tagName(elms, ms[2]);
				}
			}

			if (ms[4] /* CLASS */)
				elms = h.className(elms, ms[4]);

			if (ms[5] /* ATTR */)
				elms = h.attr(elms, ms[5]);

			if (ms[6] /* PSEUDO */)
				elms = h.pseudo(elms, ms[6]);

			selector = selector.replace(ms[0], "");
		}
		return new Sabel.Elements(founds.concat(elms));
	},

	getElementsByXPath: function(xpath, root) {
		var result = document.evaluate(xpath, root || document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

		if (result.snapshotLength) {
			var buf = new Array(result.snapshotLength), i = 0, tmp;
			while((tmp = result.snapshotItem(i))) buf[i++] = tmp;
			return new Sabel.Elements(buf);
		} else {
			return new Sabel.Elements();
		}
	}
};

Sabel.get   = Sabel.Dom.getElementById;
Sabel.find  = Sabel.Dom.getElementsBySelector;
Sabel.xpath = Sabel.Dom.getElementsByXPath;

Sabel.Dom.Selector = {
	pattern: new RegExp(
		"^\\s*" + // Space
		"([~>+])?\\s*" + // 
		"(\\w+|\\*)?" + // TagName
		"(?:#(\\w+))?" + // ID
		"((?:\\.[a-zA-Z0-9_-]+)+)?" + // ClassName
		"((?:\\[@?\\w+(?:[$^!~*|]?=['\"]?.+['\"]?)?\\])*)?" + // Attribute
		"((?::[\\w-]+(?:\\([^\\s]+\\))?)*)" // 
	),

	handlers: {
		tagName: function(nodes, tagName) {
			var founds = new Array(), elm, i = 0;
			while ((elm = nodes[i++])) {
				Sabel.Dom.Selector.concat(founds, elm.getElementsByTagName(tagName));
			}
			return Sabel.Dom.Selector.clear(founds, "_added");
		},

		id: function(nodes, id, tagName) {
			var founds = new Array(), elm, i = 0;
			id = id.replace("#", "");

			var tmpElm = document.getElementById(id);
			if (tagName === "*" || tmpElm.nodeName === tagName) {
				if (nodes[0] === document) {
					founds.push(tmpElm);
				} else {
					while ((elm = nodes[i++])) {
						if (Sabel.Element.isContain(elm, tmpElm)) {
							founds.push(tmpElm);
							break;
						}
					}
				}
			} else {
				while ((elm = nodes[i++])) {
					if (elm.getAttribute("id") === id) {
						founds[founds.length] = elm;
						break;
					}
				}
			}
			return founds;
		},

		className: function(nodes, className) {
			var founds = new Array(), elm, i = 0;
			var classNames = className.split("."), cr = new Array();
			classNames.shift();

			for (var j = 0, len = classNames.length; j < len; j++)
				cr.push(new RegExp("(?:^|\\s+)" + classNames[j] + "(?:\\s+|$)"));

			var c, cn, flag, k;
			while ((elm = nodes[i++])) {
				k = 0;
				flag = true;

				// @todo use Sabel.Element.getAttr
				c = elm.className;
				if (!c || elm._added === true) continue;

				while ((cn = cr[k++])) {
					if (!cn.test(c)) {
						flag = false;
						break;
					}
				}
				if (flag === true) {
					elm._added = true;
					founds.push(elm);
				}
			}
			return Sabel.Dom.Selector.clear(founds, "_added");
		},

		combinator: function(nodes, combName, tagName) {
			var founds = new Array(), elm, i = 0, buf;
			switch (combName) {
				case "+":
					while ((elm = nodes[i++])) {
						while ((elm = elm.nextSibling) && elm.nodeType !== 1);
						if (elm && (elm.nodeName === tagName || tagName === "*")) {
							founds[founds.length] = elm;
						}
					}
					break;
				case "~":
					while ((elm = nodes[i++])) {
						while ((elm = elm.nextSibling) && elm._added !== true) {
							if (elm.nodeName === tagName || tagName === "*") {
								elm._added = true;
								founds.push(elm);
							}
						}
					}
					Sabel.Dom.Selector.clear(founds, "_added");
					break;
				case ">":
					while ((elm = nodes[i++])) {
						buf = elm.getElementsByTagName(tagName);
						for (var j = 0, c; c = buf[j]; j++) {
							if (c.parentNode === elm) founds.push(c);
						}
					}
					break;
			}
			return founds;
		},

		attr: function(nodes, attr) {
			var founds = new Array(), elm, i = 0;
			var attrPattern = /\[@?(\w+)(?:([$^!~*|]?=)(['"]?)(.+?)\3)?\]/;
			var attrs = attr.match(/\[@?(\w+)(?:([$^!~*|]?=)(['"]?)(.+?)\3)?\]/g), attrRegex = new Array(), at,a;

			for (var j = 0, len = attrs.length; j < len; ++j) {
				buf = attrPattern.exec(attrs[j]);
				if (buf[2] == "|=" && buf[1] !== "lang" && buf[1] !== "hreflang") {
					return founds;
				}
				attrRegex.push(buf);
			}

			checkNode:
			while ((elm = nodes[i++])) {
				if (elm._added) continue;
				var k = 0;

				while ((at = attrRegex[k++])) {
					if (at[1] === 'class' && window.ActiveXObject) at[1] = 'className';
					a = elm.getAttribute(at[1]);

					switch (at[2]) {
						case "=":
							if (a !== at[4]) continue checkNode;
							break;
						case "!=":
							if (a === at[4]) continue checkNode;
							break;
						case "~=":
							if ((" "+a+" ").indexOf(" "+at[4]+" ") === -1) continue checkNode;
							break;
						case "^=":
							if ((a||"").indexOf(at[4]) !== 0) continue checkNode;
							break;
						case "$=":
							if ((a||"").lastIndexOf(at[4]) !== ((a||"").length - at[4].length)) continue checkNode;
							break;
						case "*=":
							if ((a||"").indexOf(at[4]) === -1) continue checkNode;
							break;
						case "|=":
							if ((a+"-").toLowerCase().indexOf((at[4]+"-").toLowerCase()) !== 0)
								continue checkNode;
							break;
						default:
							if (!a) continue checkNode;
							break;
					}
				}
				elm._added = true;
				founds.push(elm);
			}
			return Sabel.Dom.Selector.clear(founds, "_added");
		},

		pseudo: function(nodes, pseudo) {
			var founds = new Array(), elm, i = 0;
			var ps = pseudo.replace("):", ") :").match(/:[\w-]+(\([^\s]+\))?/g);

			var buf;

			var chk = function(nodes, next, check, isNot) {
				var founds = new Array(), i = 0, elm;
				isNot = (isNot) ? true : false;

				while ((elm = nodes[i++])) {
					var buf = elm;
					var checkValue = (check === "nodeName") ? elm.nodeName : 1;
					while ((buf = buf[next]) && buf[check] !== checkValue);
					if ((buf === null) !== isNot) founds.push(elm);
				}
				return founds;
			};

			var chkNth = function(nodes, x, s, init, nextprop, checkNodeName) {
				s = parseInt(s);
				var founds = new Array(), i = 0, elm;
				var searched = new Array();
				while ((elm = nodes[i++])) {
					var parent = elm.parentNode;
					if (parent._searched !== true) {
						var next = s;
						var cn = parent[init], counter = 0;
						while (cn) {
							if (checkNodeName === true) {
								if (cn.nodeName === elm.nodeName) {
									if (++counter === next) {
										founds.push(elm);
										next += x;
									}
								}
							} else {
								if (cn.nodeType === 1) {
									if (++counter === next) {
										if (cn.nodeName === elm.nodeName) {
											founds.push(elm);
										}
										next += x;
									}
								}
							}
							cn = cn[nextprop];
						}
						parent._searched = true;
						searched.push(parent);
					}
				}
				Sabel.Dom.Selector.clear(searched, "_searched");
				return founds;
			};

			var getSeq = function(expression) {
				var nc = /(?:(odd|even)|((?:[1-9]\d*)?n)([+-]\d+)?)/.exec(expression)
				if (nc[1]) {
					if (nc[1] === "odd") var x = 2, s = 1;
					else var x = 2, s = 2;
				} else if (nc[2]) {
					var x = parseInt(nc[2], 10) || 1, s = nc[3] || 0;
					if (s < 1) {
						s += x;
					}
				}
				return [s, x];
			};

			for (var j = 0, len = ps.length; j < len; ++j) {
				// @todo ここにgがあるとおかしくなる(opera)
				buf = /:([\w-]+)(?:\(([^\s]+)\))?/.exec(ps[j]);
				switch(buf[1]) {
					case "first-child":
						founds = chk(nodes, "previousSibling", "nodeType");
						break;
					case "last-child":
						founds = chk(nodes, "nextSibling", "nodeType");
						break;
					case "only-child":
						founds = chk(nodes,  "previousSibling", "nodeType");
						founds = chk(founds, "nextSibling", "nodeType");
						break;
					case "nth-child":
						var seq = getSeq(buf[2]);
						founds = chkNth(nodes, seq[1], seq[0], "firstChild", "nextSibling", false);

						return founds;
					case "nth-last-child":
						var seq = getSeq(buf[2]);
						founds = chkNth(nodes, seq[1], seq[0], "lastChild", "previousSibling", false);

						return founds;
					case "first-of-type":
						founds = chk(nodes, "previousSibling", "nodeName");
						break;
					case "last-of-type":
						founds = chk(nodes, "nextSibling", "nodeName");
						break;
					case "only-of-type":
						founds = chk(nodes,  "previousSibling", "nodeName");
						founds = chk(founds, "nextSibling", "nodeName");
						break;
					case "nth-of-type":
						var seq = getSeq(buf[2]);
						founds = chkNth(nodes, seq[1], seq[0], "firstChild", "nextSibling", true);
						break;
					case "nth-last-of-type":
						var seq = getSeq(buf[2]);
						founds = chkNth(nodes, seq[1], seq[0], "lastChild", "previousSibling", true);
						break;
					case "empty":
						while ((elm = nodes[i++])) {
							if (elm.childNodes.length === 0) {
								founds.push(elm);
							}
						}
						break;
					case "contains":
						var val = buf[2].replace(/['"]+/g, "");
						while ((elm = nodes[i++])) {
							if (Sabel.Element.getTextContent(elm).indexOf(val) !== -1) {
								founds.push(elm);
							}
						}
						break;
					case "not":
						var chkFunc = function(nodes, func) {
							var founds = new Array(), i = 0, elm;
							while ((elm = nodes[i++])) {
								if (func(elm)) founds.push(elm);
							}
							return founds;
						};

						var mats = Sabel.Dom.Selector.pattern.exec(buf[2]);
						if (mats[2] /* TAG */) {
							founds = nodes;
							if (mats[2] !== "*") {
								var tagName = mats[2].toUpperCase();
								founds = chkFunc(nodes, function(elm) {
									return elm.nodeName !== tagName;
								});
							}
						} else if (mats[3] /* ID */) {
							var id = mats[3].replace("#", "");
							founds = chkFunc(nodes, function(elm) {
								return elm.getAttribute("id") !== id;
							});
						} else if (mats[4] /* CLASS */) {
							var className = new RegExp("(?:^|\\s+)" + mats[4].replace(".", "") + "(?:\\s+|$)");
							var klass = (window.ActiveXObject) ? "className" : "class";
							founds = chkFunc(nodes, function(elm) {
								return !className.test(elm.getAttribute(klass));
							});
						} else if (mats[5] /* ATTR */) {
							var b = /\[@?(\w+)(?:([$^!~*|]?=)(['"]?)(.+?)\3)?\]/.exec(mats[5]);
							if (b[2] == "|=" && b[1] !== "lang" && b[1] !== "hreflang") return [];

							if (b[1] === 'class' && window.ActiveXObject) b[1] = 'className';
							founds = chkFunc(nodes, function(elm) {
								a = elm.getAttribute(b[1]) || "";
								switch (b[2]) {
									case "=":
										return a !== b[4];
									case "!=":
										return a === b[4];
									case "~=":
										return (" "+a+" ").indexOf(" "+b[4]+" ") === -1;
									case "^=":
										return a.indexOf(b[4]) !== 0;
									case "$=":
										return a.lastIndexOf(b[4]) !== (a.length - b[4].length);
									case "*=":
										return a.indexOf(b[4]) === -1;
									case "|=":
										return (a+"-").toLowerCase().indexOf((b[4]+"-").toLowerCase()) !== 0;
									default:
										return !a;
								}
							});
						} else if (mats[6] /* PSEUDO */) {
							var ps  = mats[6].substr(1);
							var pat = new RegExp("(\\w+(?:-[\\w-]+)?)(?:\\(([^)]+)\\))?");
							var buf = pat.exec(ps);
							switch (buf[1]) {
								case "first-child":
									founds = chk(nodes, "previousSibling", "nodeType", true);
									break;
								case "last-child":
									founds = chk(nodes, "nextSibling", "nodeType", true);
									break;
								case "only-child":
									founds = chk(nodes,  "previousSibling", "nodeType", true);
									founds = founds.concat(chk(nodes, "nextSibling", "nodeType", true));
									founds = Sabel.Elements.unique(founds);
									break;
								case "nth-child":
									var seq = getSeq(buf[2]);
									founds = chkNth(nodes, seq[1], seq[0], "firstChild", "nextSibling", false, true);

									return founds;
								case "nth-last-child":
									var seq = getSeq(buf[2]);
									founds = chkNth(nodes, seq[1], seq[0], "lastChild", "previousSibling", false, true);

									return founds;
								case "first-of-type":
									founds = chk(nodes, "previousSibling", "nodeName", true);
									break;
								case "last-of-type":
									founds = chk(nodes, "nextSibling", "nodeName", true);
									break;
								case "only-of-type":
									founds = chk(nodes,  "previousSibling", "nodeName", true);
									founds = founds.concat(chk(nodes, "nextSibling", "nodeName", true));
									founds = Sabel.Elements.unique(founds);
									break;
								case "nth-of-type":
									var seq = getSeq(buf[2]);
									founds = chkNth(nodes, seq[1], seq[0], "firstChild", "nextSibling", true, true);
									break;
								case "nth-last-of-type":
									var seq = getSeq(buf[2]);
									founds = chkNth(nodes, seq[1], seq[0], "lastChild", "previousSibling", true, true);
									break;
								case "empty":
									while ((elm = nodes[i++])) {
										if (elm.childNodes.length !== 0)
											founds.push(elm);
									}
									break;
								case "contains":
									var val = buf[2].replace(/(^['"]|['"]$)/g, "");
									while ((elm = nodes[i++])) {
										if (Sabel.Element.getTextContent(elm).indexOf(val) === -1) {
											founds.push(elm);
										}
									}
									break;
							}
						}
						break;
				}
			}
			return new Sabel.Elements(founds);
		}
	},

	concat: function(array, iterable) {
		for (var i = 0, data; (data = iterable[i]); i++) {
			if (data._added !== true) {
				data._added = true;
				array[array.length] = data;
			}
		}
		return array;
	},

	clear: function(nodes, prop) {
		for (var i = 0, len = nodes.length; i < len; ++i) {
			nodes[i][prop] = null;
		}
		return nodes;
	}
};

Sabel.Element = function(element) {
	if (typeof element === "string") {
		element = document.createElement(element);
	} else if (typeof element !== "object") {
		// @todo throw exception ??
		return null;
	} else if (element._extended === true) {
		return element;
	}

	return Sabel.Object.extend(element, Sabel.Element, true);
};

Sabel.Element._extended = true;

Sabel.Element.get = function(element, id) {
	var elm = Sabel.get(id), parent = elm.parentNode;

	do {
		if (parent == element) return elm;
	} while ((parent = parent.parentNode));

	return null;
};

Sabel.Element.find = function(element, selector) {
	return Sabel.Dom.getElementsBySelector(selector, Sabel.get(element, false));
};

Sabel.Element._ieAttrMap = {
	"class": "className",
	"checked": "defaultChecked",
	"for": "htmlFor",
	"colspan": "colSpan",
	"bgcolor": "bgColor",
	"tabindex": "tabIndex",
	"accesskey": "accessKey"
};

Sabel.Element.setAttr = function(element, name, value) {
	if (Sabel.UserAgent.isIE && Sabel.UserAgent.version < 8) {
		if (name === "style") {
			element.style.cssText = value;
			return element;
		}

		name = Sabel.Element._ieAttrMap[name] || name;
	}
	element.setAttribute(name, value);

	return element;
};

Sabel.Element.append = function(element, child, text, attributes) {
	element = Sabel.get(element);

	if (typeof child === "string") {
		child = document.createElement(child);
		if (text) child.innerHTML = text;
		if (attributes) {
			for (var name in attributes)
				Sabel.Element.setAttr(child, name, attributes[name]);
		}
	}

	element.appendChild(new Sabel.Element(child));
	return child;
};

Sabel.Element.getDefaultDisplay = function(element) {
	var el = document.createElement(Sabel.get(element).nodeName);
	document.body.appendChild(el);
	var display = Sabel.Element.getStyle(el, "display");
	Sabel.Element.remove(el);
	return display;
};

Sabel.Element.show = function(element, value) {
	Sabel.get(element, false).style.display = value || Sabel.Element.getDefaultDisplay(element);
};

Sabel.Element.hide = function(element) {
	Sabel.get(element, false).style.display = "none";
};

Sabel.Element.hasClass = function(element, className) {
	element = Sabel.get(element, false);

	var pattern = new RegExp("(?:^|\\s+)" + className + "(?:\\s+|$)");
	return pattern.test(element.className);
};

Sabel.Element.addClass = function(element, className) {
	if (Sabel.Element.hasClass(element, className)) return element;

	element = Sabel.get(element, false);
	element.className = element.className + " " + className;
	return element;
}

Sabel.Element.removeClass = function(element, className) {
	element = Sabel.get(element, false);
	element.className = element.className.replace(
		new RegExp("(?:^|\\s+)" + className + "(?:\\s+|$)"), " "
	);

	return element;
};

Sabel.Element.replaceClass = function(element, oldClassName, newClassName) {
	element = Sabel.get(element, false);
	element.className = element.className.replace(
		new RegExp("(^|\\s+)" + oldClassName + "(\\s+|$)"), "$1"+newClassName+"$2"
	);

	return element;
};

Sabel.Element.hasAttribute = function(element, attribute) {
	element = Sabel.get(element, false);
	if (element.hasAttribute) return element.hasAttribute(attribute);
	var node = element.getAttributeNode(attribute);
	return node && node.specified;
};

if (Sabel.UserAgent.isIE) {
	Sabel.Element.getStyle = function(element, property) {
		element = Sabel.get(element, false);
		property = (property === "float") ? "styleFloat" : new Sabel.String(property).camelize();

		var style = element.currentStyle;
		return style[property];
	};
} else {
	Sabel.Element.getStyle = function(element, property) {
		element = Sabel.get(element, false);
		// Operaでelementがwindowだった時の対策
		// CSSでdisplay: noneが指定されている時の対策
		if (element === null || element.nodeType === undefined) return null;
		property = (property === "float") ? "cssFloat" : new Sabel.String(property).camelize();

		var css = document.defaultView.getComputedStyle(element, "")
		return css[property];
	};
}

Sabel.Element.setStyle = function(element, styles) {
	element = Sabel.get(element, false);

	if (arguments.length === 3) {
		Sabel.Element._setStyle(element, styles, arguments[2]);
	} else if (typeof styles === "string") {
		element.style.cssText += ";" + styles;
	} else {
		for (var prop in styles) {
			Sabel.Element._setStyle(element, prop, styles[prop]);
		}
	}

	return element;
};

Sabel.Element._setStyle = function(element, property, value) {
	var method = "set" + new Sabel.String(property).ucfirst();
	if (typeof Sabel.Element[method] !== "undefined") {
		Sabel.Element[method](element, value);
	} else {
		element.style[property] = value;
	}
};

Sabel.Element.deleteStyle = function(element, styles) {
	element = Sabel.get(element, false);

	if (typeof styles === "string") {
		element.style[styles] = "";
	} else {
		for (var i = 0, key; key = styles[i]; i++) {
			element.style[key] = "";
		}
	}

	return element;
};

Sabel.Element.insertAfter = function(element, newChild, refChild) {
	element = Sabel.get(element, false);
	if (element.lastChild == refChild) {
		element.appendChild(newChild);
	} else {
		refChild = Sabel.get(refChild);
		element.insertBefore(newChild, refChild.getNextSibling());
	}
	return element;
};

Sabel.Element.insertPreviousSibling = function(element, sibling) {
	element = Sabel.get(element);
	element.parentNode.insertBefore(sibling, element);
	return element;
}

Sabel.Element.insertNextSibling = function(element, sibling) {
	element = Sabel.get(element);
	element.getParentNode().insertAfter(sibling, element);
	return element;
}

Sabel.Element.setHeight = function(element, value) {
	element = Sabel.get(element, false);
	if (value !== "" && typeof value === "number") value = value + "px";
	element.style.height = value;
	return element;
};

Sabel.Element.setWidth = function(element, value) {
	element = Sabel.get(element, false);
	if (value !== "" && typeof value === "number") value = value + "px";
	element.style.width = value;
	return element;
};

Sabel.Element.setOpacity = function(element, value) {
	element = Sabel.get(element, false);

	if (Sabel.UserAgent.isIE) {
		element.style.filter = "alpha(opacity=" + value * 100 + ")";
	} else {
		element.style.opacity = value;
	}
};

Sabel.Element.getBackGroundColor = function(el) {
	var color;
	el = Sabel.get(el);
	do {
		color = Sabel.Element.getStyle(el, "backgroundColor");
		if (color !== "" && color !== "transparent" &&
		   (color.indexOf("rgba") === -1 || color.slice(-2) !== "0)")) break;
	} while ((el = el.parentNode));
	return (color !== "transparent") ? color : "#ffffff";
};

Sabel.Element.getCumulativeTop = function(element) {
	return Sabel.Element.getRegion(element).top;
};

Sabel.Element.getCumulativeLeft = function(element) {
	return Sabel.Element.getRegion(element).left;
};

Sabel.Element.getCumulativePositions = function(element) {
	var rect = Sabel.Element.getRegion(element);
	return {top: rect.top, left: rect.left};
};

Sabel.Element.getOffsetTop = function(element) {
	element = Sabel.get(element, false);

	var position = element.offsetTop;

	if (Sabel.UserAgent.isOpera) {
		var parent = element.offsetParent;
		if (parent.nodeName !== "BODY") {
			position -= parseInt(Sabel.Element.getStyle(parent, "borderTopWidth"));
		}
	}

	return position;
};

Sabel.Element.getOffsetLeft = function(element) {
	element = Sabel.get(element, false);

	var position = element.offsetLeft;

	if (Sabel.UserAgent.isOpera) {
		var parent = element.offsetParent;
		if (parent.nodeName !== "BODY") {
			position -= parseInt(Sabel.Element.getStyle(parent, "borderLeftWidth"));
		}
	}

	return position;
};

Sabel.Element.getOffsetPositions = function(element) {
	return {
		left: this.getOffsetLeft(element),
		top:  this.getOffsetTop(element)
	};
};

Sabel.Element.getDimensions = function(element, ignoreBorder) {
	element = Sabel.get(element, false);
	if (element.nodeType !== 1) return {};

	var style = element.style;

	if (Sabel.Element.getStyle(element, "display") !== "none") {
		var dimensions = {
			width: element.offsetWidth,
			height: element.offsetHeight
		};
	} else {
		var oldV = style.visibility;
		var oldP = style.positions;
		var oldD = "none";

		style.visibility = "hidden";
		style.positions  = "absolute";
		style.display    = "block";

		var dimensions = {
			width:  element.offsetWidth,
			height: element.offsetHeight
		};

		style.visibility = oldV;
		style.positions  = oldP;
		style.display    = oldD;
	}

	if (ignoreBorder === true) {
		// parseFloat Fx3 fix
		// || 0 IE7 fix
		dimensions.width -= Math.round(parseFloat(Sabel.Element.getStyle(element, "borderLeftWidth")))||0
		                  + Math.round(parseFloat(Sabel.Element.getStyle(element, "borderRightWidth")))||0;
		dimensions.height -= Math.round(parseFloat(Sabel.Element.getStyle(element, "borderTopWidth")))||0
		                   + Math.round(parseFloat(Sabel.Element.getStyle(element, "borderBottomWidth")))||0;
	}

	return dimensions;
};

Sabel.Element.getWidth = function(element, ignoreBorder) {
	return Sabel.Element.getDimensions(element, ignoreBorder).width;
};

Sabel.Element.getHeight = function(element, ignoreBorder) {
	return Sabel.Element.getDimensions(element, ignoreBorder).height;
};

Sabel.Element.getRegion = function(element) {
	element = Sabel.get(element);
	if (element.parentNode === null || element.offsetParent === null) {
		return false;
	}

	if (element.getBoundingClientRect) {
		var rect = element.getBoundingClientRect();

		var st = Sabel.Window.getScrollTop()  - document.documentElement.clientTop;
		var sl = Sabel.Window.getScrollLeft() - document.documentElement.clientLeft;

		return {
			top: Math.round(rect.top + st), right: Math.round(rect.right + sl),
			bottom: Math.round(rect.bottom + st), left: Math.round(rect.left + sl),
			toString: function() {
				return new Sabel.String("{top: #{top}, right: #{right}, bottom: #{bottom}, left: #{left}}").format(this);
			}
		};
	} else {
		var wh = Sabel.Element.getDimensions(element);
		var rect = {top: element.offsetTop, left: element.offsetLeft};

		var add = function(t, l) {
			rect.top  += parseInt(t) || 0;
			rect.left += parseInt(l) || 0;
		};

		while ((element = element.offsetParent)) {
			add(element.offsetTop, element.offsetLeft);

			if (Sabel.UserAgent.isOpera === false) {
				var borderTop  = Sabel.Element.getStyle(element, "borderTopWidth");
				var borderLeft = Sabel.Element.getStyle(element, "borderLeftWidth");
				add(borderTop, borderLeft);

				if (Sabel.UserAgent.isMozilla) {
					var of = Sabel.Element.getStyle(element, "overflow");
					if (!Sabel.Array.include(["visible", "inherit"], of)) {
						add(borderTop, borderLeft);
					}
				}

				if (Sabel.Array.include(["BODY", "HTML"], element.tagName)) {
					if (document.compatMode === "CSS1Compat") {
						var html = document.getElementsByTagName('html')[0];
						add(
							Sabel.Element.getStyle(html, "marginTop"),
							Sabel.Element.getStyle(html, "marginLeft")
						);

						if (Sabel.UserAgent.isIE) {
							add(
								Sabel.Element.getStyle(element, "marginTop"),
								Sabel.Element.getStyle(element, "marginLeft")
							);
							add(
								Sabel.Element.getStyle(html, "borderTopWidth"),
								Sabel.Element.getStyle(html, "borderLeftWidth")
							);
						}
					}
					break;
				}
			}
		}

		return {
			top: rect.top, right: rect.left + wh.width,
			bottom: rect.top + wh.height, left: rect.left,
			toString: function() {
				return new Sabel.String("{top: #{top}, right: #{right}, bottom: #{bottom}, left: #{left}}").format(this);
			}
		};
	}
};

Sabel.Element.remove = function(element) {
	element = Sabel.get(element, false);
	element.parentNode.removeChild(element);
};

Sabel.Element.update = function(element, contents) {
	element = Sabel.get(element, false);

	var newEl = document.createElement(element.nodeName);
	newEl.id  = element.id;
	newEl.className = element.className;
	newEl.innerHTML = contents;

	element.parentNode.replaceChild(newEl, element);

	return Sabel.get(newEl);
};

Sabel.Element.getXPath = function(element) {
	var buf = new Array(), idx, tagName = element.tagName;

	do {
		if (element.getAttribute("id")) {
			buf.unshift('id("' + element.getAttribute("id") + '")');
			return buf.join("/");
		} else {
			if (document.getElementsByTagName(element.nodeName).length === 1) {
				buf.unshift("/" + element.nodeName.toLowerCase());
				break;
			} else {
				idx = Sabel.Element.getPreviousSiblings(element, element.nodeName).length + 1;
				buf.unshift(element.nodeName.toLowerCase() + "[" + idx + "]");
			}
		}
	} while ((element = element.parentNode) && element.nodeType === 1);
	return "/" + buf.join("/");
};

if (Sabel.UserAgent.isIE) {
	Sabel.Element.getTextContent = function(element) {
		return element.innerText;
	};
} else {
	Sabel.Element.getTextContent = function(element) {
		return element.textContent;
	};
}

Sabel.Element.observe = function(element, eventName, handler, useCapture, scope) {
	element = Sabel.get(element, false);
	if (element._events === undefined) element._events = {};
	if (element._events[eventName] === undefined) element._events[eventName] = new Array();

	var evt = new Sabel.Event(element, eventName, handler, useCapture, scope);
	element._events[eventName].push(evt);

	return evt;
};

Sabel.Element.stopObserve = function(element, eventName, handler) {
	element = Sabel.get(element, false);
	var events = (element._events) ? element._events[eventName] : "";
	if (events.constructor === Array) {
		if (typeof handler === "function") {
			Sabel.Array.each(events, function(e) { if (e.getHandler() === handler) e.stop(); });
		} else {
			Sabel.Array.each(events, function(e) { e.stop(); });
		}
	}

	return element;
};

Sabel.Element.analyze = function(element) {
	element = Sabel.get(element, false)
	var as = element.attributes, buf = new Array(), attr, i = 0;

	buf.push("<" + element.tagName.toLowerCase());
	if (Sabel.UserAgent.isIE) {
		var def = document.createElement(element.nodeName);

		var defAttr;
		while ((attr = as[i++])) {
			if (typeof attr.nodeValue !== "string") continue;

			defAttr = def.getAttributeNode(attr.nodeName);
			if (defAttr != null && attr.nodeValue === defAttr.nodeValue) continue;

			buf[buf.length] = attr.nodeName + '="' + attr.nodeValue + '"';
		}
	} else {
		while ((attr = as[i++])) {
			buf[buf.length] = attr.nodeName + '="' + attr.nodeValue + '"';
		}
	}

	return buf.join(" ") + ">";
};

Sabel.Element.getParentNode = function(element, tagName) {
	tagName = (tagName || "").toUpperCase();
	element = Sabel.get(element, false);
	while ((element = element.parentNode)) {
		if (tagName === "" || tagName === element.tagName)
			return new Sabel.Element(element);
	}
	return null;
};

Sabel.Element.getChildElements = function(element, tagName) {
	tagName = (tagName || "").toUpperCase();
	var buf = new Array();
	element = Sabel.get(element, false);
	Sabel.Array.each(element.childNodes, function(elm) {
		if (elm.nodeType === 1) {
			if (tagName === "" || tagName === elm.tagName) buf[buf.length] = elm;
		}
	});
	return buf;
};

Sabel.Element.getFirstChild = function(element, tagName) {
	element = Sabel.Element.getChildElements(element, tagName)[0];
	return (element) ? new Sabel.Element(element) : null;
};

Sabel.Element.getLastChild = function(element, tagName) {
	var elms = Sabel.Element.getChildElements(element, tagName);
	return new Sabel.Element(elms[elms.length - 1]);
};

Sabel.Element.getNextSibling = function(element) {
	while ((element = element.nextSibling)) {
		if (element.nodeType === 1) return new Sabel.Element(element);
	}
	return null;
};

Sabel.Element.getPreviousSibling = function(element) {
	while ((element = element.previousSibling)) {
		if (element.nodeType === 1) return new Sabel.Element(element);
	}
	return null;
};

Sabel.Element.getPreviousSiblings = function(element, nodeName) {
	nodeName = (nodeName || "").toUpperCase();
	var buf = new Array();
	while ((element = element.previousSibling)) {
		if (element.nodeType === 1) {
			if (nodeName === "" || nodeName === element.nodeName)
				buf[buf.length] = element;
		}
	}
	return buf;
};

Sabel.Element.getNextSiblings = function(element, nodeName) {
	nodeName = (nodeName || "").toUpperCase();
	var buf = new Array();
	while ((element = element.nextSibling)) {
		if (element.nodeType === 1) {
			if (nodeName === "" || nodeName === element.nodeName)
				buf[buf.length] = element;
		}
	}
	return buf;
};

Sabel.Element.getNodeIndex = function(element, reverse, ofType) {
	var ret;
	if (ofType === true) {
		ret = Sabel.Element._getOfTypeNodeIndex(element, reverse);
	} else {
		ret = Sabel.Element._getNodeIndex(element,reverse);
	};
	return ret;
};

Sabel.Element._getNodeIndex = function(element, reverse) {
	var parentNode = element.parentNode;

	var childNodes = parentNode.childNodes;
	var propName = (reverse === true) ? "__cachedLastIdx"
	                                  : "__cachedIdx";
	if (parentNode.__cachedLength === childNodes.length) {
		if (element[propName]) {
			return element[propName];
		}
	}
	parentNode.__cachedLength = childNodes.length;

	if (reverse === true) {
		childNodes = new Sabel.Array(childNodes).reverse();
	}

	for (var i = 0, idx = 1, child; child = childNodes[i]; i++) {
		if (child.nodeType == 1) child[propName] = idx++;
	}

	return element[propName];
};

Sabel.Element._getOfTypeNodeIndex = function(element, reverse) {
	var parentNode = element.parentNode;
	var childNodes = parentNode.childNodes;
	var propName   = (reverse === true) ? "__cachedLastOfTypeIdx"
	                                    : "__cachedOfTypeIdx";

	if (parentNode.__cachedLength === childNodes.length) {
		if (element[propName]) {
			return element[propName];
		}
	}

	if (reverse === true) {
		childNodes = new Sabel.Array(childNodes).reverse();
	}
	parentNode.__cachedLength = childNodes.length;

	for (var i = 0, idx = 1, child; child = childNodes[i]; i++) {
		if (child.tagName === element.tagName && child.nodeType === 1) {
			child[propName] = idx++;
		}
	}

	return element[propName];
};

Sabel.Element.isContain = function(element, other) {
	if (element === document) element = document.body;

	if (element.contains) {
		// IE, Opera, Safari
		return element.contains(other);
	} else {
		// Firefox
		return !!(element.compareDocumentPosition(other) & element.DOCUMENT_POSITION_CONTAINED_BY);
	}
};

Sabel.CSS = {};

Sabel.CSS.rgbToHex = function(rgb) {
	var hex = "#";
	Sabel.Array.each(rgb, function(num) {
		hex += new Sabel.String("%02x").sprintf(num.toString(16));
	});
	return hex;
};

Sabel.CSS.getRGB = function(color) {
	if (color.indexOf("#") === 0) {
		if (color.length === 4) {
			return [
				parseInt(color.charAt(1) + color.charAt(1), 16),
				parseInt(color.charAt(2) + color.charAt(2), 16),
				parseInt(color.charAt(3) + color.charAt(3), 16)
			];
		} else if (color.length === 7) {
			return [
				parseInt(color.substr(1,2), 16),
				parseInt(color.substr(3,2), 16),
				parseInt(color.substr(5,2), 16)
			];
		}
	} else if (color.search("\(([0-9, ]+)\)") !== -1) {
		return RegExp.$1.replace(/ /g, "").split(",");
	} else if (typeof color === "Array" && color.length === 3) {
		return color;
	} else {
		if (color in Sabel.CSS.colorNames) return Sabel.CSS.colorNames[color];
	}
	return null;
}

Sabel.CSS.colorNames = {
	black:   [  0,   0,   0],
	blue:    [  0,   0, 255],
	green:   [  0, 128,   0],
	lime:    [  0, 255,   0],
	cyan:    [  0, 255, 255],
	purple:  [128,   0, 128],
	gray:    [128, 128, 128],
	silver:  [192, 192, 192],
	red:     [255,   0,   0],
	magenta: [255,   0, 255],
	orange:  [255, 165,   0],
	pink:    [255, 192, 203],
	yellow:  [255, 255,   0],
	white:   [255, 255, 255]
};

Sabel.Object.extend(Sabel.Element, Sabel.Object.Methods);

Sabel.Elements = function(elements) {
	if (typeof elements === "undefined") {
		elements = new Sabel.Array();
	// @todo 普通のSabel.Elementが引っかかる
	} else if (elements._extended === true) {
		return elements;
	} else if (elements.constructor !== Array) {
		return null;
	} else {
		elements = new Sabel.Array(elements);
	}

	return Sabel.Object.extend(elements, Sabel.Elements, true, true);
};

Sabel.Elements._extended = true;

Sabel.Elements.add = function(elements, element) {
	elements[elements.length] = element;
};

Sabel.Elements.item = function(elements, pos) {
	var elm = elements[pos];

	return (elm) ? new Sabel.Element(elm) : null;
};

Sabel.Elements.observe = function(elements, eventName, handler, useCapture, scope) {
	Sabel.Array.each(elements, function(elm) {
		Sabel.Element.observe(elm, eventName, handler, useCapture, scope);
	});
};

Sabel.Elements.stopObserve = function(elements, eventName, handler) {
	Sabel.Array.each(elements, function(elm) {
		Sabel.Element.stopObserve(elm, eventName, handler);
	});
};

Sabel.Elements.each = function(elements, callback) {
	var i = 0, el;
	while((el = elements.item(i))) callback(el, i++);
	return elements;
};

Sabel.Elements.unique = function(elements) {
	var finds = Sabel.Array.inject(elements, function(elm) {
		if (elm._searched === true) return false;
		elm._searched = true;
		return true;
	});

	Sabel.Array.each(finds, function(elm) { elm._searched = false; });
	return finds;
};

Sabel.Object.extend(Sabel.Elements, Sabel.Object.Methods);

Sabel.Iterator = function(iterable) {
	this.items = Sabel.Array(iterable);
	this.index = -1;
};

Sabel.Iterator.prototype = {
	hasPrev: function() {
		return this.index > 0;
	},

	hasNext: function() {
		return this.index < this.items.length-1;
	},

	prev: function() {
		return this.index > -1 ? this.items[--this.index] || null : null;
	},

	next: function() {
		return this.hasNext() ? this.items[++this.index] || null : null;
	}
};


if (typeof window.XMLHttpRequest === "undefined") {
	window.XMLHttpRequest = function() {
		var http;
		var objects = ["Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.3.0", "Msxml2.XMLHTTP", "Microsoft.XMLHTTP"];
		for (var i = 0, obj; obj = objects[i]; i++) {
			try {
				http = new ActiveXObject(obj);
				window.XMLHttpRequest = function() {
					return new ActiveXObject(obj);
				}
				break;
			} catch (e) {}
		}
		return http;
	}
};

Sabel.Ajax = function() {
	this.init.apply(this, arguments);
};

Sabel.Ajax.prototype = {
	init: function() {
		this.xmlhttp   = new XMLHttpRequest();
		this.completed = false;
	},

	request: function(url, options) {
		var xmlhttp = this.xmlhttp;
		options = this.setOptions(options);

		this.completed = false;
		this._abort();

		if (options.method === "get") {
			url += ((url.indexOf("?") !== -1) ? "&" : "?") + options.params;
		}

		xmlhttp.open(options.method, url, options.async);
		xmlhttp.onreadystatechange = Sabel.Function.bind(this.onStateChange, this);

		this.setRequestHeaders();
		if (options.timeout) {
			if (typeof xmlhttp.timeout !== "undefined") {
				xmlhttp.timeout   = options.timeout;
				xmlhttp.ontimeout = Sabel.Function.bind(this.abort, this);
			} else {
				this.timer = setTimeout(Sabel.Function.bind(this.abort, this), options.timeout);
			}
		}
		xmlhttp.send((options.method === "post") ? options.params : "");

		if (options.async === false) {
			this.onStateChange();
		}
	},

	abort: function() {
		if (this._abort()) this.options.onTimeout.apply(this.options.scope);
	},

	_abort: function() {
		var xmlhttp = this.xmlhttp;
		if (xmlhttp.readyState !== 4) {
			xmlhttp.onreadystatechange = Sabel.emptyFunc;
			xmlhttp.abort();

			return true;
		}
		return false;
	},

	updater: function(element, url, options) {
		options = options || {};

		var onComplete = options.onComplete || function() {};
		options.onComplete = function(response) {
			Sabel.get(element).innerHTML = response.responseText;
			onComplete(response);
		}

		this.request(url, options);
	},

	setOptions: function(options) {
		if (options === undefined) options = {};

		var defaultOptions = {
			method: "post",
			params: "",
			contentType: "application/x-www-form-urlencoded",
			charset: "UTF-8",
			onComplete: function(){},
			onSuccess: function(){},
			onFailure: function(){},
			onTimeout: function(){},
			scope: null,
			async: true,
			autoEval: false
		};
		Sabel.Object.extend(options, defaultOptions);
		options.method = options.method.toLowerCase();

		if (options.params instanceof Object) {
			options.params = new Sabel.QueryObject(options.params).serialize();
		}

		return (this.options = options);
	},

	setRequestHeaders: function() {
		var headers = {
			"X-Requested-With": "XMLHttpRequest",
			"Accept": "text/javascript, text/html, application/xml, text/xml, */*"
		};
		var xmlhttp = this.xmlhttp;
		var options = this.options;

		if (options.method === "post") {
			headers["Content-Type"] = options.contentType + "; charset=" + options.charset;
		}

		if (typeof options.headers === "object") {
			headers = Sabel.Object.extend(options.headers, headers);
		}

		for (var key in headers) {
			xmlhttp.setRequestHeader(key, headers[key]);
		}
	},

	onStateChange: function() {
		if (this.completed === true) return;

		if (this.xmlhttp.readyState === 4) {
			this.completed = true;
			clearTimeout(this.timer);

			var options  = this.options;
			var response = this.getResponses();
			options["on" + (this.isSuccess() ? "Success" : "Failure")].call(options.scope, response);
			options.onComplete.call(options.scope, response);

			this.xmlhttp.onreadystatechange = Sabel.emptyFunc;
		}
	},

	getResponses: function() {
		var xmlhttp  = this.xmlhttp;
		var response = new Object();
		response.responseXML  = xmlhttp.responseXML;
		response.responseText = this.responseFilter(xmlhttp.responseText);
		if (this.options.autoEval === true) {
			response.responseJson = eval("eval(" + response.responseText+ ")");
		}
		response.status = xmlhttp.status;
		response.statusText = xmlhttp.statusText;
		return response;
	},

	isSuccess: function() {
		var status = this.xmlhttp.status;
		return (status && (status >= 200 && status < 300));
	},

	responseFilter: function(text) {
		if (Sabel.UserAgent.isKHTML) {
			var esc = escape(text);
			if (esc.indexOf("%u") < 0 && esc.indexOf("%") > -1) {
				text = decodeURIComponent(esc);
			}
		}
		return text;
	}
};

Sabel.History = function() {
	this.init.apply(this, arguments);
};

Sabel.History.prototype = {
	currentHash: "",
	callback: null,
	timer: null,

	init: function(callback) {
		this.callback = callback || function() {}
		var hash = this._getHash(document);

		if (hash !== "") this.callback(hash);

		if (typeof window.onhashchange === "undefined") {
			this.timer = setInterval(Sabel.Function.bind(this._check, this), 300);
		} else {
			new Sabel.Event(window, "hashchange", this._check, false, this);
		}
	},

	load: function(hash) {
		this._setHash(hash.replace(/^#/, ""), true);
	},

	_check: function() {
		var hash = this._getHash(document);

		if (hash !== this.currentHash) {
			this._setHash(hash);
		}
	},

	_setHash: function(hash, isUpdate) {
		if (isUpdate === true) location.hash = "#" + hash;
		this.currentHash = hash;
		if (hash !== "") this.callback(hash);
	},

	_getHash: function(target) {
		return new Sabel.Uri(target.location.href).hash.replace(/^[^#]*#/, "");
	}
};

if (Sabel.UserAgent.isIE && Sabel.UserAgent.version < 8) {
	Sabel.History.prototype.init = function(callback) {
		this.callback = callback || function() {}
		var hash = this._getHash(document);

		this.iframe = document.createElement('<iframe id="sbl_history_frame" style="display: none;">');
		document.body.appendChild(this.iframe);
		var doc = this.iframe.contentWindow.document;
		doc.open();
		doc.close();
		this._setHash(doc, hash, false);

		this.timer = setInterval(Sabel.Function.bind(this._check, this), 300);
	};

	Sabel.History.prototype.load = function(hash) {
		var doc = this._getIframe();
		hash = hash.replace(/^#/, "");

		this._setHash(document, hash);
		doc.open();
		doc.close();
		this._setHash(doc, hash);

		this.callback(hash);
	};

	Sabel.History.prototype._check = function() {
		var hash = this._getHash(this._getIframe());

		if (hash !== this.currentHash) {
			this._setHash(document, hash);
			if (hash !== "") this.callback(hash);
		}
	};

	Sabel.History.prototype._setHash = function(target, hash, isUpdate) {
		target.location.hash = "#" + hash;
		if (isUpdate !== false) this.currentHash = hash;
	};

	Sabel.History.prototype._getIframe = function() {
		return this.iframe.contentWindow.document;
	};
}

Sabel.Form = function(form) {
	this.form = Sabel.get(form, false);

	var elms = this.form.getElementsByTagName("*");
	var buf = {}, elements = {};
	Sabel.Array.each(elms, function(el) {
		var method = Sabel.Form.Elements[el.tagName.toLowerCase()], value;
		if (method) {
			if (elements[el.name]) {
				if (!Sabel.Object.isArray(elements[el.name])) {
					elements[el.name] = [elements[el.name]];
				}
				elements[el.name].push(el);
			} else {
				elements[el.name] = el;
			}

			if ((value = method(el)) !== null) {
				if (buf[el.name]) {
					if (!Sabel.Object.isArray(buf[el.name])) {
						buf[el.name] = [buf[el.name]];
					}
					buf[el.name].push(value);
				} else {
					buf[el.name] = value;
				}
			}
		}
	});

	this.queryObj = new Sabel.QueryObject(buf);
	this.elements = elements;
};

Sabel.Form.prototype = {
	has: function(key) {
		return !!this.elements[key];
	},

	get: function(key) {
		return this.queryObj.get(key);

		var el = this.elements[key];
		if (Sabel.Object.isArray(el)) {
			var method = Sabel.Form.Elements[el[0].tagName.toLowerCase()], value;
			for (var i = 0; i < el.length; i++) {
				if ((value = method(el[i])) !== null) return value;
			}
		}
		return el.value;
	},

	set: function(key, val) {
		this.queryObj.set(key, val);

		var el = this.elements[key];
		if (Sabel.Object.isArray(el)) {
			if (Sabel.Object.isString(val)) val = [val];

			for (var i = 0; i < el.length; i++) {
				//el[i].checked = (el[i].value === val);
				el[i].checked = Sabel.Array.include(val, el[i].value);
			}
		} else {
			el.value = val;
		}

		return this;
	},

	serialize: function() {
		return this.queryObj.serialize();
	}
};

Sabel.Object.extend(Sabel.Form, Sabel.Object.Methods);

Sabel.Validator = function(formElm, errField) {
	this.errField   = Sabel.get(errField || "sbl_errmsg", false);
	this.validators = new Object();

	Sabel.Element.observe(formElm, "submit", Sabel.Function.bindWithEvent(this.validate, this));
};

Sabel.Validator.prototype = {
	add: function(elm, func, errMsg) {
		elm = Sabel.get(elm, false);
		var name = elm.name;
		var validators = this.validators;

		if (validators[name] === undefined) {
			validators[name] = new Sabel.Validator.Element(elm);
		}

		validators[name].add(func, errMsg);
	},

	validate: function(e) {
		var validators = this.validators;
		var errors = [], v;
		for (var name in validators) {
			v = validators[name];
			if (v.validate() === false) errors.push(v.errMsg);
		}

		this.clearMessageField();

		if (errors.length !== 0) {
			this.insertMessage(errors);
			Sabel.Event.preventDefault(e);
		}
	},

	insertMessage: function(errors) {
		this.errField.appendChild(this.getErrorMessage(errors));
		Sabel.Element.show(this.errField);

		var yPos = Sabel.Element.getCumulativeTop(this.errField) - 20;
		window.scroll(0, yPos);
	},

	clearMessageField: function() {
		Sabel.Element.hide(this.errField);
		this.errField.innerHTML = "";
	},

	getErrorMessage: function(errors) {
		var ul = document.createElement("ul");
		Sabel.Array.each(errors, function(err) {
			var li = document.createElement("li");
			li.appendChild(document.createTextNode(err));
			ul.appendChild(li);
		});
		return ul;
	}
};

Sabel.Event = function(element, eventName, handler, useCapture, scope) {
	element = Sabel.get(element, false);

	this.element    = element;
	this.eventName  = eventName;
	this.defHandler = handler;
	this.handler    = function(evt) {
		handler.call(scope || element, evt || window.event);
	};
	this.useCapture = useCapture;
	this.isActive   = false;
	this.eventId    = Sabel.Events.add(this);

	this.start();
};

Sabel.Event.prototype = {
	start: function() {
		if (this.isActive === false) {
			var element = this.element;

			if (element.addEventListener) {
				var eventName = this.eventName, obj;
				if (Sabel.Event._events[eventName] &&
					(obj = Sabel.Event._events[eventName](this.handler, this.element))) {
					element.addEventListener(obj.eventName, obj.handler, this.useCapture);
				} else {
					element.addEventListener(this.eventName, this.handler, this.useCapture);
				}
			} else if (element.attachEvent) {
				element.attachEvent("on" + this.eventName, this.handler);
			}
			this.isActive = true;
		}
	},

	stop: function() {
		if (this.isActive === true) {
			var element = this.element;

			if (element.removeEventListener) {
				element.removeEventListener(this.eventName, this.handler, this.useCapture);
			} else if (element.detachEvent) {
				element.detachEvent("on" + this.eventName, this.handler);
			}
			this.isActive = false;
		}
	},

	getHandler: function() {
		return this.defHandler;
	}
};

Sabel.Event.getTarget = function(evt) {
	return new Sabel.Element(evt.srcElement || evt.target);
};

Sabel.Event.stopPropagation = function(evt) {
	evt.stopPropagation();
};

Sabel.Event.preventDefault = function(evt) {
	evt.preventDefault();
};

Sabel.Event._isChildEvent = function(event, el) {
	var p = event.relatedTarget;

	try {
		while (p && p !== el) {
			p = p.parentNode;
		}
	} catch (e) {
	}

	return p === el;
};

Sabel.Event._events = {
	mouseenter: function(handler, el) {
		if (Sabel.UserAgent.isIE) return handler;

		return {eventName: "mouseover", handler: function(event) {
			if (Sabel.Event._isChildEvent(event, el)) return false;

			return handler(event);
		}};
	},

	mouseleave: function(handler, el) {
		if (Sabel.UserAgent.isIE) return handler;

		return {eventName: "mouseout", handler: function(event) {
			if (Sabel.Event._isChildEvent(event, el)) return false;

			return handler(event);
		}};
	}
};

if (Sabel.UserAgent.isIE) {
	Sabel.Event.stopPropagation = function(evt) {
		(evt || window.event).cancelBubble = true;
	};

	Sabel.Event.preventDefault = function(evt) {
		(evt || window.event).returnValue = false;
	};
}
Sabel.Events = {

	_events: new Array(),

	add: function(evtObj) {
		var len = Sabel.Events._events.length;
		Sabel.Events._events[len] = evtObj;

		return len;
	},

	stop: function(eventId) {
		Sabel.Events._events[eventId].stop();
	},

	stopAll: function() {
		var events = Sabel.Events._events;

		Sabel.Array.callmap(events, "stop");
	}
};


Sabel.KeyEvent = new Sabel.Class({
	_lists: {},
	element: null,

	_keyDownEvent: null,
	_keyPressEvent: null,

	init: function(element) {
		this._lists = {};
		element = this.element = Sabel.get(element) || document;

		var cancel = false;

		var keyDownListener = function(e) {
			var key = this.getKeyCode(e);

			if (this._lists[key]) {
				cancel = (this._lists[key](e) !== false);
			} else {
				cancel = false;
			}
		};

		var keyPressListener = function(e) {
			if (cancel === true) {
				Sabel.Event.preventDefault(e);
				cancel = false;
				return;
			}

			var key = this.getKeyCode(e);
			if (this._lists[key]) this._lists[key](e);
		};

		this._keyDownEvent  = new Sabel.Event(element, "keydown", keyDownListener, false, this);
		this._keyPressEvent = new Sabel.Event(element, "keypress", keyPressListener, false, this);
	},

	start: function() {
		this._keyDownEvent.start();
		this._keyPressEvent.start();

		return this;
	},

	stop: function() {
		this._keyDownEvent.stop();
		this._keyPressEvent.stop();

		return this;
	},

	add: function(key, func, scope) {
		var buf = key.toLowerCase().split("-"), tmp = buf.pop();
		buf.sort();
		buf.push(tmp);
		this._lists[buf.join("-")] = Sabel.Function.bind(func, scope || this.element);

		return this;
	},

	remove: function(key) {
		var buf = key.toLowerCase().split("-"), tmp = buf.pop();
		buf.sort();
		buf.push(tmp);
		delete this._lists[buf.join("-")];

		return this;
	},

	getKeyCode: function(e) {
		var buf = new Array();
		var kc = e.keyCode || e.charCode || e.which;

		if (Sabel.Number.between(kc, 16, 18) !== true) {
			if (e.altKey === true) buf.push("alt");
			if (e.ctrlKey === true) buf.push("ctrl");
			if (e.type === "keydown" && e.shiftKey === true) buf.push("shift");
		}

		if (e.type === "keydown") {
			if (Sabel.UserAgent.isIPhone === true) {
				if (Sabel.KeyEvent.special_keys_for_iphone[kc]) {
					buf.push(Sabel.KeyEvent.special_keys_for_iphone[kc]);
				} else {
					buf.push(String.fromCharCode(kc).toLowerCase());
				}
			} else {
				if (Sabel.KeyEvent.special_keys[kc]) {
					buf.push(Sabel.KeyEvent.special_keys[kc]);
				} else {
					buf.push(String.fromCharCode(kc).toLowerCase());
				}
			}
		} else {
			buf.push(String.fromCharCode(kc).toLowerCase());
		}

		return buf.join("-");
	}
});

Sabel.KeyEvent.special_keys = {
	8: "backspace", 9: "tab", 13: "enter", 16: "shift",
	17: "ctrl", 18: "alt", 19: "pause", 27: "esc",
	32: "space", 33: "pageup", 34: "pagedown", 35: "end", 36: "home",
	37: "left", 38: "up", 39: "right", 40: "down", 45: "insert", 46: "del",
	106: "*", 107: "+", 109: "-", 110: ".", 111: "/",
	112: "f1", 113: "f2", 114: "f3", 115: "f4", 116: "f5", 117: "f6",
	118: "f7", 119: "f8", 120: "f9", 121: "f10", 122: "f11", 123: "f12",
	144: "numlock", 145: "scrolllock", 240: "capslock"
};
Sabel.KeyEvent.special_keys_for_iphone = {
	10: "enter", 32: "space", 127: "del",
	163: "pound", 165: "yen", 8226: "bullet", 8364: "euro"
};

Sabel.Effect = function() {
	this.init.apply(this, arguments);
};

Sabel.Effect.prototype = {
	init: function(options) {
		options = options || {};

		this.callback = options.callback || function() {};
		this.interval = options.interval || 20;
		this.duration = options.duration || 1000;
		this.step = this.interval / this.duration;
		this.state  = null;
		this.target = 0;
		this.timer  = null;
		this.effects = Sabel.Array();
	},

	add: function(effect) {
		var args = arguments;
		if (typeof effect === "string") {
			effect  = new Sabel.Effect[effect](args[1]);
			reverse = args[2] || false;
		} else {
			reverse = args[1] || false;
		}
		this.effects.push({func: effect, reverse: reverse});
		return this;
	},

	setCallback: function(callback, scope) {
		this.callback = function() {
			callback.apply(scope||window, arguments);
		};
	},

	play: function(force) {
		if (this.state === 1 && force !== true) {
			return this;
		} else if (this.state === 0 || this.state === null) {
			this.set(0, 1);
			this._run();
		} else if (force === true) {
			var state = (this.state === 1) ? 0 : this.state;
			this.set(state, 1)
			this._run();
		} else if (this.timer === null) {
			this.set(this.state, 1);
			this._run();
		}
		return this;
	},

	reverse: function(force) {
		if (this.state === 0 && force !== true) {
			return this;
		} else if (this.state === 1 || this.state === null) {
			this.set(1, 0);
			this._run();
		} else if (force === true) {
			var state = (this.state === 0) ? 1 : this.state;
			this.set(state, 0)
			this._run();
		} else if (this.timer === null) {
			this.set(this.state, 0);
			this._run();
		}
		return this;
	},

	toggle: function() {
		this.set(this.state, 1 - this.target);
		this._run();
		return this;
	},

	pause: function() {
		this._clear();
		return this;
	},

	resume: function() {
		this._clear();
		this._run();
		return this;
	},

	show: function() {
		this.set(1, 1);
		this.execEffects();
		var state = this.state;
		this.effects.each(function(ef) {
			ef.func.end((ef.reverse === true) ? 1 - state : state);
		});

		return this;
	},

	hide: function() {
		this.set(0, 0);
		var state = this.state;
		this.effects.each(function(ef) {
			ef.func.end(0);
		});

		return this;
	},

	set: function(from, to) {
		this.state  = from;
		this.target = to;
		this._clear();
	},

	execEffects: function() {
		var state = this.state;
		this.effects.each(function(ef) {
			ef.func.exec((ef.reverse === true) ? 1 - state : state);
		});
	},

	_run: function() {
		var state = this.state;
		if (state == 1 || state == 0) {
			this.effects.each(function(ef) {
				ef.func.start((ef.reverse === true) ? 1 - state : state);
			});
		}
		this.timer = setInterval(Sabel.Function.bind(this._exec, this), this.interval);
	},

	_exec: function() {
		var mv = (this.state > this.target ? -1 : 1) * this.step;
		this.state += mv;
		if (this.state >= 1 || this.state <= 0) {
			this.set(this.target, this.target);
		}

		this.execEffects();

		if (this.state == 1 || this.state == 0) {
			var state = this.state;
			this.effects.each(function(ef) {
				ef.func.end((ef.reverse === true) ? 1 - state : state);
			});
			this.callback(!this.state);
		}
	},

	_clear: function() {
		clearInterval(this.timer);
		this.timer = null;
	}
};

Sabel.Effect.Chain = new Sabel.Class({
	init: function(options) {
		options = options || {};

		this.effects = new Array();
		this.current = 0;
		this.state   = 0;
		this.callback = options.callback || function() {}
	},

	add: function(effect) {
		if (!(effect instanceof Sabel.Effect)) {
			effect = new Sabel.Effect().add(effect);
		}
		if (arguments.length > 1) {
			for (var i = 1, len = arguments.length; i < len; ++i) {
				effect.add(arguments[i]);
			}
		}
		effect.setCallback(this._callback, this);
		this.effects.push(effect);

		return this;
	},

	play: function() {
		if (this.state === 0) {
			this.state = 1;
			this.effects[this.current].play(true);
		}
	},

	pause: function() {
		this.effects[this.current].pause();
		return this;
	},

	resume: function() {
		this.effects[this.current].resume();
		return this;
	},

	_callback: function() {
		this.state = 0;
		if (++this.current === this.effects.length) {
			this.current = 0;
			this.callback();
		} else {
			this.play();
		}
	}
});

Sabel.Cookie = {
	set: function(key, value, option)
	{
		var cookie = key + "=" + escape(value);

		if (typeof option !== "object") option = { expire: option };

		if (option.expire) {
			var date = new Date();
			date.setTime(date.getTime() + option.expire * 1000);
			cookie += "; expires=" + date.toGMTString();
		}
		if (option.domain) cookie += "; domain=" + option.domain;
		if (option.path)   cookie += "; path="   + options.path;
		if (option.secure) cookie += "; secure";

		document.cookie = cookie;
	},

	get: function(key)
	{
		key = key + "=";
		var cs = document.cookie.split(";");
		for (var i = 0; i < cs.length; i++) {
			var c = cs[i].replace(/ /g, "");
			if (c.indexOf(key) === 0) return unescape(c.substring(key.length));
		}

		return null;
	},

	unset: function(key)
	{
		this.set(key, "", -1);
	},

	clear: function()
	{
		var cs = document.cookie.split(";");
		for (var i = 0, len = cs.length; i < len; i++) {
			Sabel.Cookie.unset(cs[i].match(/\w+/));
		}
	}
};

Sabel.dump = function(element, limit)
{
	var br = (Sabel.UserAgent.isIE) ? "\r" : "\n";
	limit  = limit || 1;

	output = document.createElement("pre");
	output.style.border = "1px solid #ccc";
	output.style.color  = "#333";
	output.style.background = "#fff";
	output.style.margin = "5px";
	output.style.padding = "5px";

	output.appendChild(document.createTextNode((function(element, ind) {
		var space = new Sabel.String("  ");
		var indent = space.times(ind);
		if (typeof element === "undefined") {
			return "undefined";
		} else if (Sabel.Element.isString(element)) {
			return "string(" + element.length + ') "' + element + '"';
		} else if (Sabel.Element.isNumber(element)) {
			return "int(" + element + ")";
		} else if (Sabel.Element.isBoolean(element)) {
			return "bool(" + element + ")";
		} else if (Sabel.Element.isFunction(element)) {
			return element.toString().replace(/(\n)/g, br + space.times(ind - 1));
		} else if (Sabel.Element.isAtomic(element)) {
			return element;
		} else {
			if (ind > limit) return element + "...";

			var buf = new Array();
			buf[buf.length] = "object() {";
			for (var key in element) {
				try {
					buf[buf.length] = indent + '["' + key + '"]=>' + br +
					indent + arguments.callee(element[key], ind+1);
				} catch (e) {}
			}
			buf[buf.length] = space.times(ind - 1)+ "}";
			return buf.join(br);
		}
	})(element, 1)));

	document.body.appendChild(output);
};

Sabel.Form.Elements = {
	input: function(element) {
		switch(element.type.toLowerCase()) {
		case "radio":
		case "checkbox":
			return (element.checked) ? element.value : null;
		case "submit":
		case "reset":
		case "button":
		case "image":
			return null;
		default:
			return element.value;
		}
	},

	select: function(element) {
		switch (element.type) {
		case "select-multiple":
			var buf = [];
			Sabel.Array.each(element.options, function(el) {
				if (el.selected) buf[buf.length] = el.value;
			});
			return buf;
		default:
			return element.value;
		}
	},

	textarea: function(element) {
		return element.value;
	}
};
Sabel.Validator.Int = function(option) {
	option = option || {};
	return function(value) {
		if (option.min && value && value < option.min) return false;
		if (option.max && value && value > option.max) return false;

		// 8進数が動かない。
		if (value == "" || parseInt(value) == value) return true;

		return false;
	};
};

Sabel.Validator.Float = function(option) {
	option = option || {};
	return function(value) {
		if (option.min && value && value < option.min) return false;
		if (option.max && value && value > option.max) return false;

		if (value == "" || parseFloat(value) == value) return true;
		return false;
	};
};

Sabel.Validator.String = function(option) {
	return function(value) {
		if (option.min && value.length < option.min) return false;
		if (option.max && value.length > option.max) return false;

		return true;
	};
};

Sabel.Validator.Must = function() {
	return function(value) {
		if (value === "") return false;
		return true;
	};
};

Sabel.Validator.Regex = function(pattern) {
	return function(value) {
		if (value.search(pattern) !== -1) return false;
		return true;
	};
};
Sabel.Validator.Element = function(element) {
	this.validations = new Array();
	this.element = Sabel.get(element, false);
	this.errMsg = "";
};
Sabel.Validator.Element.prototype = {
	add: function(func, errMsg) {
		this.validations.push({
			func: func,
			errMsg: errMsg
		});

		return this;
	},

	validate: function() {
		var validations = this.validations;
		for (var i = 0, v; v = validations[i]; i++) {
			if (v.func(this.element.value) === false) {
				this.errMsg = v.errMsg;
				return false;
			}
		}
		return true;
	}
};

Sabel.Effect.Fade = function() {
	this.init.apply(this, arguments);
};
Sabel.Effect.Fade.prototype = {
	init: function(element) {

		this.element = Sabel.get(element, false);
	},

	start: function(state) {
		this.exec(state);
		Sabel.Element.show(this.element);
	},

	end: function(state) {
		if (state === 0) {
			this.exec(1);
			Sabel.Element.hide(this.element);
		}
	},

	exec: function(state) {
		Sabel.Element.setOpacity(this.element, state);
	}
};


Sabel.Effect.Slide = function() {
	this.init.apply(this, arguments);
};

Sabel.Effect.Slide.prototype = {
	init: function(element) {
		this.element = Sabel.get(element, false);
	},

	start: function(state) {
		var elm = this.element;

		this.elementHeight   = Sabel.Element.getHeight(elm);

		this.styleHeight     = elm.style.height || "";
		this.defaultPosition = Sabel.Element.getStyle(elm, "position");
		this.defaultOverflow = Sabel.Element.getStyle(elm, "overflow");

		var height = state * this.elementHeight;
		var style = {
			overflow: "hidden",
			display: "",
			height: height
		};
		if (this.defaultPosition !== "absolute") style.position = "relative";
		Sabel.Element.setStyle(elm, style);

		this.exec(state);
	},
	end: function(state) {
		var style = {
			position: this.defaultPosition,
			overflow: this.defaultOverflow,
			height: this.styleHeight
		};

		if (state === 0) style.display = "none";
		Sabel.Element.setStyle(this.element, style);
	},

	exec: function(state) {
		var element = this.element;
		var height = state * this.elementHeight;
		Sabel.Element.setStyle(element, {height: height});
	}
};

Sabel.Effect.Highlight = new Sabel.Class({
	init: function(element, to) {
		element = this.element = Sabel.get(element);
		var from = this.from    = Sabel.CSS.getRGB(element.getBackGroundColor());
		to = this.to      = Sabel.CSS.getRGB(to || "yellow");
		this.step  = [from[0] - to[0], from[1] - to[1], from[2] - to[2]];
	},

	start: function(state) {
		this.exec(state);
	},

	end: function(state) {},

	exec: function(state) {
		var r = this.to[0] + parseInt(this.step[0] * state);
		var g = this.to[1] + parseInt(this.step[1] * state);
		var b = this.to[2] + parseInt(this.step[2] * state);
		this.element.style.backgroundColor = Sabel.CSS.rgbToHex([r,g,b]);
	}
});


Sabel.DragAndDrop = function() { this.initialize.apply(this, arguments); };

Sabel.DragAndDrop.prototype = {
	initialize: function(element, options)
	{
		options = options || {};
		element = Sabel.get(element);
		var handle = options.handle ? Sabel.get(options.handle) : element;

		handle.style.cursor = options.cursor || "move";

		this.element  = element;
		this.observes = new Array();
		this.initPos  = Sabel.Element.getOffsetPositions(element);
		this.setOptions(options || {});

		var self = this;
		this.observe(handle, "mousedown", function(e) { self.mouseDown(e) });
	},

	setOptions: function(o)
	{
		this.options = {
			startCallback: o.startCallback ? o.startCallback : null,
			endCallback:   o.endCallback   ? o.endCallback   : null,
			moveCallback:  o.moveCallback  ? o.moveCallback  : null,
			bsc: o.bsc ? o.bsc : null,
			rangeX: null, rangeY: null
		}
		if (o.x) this.setXConst(o.x);
		if (o.y) this.setYConst(o.y);
	},

	setXConst: function(range)
	{
		if (range.length < 2) return null;

		var startX = this.initPos.left;
		this.options.rangeX = {min: startX - range[0], max: startX + range[1]};
		if (range[2] !== undefined) this.options.gridX = range[2];
		return this;
	},

	setYConst: function(range)
	{
		if (range.length < 2) return null;

		var startY = this.initPos.top;
		this.options.rangeY = {min: startY - range[0], max: startY + range[1]};
		if (range[2] !== undefined) this.options.gridY = range[2];
		return this;
	},

	setGrid: function(grid)
	{
		this.options.gridX = grid[0];
		this.options.gridY = grid[1];
	},

	observe: function(element, handler, func, useCapture)
	{
		if (this.observes[handler]) return;

		Sabel.Element.observe(element, handler, func);
		this.observes[handler] = func;
	},

	stopObserve: function(element, handler)
	{
		Sabel.Element.stopObserve(element, handler, this.observes[handler]);
		delete this.observes[handler];
	},

	mouseDown: function(e)
	{
		Sabel.Event.preventDefault(e);

		var element = this.element;
		if (this.options.startCallback !== null) this.options.startCallback(element, e);

		if (element.getStyle("position") !== "absolute") {
			element.style.top = element.getOffsetTop() + "px";
			element.style.left = element.getOffsetLeft() + "px";
			var dimensions = element.getDimensions(true);
			element.style.height = dimensions.height + "px";
			element.style.width  = dimensions.width  + "px";
			element.style.position = "absolute";
		}

		this.startPos = Sabel.Element.getOffsetPositions(element);
		this.startX   = e.clientX;
		this.startY   = e.clientY;

		element.style.zIndex = "10000";

		var self = this;
		this.observe(document, "mousemove", function(e) { self.mouseMove(e) });
		this.observe(document, "mouseup", function(e) { self.mouseUp(e) });

		//if (this.options.startCallback !== null) this.options.startCallback(element, e);
		if (this.options.bsc !== null) this.options.bsc(element, e);
	},

	mouseUp: function(e)
	{
		e = e || window.event;
		this.element.style.zIndex = "1";
		this.stopObserve(document, "mousemove");
		this.stopObserve(document, "mouseup");

		if (this.options.endCallback !== null) this.options.endCallback(this.element, e);
		return false;
	},

	mouseMove: function(e)
	{
		Sabel.Event.preventDefault(e);

		var options = this.options;
		var element = this.element;
		var moveX = e.clientX - this.startX;
		var moveY = e.clientY - this.startY;
		if (options.gridX) moveX -= (moveX % options.gridX);
		if (options.gridY) moveY -= (moveY % options.gridY);

		var xPos = this.startPos.left + moveX;
		var yPos = this.startPos.top  + moveY;

		if (options.rangeX !== null) xPos = Math.max(options.rangeX.min, Math.min(options.rangeX.max, xPos));
		if (options.rangeY !== null) yPos = Math.max(options.rangeY.min, Math.min(options.rangeY.max, yPos));

		element.style.top  = yPos + "px";
		element.style.left = xPos + "px";

		if (this.options.moveCallback !== null) this.options.moveCallback(this.element, e);
		return false;
	}
};

Sabel.Widget = {};

Sabel.Widget.Overlay = function(option) {
	option = option || {};

	if (!option.backgroundColor) option.backgroundColor = "#000";
	if (!option.opacity) option.opacity = 70;
	if (!option.zIndex)  option.zIndex  = 100;

	var div = document.createElement("div");
	if (option.id) div.setAttribute("id", option.id);

	div.style.cssText += "; background-color: " + option.backgroundColor
										 + "; position: absolute; top: 0px; left: 0px; opacity: "
										 + (option.opacity / 100) + "; -moz-opacity: "
										 + (option.opacity / 100) + "; filter: alpha(opacity="
										 + option.opacity + "); z-index: " + option.zIndex + ";";

	this.div = Sabel.Element(div);;
	document.body.appendChild(div);
	this.show();
};

Sabel.Widget.Overlay.prototype = {
	div: null,

	show: function() {
		this.setStyle();
		this.div.show();
	},

	hide: function() {
		this.div.hide();
	},

	setStyle: function() {
		var height = Sabel.Window.getScrollHeight();
		var width  = Sabel.Window.getScrollWidth();

		this.div.style.width  = width  + "px";
		this.div.style.height = height + "px";
	}
};


Sabel.Widget.Calendar = function() {
	this.initialize.apply(this, arguments);
};

Sabel.Widget.Calendar.prototype = {
	OneDay: (1000 * 60 * 60 * 24),
	WeekDays: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],

	date:          null,
	targetElement: null,
	rootElement:   null,
	options:       null,
	
	selectBox: null,

	initialize: function(targetElement, options)
	{
		this.date = new Date();
		this.targetElement = Sabel.get(targetElement);
		var rootElement = new Sabel.Element("div");
		var childElement = new Sabel.Element("div");
		childElement.addClass("sbl_calendarFrame");
		rootElement.appendChild(childElement);
		this.rootElement = rootElement;
		this.options = options || {};

		this.rootElement.hide();
		this.targetElement.insertNextSibling(this.rootElement);
		this.targetElement.observe("click", this.clickHandler, false, this);
	},

	prevMonth: function(evt)
	{
		this.date.setMonth(this.date.getMonth() - 1);
		this.render();
		Sabel.Event.preventDefault(evt);
	},

	nextMonth: function(evt)
	{
		this.date.setMonth(this.date.getMonth() + 1);
		this.render();
		Sabel.Event.preventDefault(evt);
	},

	mouseOver: function(evt)
	{
		Sabel.Element.addClass(Sabel.Event.getTarget(evt), "hover");
	},

	mouseOut: function(evt)
	{
		Sabel.Element.removeClass(Sabel.Event.getTarget(evt), "hover");
	},

	mouseDown: function(evt)
	{
		var target = Sabel.Event.getTarget(evt);
		if (Sabel.Element.hasClass(target, 'selectable') === false) {
			return;
		}
		var opt = this.options; // alias

		var d = this.date; // alias
		var cN = target.className.split(' ')[0];
		if (opt.callback) {
			opt.callback([d.getFullYear(), d.getMonth()+1, cN.substr(3)]);
		} else {
			this.targetElement.value = new Sabel.String("#{year}/#{month}/#{day}").format({
				year: d.getFullYear(),
				month: d.getMonth() + 1,
				day: cN.substr(3)
			});
		}

		var selected = Sabel.Dom.getElementsByClassName("selected", this.rootElement, true);
		if (selected.length > 0)
			Sabel.Element.removeClass(selected[0], "selected");

		Sabel.Element.addClass(target, "selected");

		this.rootElement.hide();
	},
	
	clickHandler: function()
	{
		if (this.rootElement.getFirstChild().innerHTML === "") {
			var date = this.targetElement.value || this.options.date || "";
			date = new Date(date.replace("-", "/", "g"));
			if (isNaN(date.getTimezoneOffset()) === false) {
				this.render(date);
			} else {
				this.render(new Date());
			}
		} else {
			this.show();
		}
	},
	
	_changeDate: function()
	{
		var date = new Array();
		Sabel.Array.each(this.selectBox, function(el) {
			date.push(el.value);
		});
		date.push("1");
		this.date = new Date(date.join("/"));
		this.render();
	},

	render: function(date)
	{
		var year, month, day, i;
		if (date !== undefined) {
			year  = date.getFullYear();
			month = date.getMonth();
			day   = date.getDate();
		} else {
			year  = this.date.getFullYear();
			month = this.date.getMonth();
		}
		date = this.date = new Date(year, month, 1);

		var tmpDate = new Date();
		tmpDate.setTime(date.getTime() - (this.OneDay * date.getDay()));

		var time = tmpDate.getTime();
		var html = [];

		html.push('<div class="sbl_calendar">');
		html.push('  <div class="sbl_cal_header">');
		html.push('    <a class="sbl_page_l">&#160;</a>');
		if (this.options.useSelectBox === true) {
			var yearSelect = new Array();
			yearSelect.push("<select>");
			for (i = 2000; i < 2020; ++i) {
				if (i === year) {
					yearSelect.push('<option value="' + i + '" selected="selected">' + i + '</option>');
				} else {
					yearSelect.push('<option value="' + i + '">' + i + '</option>');
				}
			}
			yearSelect.push("</select>");
			var monthSelect = new Array();
			monthSelect.push("<select>");
			for (i = 1; i <= 12; ++i) {
				if (i === month + 1) {
					monthSelect.push('<option value="' + i + '" selected="selected">' + i + '</option>');
				} else {
					monthSelect.push('<option value="' + i + '">' + i + '</option>');
				}
			}
			monthSelect.push("</select>");
			html.push('    <span>&#160;' + yearSelect.join("\n") + "年" + monthSelect.join("\n") + '月&#160;</span>');
		} else {
			html.push('    <span>&#160;' + year + '年' + (month+1) + '月&#160;</span>');
		}
		html.push('    <a class="sbl_page_r">&#160;</a>');
		html.push('  </div>');
		html.push('  <div class="sbl_cal_weekdays">');
		for (var i=0; i<this.WeekDays.length; i++) {
			html.push('<div>'+this.WeekDays[i]+'</div>');
		}
		html.push('  </div>');

		html.push('  <div class="sbl_cal_days">');
		for (i = 0; i < 42; i++) {
			tmpDate.setTime(time + (this.OneDay * i));
			var cDate = tmpDate.getDate();

			if (tmpDate.getMonth() === month) {
				if (tmpDate.getDay() === 0) {
					html.push("<div class='day" + cDate + " selectable sunday'>" + cDate + "</div>");
				} else if (tmpDate.getDay() === 6) {
					html.push("<div class='day" + cDate + " selectable saturday'>" + cDate + "</div>");
				} else {
					html.push("<div class='day" + cDate + " selectable'>" + cDate + "</div>");
				}
			} else {
				html.push("<div class='nonselectable'>" + cDate + "</div>");
			}
		}
		html.push('  </div>');
		html.push('</div>');
		html.push('<a class="sbl_cal_close">Close</a>');

		this.rootElement.getFirstChild().innerHTML = html.join("\n");
		this.rootElement.show();

		var find = Sabel.Dom.getElementsByClassName;

		var close = find("sbl_cal_close", this.rootElement, true).item(0);
		Sabel.Element.observe(close, "click", this.hide, false, this);

		var l = find("sbl_page_l", this.rootElement, true).item(0);
		Sabel.Element.observe(l, "click", this.prevMonth, false, this);

		var r = find("sbl_page_r", this.rootElement, true).item(0);
		Sabel.Element.observe(r, "click", this.nextMonth, false, this);

		var el = find("sbl_cal_days", this.rootElement, true).item(0);
		Sabel.Element.observe(el, "mouseover", Sabel.Function.bind(this.mouseOver, this));
		Sabel.Element.observe(el, "mouseout", Sabel.Function.bind(this.mouseOut, this));
		Sabel.Element.observe(el, "mousedown", Sabel.Function.bindWithEvent(this.mouseDown, this));
		
		if (this.options.useSelectBox === true) {
			this.selectBox = Sabel.find(".sbl_cal_header select");
			this.selectBox.observe("change", this._changeDate, false, this);
		}

		if (day !== undefined) {
			var selected = Sabel.Dom.getElementsByClassName("selected", this.rootElement, true);
			if (selected.length > 0)
				Sabel.Element.removeClass(selected[0], "selected");

			Sabel.Element.addClass(find("day"+day, this.rootElement)[0], "selected");
		}

		this.show();
	},

	show: function()
	{
		this.rootElement.show();
	},

	hide: function()
	{
		this.rootElement.hide();
	}
}


Sabel.Widget.Dropdown = new Sabel.Class({
	hoverElements: null,
	lastElement: null,
	event: null,
	moveTimer: null,
	leaveTimer: null,

	init: function() {
		var root = Sabel.find("div.sbl_dropdown > ul.sbl_dropdown_list").item(0);
		var elms = root.find("> li");

		this.setup();

		elms.each(function(el) {
			el.addClass("root");
		});

		root.find("li li").each(function(el) {
			if (el.getFirstChild("UL")) el.addClass("icon");
		});

		var self = this;
		root.observe("mouseenter", function() {
			if (self.leaveTimer) clearTimeout(self.leaveTimer);
			self.event = new Sabel.Event(document, "mousemove", function(e) {
				self.targetElm = Sabel.Event.getTarget(e);
				if (self.moveTimer) clearTimeout(self.moveTimer);
				self.moveTimer = setTimeout(function() {
					self.moveHandler();
				}, 10);
			}, false, self);
		});
		root.observe("mouseleave", this.leaveHandler, false, this);
		root.observe("mousedown", this.clickHandler, false, this);
	},

	moveHandler: function() {
		var el = this.targetElm, child;
		if (el.tagName == "SPAN") el = el.parentNode;
		if (el.tagName !== "LI" || this.lastElement === el) return;
		this.lastElement = el = new Sabel.Element(el);

		this.hoverElements = Sabel.Array.inject(this.hoverElements, function(elm) {
			if (Sabel.Element.isContain(elm, el) === false) {
				elm.style.display = "none";
				return false;
			}
			return true;
		});

		Sabel.find(".hover").each(function(elm) {
			if (elm.isContain(el) === false)
				elm.removeClass("hover");
		});
		el.addClass("hover");

		if ((child = el.getFirstChild("UL")) === null) return;

		if (el.hasClass("root")) {
			child.setStyle({
				display: "block",
				top: (el.getOffsetTop() + el.getHeight(true)) + "px",
				left: el.getOffsetLeft() + "px"
			});
		} else {
			var borderWidth = parseInt(el.getParentNode().getStyle("borderTopWidth"));
			child.setStyle({
				display: "block",
				top: el.getOffsetTop() - borderWidth + "px",
				left: el.getWidth() + "px"
			});
		}

		this.hoverElements.unshift(child);
	},

	leaveHandler: function(e) {
		var self = this;
		this.leaveTimer = setTimeout(function() {
			Sabel.Array.each(self.hoverElements, function(el) {
				el.hide();
			});
			Sabel.find(".hover").each(function(el) {
				el.removeClass("hover");
			});
			self.setup();
		}, 250);
	},

	clickHandler: function() {
		var el = new Sabel.Element(this.targetElm);
		var href = (el.getFirstChild("span") || el).getAttribute("href");;
		if (href !== null) location.href = href;
	},

	setup: function() {
		this.hoverElements = new Sabel.Array();
		this.lastElement   = null;
		this.moveTimer     = null;
		this.leaveTimer    = null;
		if (this.event) this.event.stop();
	}
});
