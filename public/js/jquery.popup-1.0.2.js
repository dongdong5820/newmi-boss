/**
 * @license Copyright 2013-2014
 * jQuery popup v1.0.2
 *
 * @author yutlee.cn@gmail.com
 * Date 2014-1-17
 * Update 2014-1-22 --v1.0.2
 */

(function($, plugin) {
	var defs = {
		name: plugin,
		width: 400,
		height: 300,
		animTime: 300
	};

	$.fn[defs.name] = function(options) {
		return this.each(function() {
			new box($(this), options);
		});
	};
	
	function box(element, options) {
		this.init(element, options);
		element.data(this.options.name, this);
	}

	box.prototype = {
		init: function(element, options) {
			var that = this;

			options = that.options = $.extend(true, {}, that.options, options);
			that.element = element;

			that.open();
		},
		options: defs,
		open: function() {
			var that = this,
				options = that.options,
				len = $.fn[defs.name].win.length,
				titleDiv = $('<div class="title" />'),
				closeDiv = $('<div class="close" tabIndex="0">×</div>'),
				contentDiv = $('<div class="content" />'),
				popupDiv = $('<div class="popup" />'),
				zIndex,
				className = options.className;

			if(className && $.trim(className) !== '') {
				popupDiv.addClass(className);
			}

			if(len >= 1) {
				zIndex = parseInt($.fn[defs.name].shadow.attr('zIndex'), 10) + 2;
				$.fn[defs.name].shadow.css({'z-index': zIndex}).attr('zIndex', zIndex);
			}else {
				$.fn[defs.name].shadow = $('<div class="shadow" />').appendTo('body');
				zIndex = parseInt($.fn[defs.name].shadow.css('z-index'), 10) || 1;
				$.fn[defs.name].shadow.attr('zIndex', zIndex);
			}

			closeDiv = closeDiv.appendTo(popupDiv);
			if(options.title && $.trim(options.title) !== '') {
				titleDiv = titleDiv.html(options.title).appendTo(popupDiv);
			}
			contentDiv = contentDiv.appendTo(popupDiv);
			popupDiv = popupDiv.appendTo('body').css({'z-index': zIndex + 1, 'opacity': 0});

			that.setStyle(popupDiv);
			//contentDiv.height(popupDiv.height() - titleDiv.outerHeight());

			$.fn[defs.name].win.unshift(popupDiv);

			closeDiv.one('click', function(e) {
				that.close();
			});

			if($.isFunction(options.insert)) {
				options.insert.call(that, that, popupDiv, contentDiv, titleDiv, closeDiv);
			}
		},
		close: function() {
			var that = this,
				len = $.fn[defs.name].win.length,
				last;

			if(len > 0) {
				last = $.fn[defs.name].win.shift();
				last.remove();
				if(len === 1) {
					$.fn[defs.name].shadow.remove();
				}else {
					zIndex = parseInt($.fn[defs.name].shadow.css('z-index'), 10) - 2;
					$.fn[defs.name].shadow.attr('zIndex', zIndex);
					$.fn[defs.name].shadow.css({'z-index': zIndex});
				}
				if($.isFunction(that.options.closeCallback)) {
					that.options.closeCallback.call(that, that);
				}
			}
		},
		closeAll: function() {
			var that = this,
				len = $.fn[defs.name].win.length;

			for(var i = 0; i < len; i++) {
				that.close();
			}
		},
		setStyle: function(el) {
			var that = this,
				width = that.options.width,
				height = that.options.height,
				full = that.options.full;
			if(el.css('position') !== 'absolute') {
				el.css({'position': 'absolute'})	
			}
			if(full) {
				var top = full.top,
					right = full.right,
					bottom = full.bottom,
					left = full.left;
				top = (top || top == 0) ? top : 'auto';
				right = (right || right == 0) ? right : 'auto';
				bottom = (bottom || bottom == 0) ? bottom : 'auto';
				left = (left || left == 0) ? left : 'auto';
				el.css({'width': 'auto', 'height': 'auto', 'top': top, 'right': right, 'bottom': bottom, 'left': left});
				$('html').css({'overflow': 'hidden'});
				el.animate({'opacity': 1}, that.options.animTime);
			}else {
				if(width && width === 'auto') {
					el.css({'width': el.width()});
				}else {
					el.width(width);	
				}	
				if(height && height === 'auto') {
					el.css({'height': el.height(), 'position': 'absolute'});
				}else {
					el.height(height);
					el.children('.content').height(height - el.children('.title').outerHeight());
				}
				that.position(el);
			}
		},
		position: function(el) {
			var that = this,
				winWidth = $(window).width(),
				winHeight = $(window).height(),
				left = (winWidth - el.outerWidth()) * .5,
				top = (winHeight - el.outerHeight()) * .5;
			left = left > 0 ? left : 0;
			top = top > 0 ? top : 0;
			el.animate({'left': left, top: top}, that.options.animTime, function() {
				el.animate({'opacity': 1}, that.options.animTime);
			});
		},
		destory: function() {
			//do
		}
	};

	$.fn[defs.name].defs = defs;
	$.fn[defs.name].win = [];
	$.extend($.fn[defs.name], box.prototype);

})(jQuery, 'popup');

(function($, mm) {
	var check, mConfirm;
	$.fn.popup.defs.insert = function(box, popup, content, title, close) {
		var options = {
	            url: box.options.url,
	            mod: content,
	            success: function(data, ts, xhr) {
	            	check = mm.check || function() {};
	            	mConfirm = mm.confirm || function() {};
	                this.mod.html(data);

	                var repos = 0;
	                if(box.options.height == 'auto') {
	                	popup.height('');
	                	content.height('');
	                	repos = 1;
	                } 
	                if(box.options.width == 'auto') {
	                	popup.width('');
	                	repos = 1;
	                }
	                if(repos) {
	                	$().popup.position(popup);
	                }
	                callback = $.fn.popup.defs.callback;
					if($.isFunction(callback)) {
						callback.call(box, content, data);
					}
	            }
	        };
	   	mm.ajax(options);

		$.fn.popup.refresh = function() {
			$.fn.popup.defs.insert(box, $.fn.popup.win[0].find('.content'));
		}

		var popupResize;
		$(window).bind('resize.popup', function(e) {
			clearTimeout(popupResize);
			popupResize = setTimeout(function() {
				$.fn.popup.position(popup);
			}, 500);
		});
	};

	$.fn.popup.defs.closeCallback = function(argument) {
		mm.confirm = mConfirm;
		mm.check = check;
	}
})(jQuery, mm);