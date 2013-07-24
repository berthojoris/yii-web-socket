/**
 * Created with JetBrains PhpStorm.
 * User: once
 * Date: 6/24/13
 * Time: 6:20 PM
 * To change this template use File | Settings | File Templates.
 */
if(typeof JSON!=='object'){JSON={}}(function(){'use strict';function f(n){return n<10?'0'+n:n}if(typeof Date.prototype.toJSON!=='function'){Date.prototype.toJSON=function(key){return isFinite(this.valueOf())?this.getUTCFullYear()+'-'+f(this.getUTCMonth()+1)+'-'+f(this.getUTCDate())+'T'+f(this.getUTCHours())+':'+f(this.getUTCMinutes())+':'+f(this.getUTCSeconds())+'Z':null};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(key){return this.valueOf()}}var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},rep;function quote(string){escapable.lastIndex=0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+string+'"'}function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key)}if(typeof rep==='function'){value=rep.call(holder,key,value)}switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null'}gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null'}v=partial.length===0?'[]':gap?'[\n'+gap+partial.join(',\n'+gap)+'\n'+mind+']':'['+partial.join(',')+']';gap=mind;return v}if(rep&&typeof rep==='object'){length=rep.length;for(i=0;i<length;i+=1){if(typeof rep[i]==='string'){k=rep[i];v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v)}}}}else{for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v)}}}}v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+mind+'}':'{'+partial.join(',')+'}';gap=mind;return v}}if(typeof JSON.stringify!=='function'){JSON.stringify=function(value,replacer,space){var i;gap='';indent='';if(typeof space==='number'){for(i=0;i<space;i+=1){indent+=' '}}else if(typeof space==='string'){indent=space}rep=replacer;if(replacer&&typeof replacer!=='function'&&(typeof replacer!=='object'||typeof replacer.length!=='number')){throw new Error('JSON.stringify');}return str('',{'':value})}}if(typeof JSON.parse!=='function'){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==='object'){for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v}else{delete value[k]}}}}return reviver.call(holder,key,value)}text=String(text);cx.lastIndex=0;if(cx.test(text)){text=text.replace(cx,function(a){return'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4)})}if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,''))){j=eval('('+text+')');return typeof reviver==='function'?walk({'':j},''):j}throw new SyntaxError('JSON.parse');}}}());
var YiiWebSocket = {

	listen : function (host, path) {

		return new function () {

			this.host = host;
			this.path = path;

			if (host.indexOf('ws://') == -1) {
				host = 'ws://' + host;
			}
			host += path || '/';

			/**
			 *
			 * @type {WebSocket}
			 */
			var socket = new WebSocket(host);

			/**
			 * Events container
			 *
			 * @type {Object}
			 */
			var events = {};

			var callbacks = {

				_fns : {},

				register : function (event, fn) {
					var date = new Date();
					var id = event + '|' + date.getTime() + '|' + date.getMilliseconds();
					this._fns[id] = fn;
					return id;
				},

				remove : function (id) {
					if (this.has(id)) {
						delete this._fns[id];
					}
				},

				get : function (id) {
					var callback = null;
					if (this.has(id)) {
						callback = this._fns[id];
						this.remove(id);
					}
					return callback;
				},

				has : function (id) {
					return this._fns.hasOwnProperty(id);
				}
			};

			var isNormalClose = null;

			/**
			 * Set event listener
			 *
			 * @param event
			 * @param callback
			 */
			this.on = function (event, callback) {
				if (!events.hasOwnProperty(event)) {
					events[event] = [];
				}
				events[event].push(callback);
				return this;
			};

			/**
			 * Emit remote server with event
			 */
			this.emit = function () {
				var args = [];
				Array.prototype.push.apply( args, arguments );
				var event = args.shift();
				var context = createEventContext(event, args);
				sendPackage(context);
			};

			/**
			 * Close socket connection
			 */
			this.close = function () {
				isNormalClose = true;
				socket.close();
			};

			/**
			 * Attach event on establish socket connection
			 *
			 * Fired when connection is established
			 *
			 * @param callback
			 * @return {*}
			 */
			this.onConnection = function (callback) {
				return this.on('connection', callback);
			};

			/**
			 * Attach event on close
			 *
			 * Fired when connection closed
			 *
			 * @param callback
			 * @return {*}
			 */
			this.onClose = function (callback) {
				return this.on('close', callback);
			};

			/**
			 * Fired when connection was lost and system try reconnect to the server
			 *
			 * @param callback
			 * @return {*}
			 */
			this.onReconnect = function (callback) {
				return this.on('reconnect', callback);
			};

			/**
			 * Return true if socket opened
			 *
			 * @return {Boolean}
			 */
			this.isOpened = function (){
				return socket.readyState == 1;
			};

			/**
			 *
			 * Check connecting state
			 *
			 * @return {Boolean}
			 */
			this.isConnecting = function () {
				return socket.readyState == 0;
			};

			/**
			 * Check if connection closing
			 *
			 * @return {Boolean}
			 */
			this.isClosing = function () {
				return socket.readyState == 2;
			};

			/**
			 *
			 * @return {Boolean}
			 */
			this.isClosed = function () {
				return socket.readyState == 3;
			};

			var self = this;

			var emitLocalEvent = function () {
				var args = [];
				Array.prototype.push.apply( args, arguments );
				var event = args.shift();
				if (events.hasOwnProperty(event)) {
					for (var i in events[event]) {
						events[event][i].apply(self, args);
					}
				}
			};

			var sendPackage = function (context) {
				if (self.isOpened()) {
					var frame = JSON.stringify(context);
					socket.send(frame);
				}
			};

			var createContext = function (arguments, event) {
				var context = {
					arguments : []
				};
				for (var i in arguments) {
					context.arguments[i] = {
						type : typeof arguments[i],
						value : arguments[i]
					};
					if (typeof arguments[i] == 'function') {
						context.arguments[i].type = 'callback';
						context.arguments[i].value = callbacks.register(event, arguments[i]);
					}
				}
				return context;
			};

			var createEventContext = function (event, arguments) {
				var context = createContext(arguments, event);
				context['event'] = event;
				return context;
			};

			var createCallbackContext = function (id, arguments) {
				var context = createContext(arguments);
				context['callback'] = id;
				return context;
			};

			var handleSocketData = function (data) {
				var data = JSON.parse(data);
				var args = [];
				for (var i in data['arguments']) {
					args.push(parseArgument(data['arguments'][i]));
				}
				if (data['event']) {
					var emitData = args;
					emitData.unshift(data.event);
					emitLocalEvent.apply(self, emitData);
				} else if (data['callback']) {
					if (callbacks.has(data['callback'])) {
						callbacks.get(data['callback']).apply(self, args);
					}
				}
			};

			var parseArgument = function (argument) {
				switch (argument['type']) {

					case 'callback':
						return createCallbackWrapper(argument['value']);
						break;

					default:
						return argument['value'];
						break;
				}
			};

			var createCallbackWrapper = function (id) {
				return function () {
					var args = [];
					Array.prototype.push.apply( args, arguments );
					var context = createCallbackContext(id, args);
					sendPackage(context);
				};
			};

			var closeIntervalId;

			/**
			 * Handle open
			 */
			socket.onopen = function () {
				if (self.isOpened()) {
					emitLocalEvent('connection', self);
				}
			};

			/**
			 * Handle close
			 */
			socket.onclose = function () {
				emitLocalEvent('close', self, isNormalClose);
				if (isNormalClose === null) {
					recreateConnection();
				}
			};

			socket.onerror = function (e) {
				console.log(e);
			};

			socket.onmessage = function (message) {
				handleSocketData(message.data);
			};

			var isReconnecting = false;
			var reconnectTime = 100;

			var recreateConnection = function () {
				setTimeout(function () {
					self.emit('reconnect');
					var sock = new WebSocket(host);
					sock.onopen = function () {
						if (this.readyState == 1) {
							sock.onopen = socket.onopen;
							socket = sock;
							socket.onopen();
							reconnectTime = 100;
						}
					};
					sock.onclose = socket.onclose;
					sock.onmessage = socket.onmessage;
					if (reconnectTime <= 10000) {
						reconnectTime *= 10;
					}
				}, reconnectTime);
			};
		};
	}
};