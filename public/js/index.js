(function($, path, mm, undefined) {

	function ajaxError(ts) {
		if(ts == 'timeout') {
			tooltip.tip('error', '加载超时，请刷新重新加载');
		}else {
			tooltip.tip('error', '异常错误，请刷新重新加载');
		}
	}

	mm.ajaxSetting.error = function(xhr, ts, error) {
		ajaxError(ts);
	};

	mm.ajaxFormSetting.error = function(xhr, ts, error) {
		ajaxError(ts);
	}

	$(document).ready(function() {
		var menuRequest;//记录最后一次ajax对象
		var kMainContent = $('#k-maincontent'),
			kMainMenu = $('#k-mainmenu'),
			$header = $('header.a_header'),
			$navItems = $header.children('.nav').children(),
			$menu = $('.a_menu_fixed');
		const LOADING = '<div class="loading"></div>',
			ON = 'on',
			CHOSE = 'chose';

		function setContentSize() {
			var $toolbar = $('.a_toolbar'),
				$thbar = $('.a_thbar'),
				headerHeight = $header.height() || 0,
				toolbarHeight = $toolbar.height(),
				otherHeight = headerHeight + toolbarHeight + $thbar.height();
			$toolbar.css('top', headerHeight);
			$thbar.css('top', headerHeight + toolbarHeight);
			$menu.css('top', headerHeight);
			$('.a_content').css({'height' : $(window).height() - otherHeight - kMainContent.find('.a_page').height(), 'padding-top': otherHeight});
			$('.a_detailbar').css('top', headerHeight);
		}

		$(window).bind('hashchange', function(e) {
			mm.hash.checkHash();
			if(menuRequest) {
				menuRequest.abort();
			}
			var hash = location.hash,
				href = location.href;
			if(hash != '') {
				url = path.ROOT + '/' + hash.replace('#', '');
				kMainMenu.show();
			}else {
				url = path.ROOT + '/index/icenter';
				kMainMenu.hide();
			}
			isFirstTr = 0;
			var options = {
				url: url,
				hasCheck: 1,
				hasConfirm: 1,
				mod: $('#k-maincontent'),
				beforeSend: function(xhr) {
					if($().popup.closeAll) {
						$().popup.closeAll();
					}
					this.mod.append(LOADING);
				},
				success: function(data, ts, xhr) {
					this.mod.html(data).children('.loading').remove();

					//默认选中第一行
					this.mod.find('.b_table').find('tr').each(function() {
						var t = $(this);
						if(!isFirstTr && t.attr('origin')) {
							t.click();
							isFirstTr = 1;
							return false;
						}
					});
					// 菜单
					var menuHash;
					if(hash.match(/(^#.*?)(\/$|\/index$|\/index\/$|\/index-page-\d*$)/)) {
						menuHash = hash.replace(/(^#.*?)(\/$|\/index$|\/index\/$|\/index-page-\d*$)/, '$1/index');
					}else if(hash.match(/\?.*/)) {
						menuHash = hash.replace(/\?.*/, '').replace(/(^#.*?\/).*/, '$1').replace(/\/$/, '').replace(/\/index$/, '') + '/index';
					}else {
						menuHash = hash + '/index'
					}

					kMainMenu.find('ul').each(function(idx) {
						var that = $(this),
							k = that.prev('h2').index() / 2;

						that.find('a').each(function(i) {
							var t = $(this),
								href = t.attr('href'),
								rootReg = new RegExp(path.ROOT);
							href = href.replace(rootReg, '');

							if(href == menuHash) {
								t.parent().addClass(ON).parent('ul').show().siblings('ul').hide();
								t.closest('ul').prev('h2').addClass('open');
								t.closest('div').show().siblings().hide();
								$navItems.children().eq(t.closest('div').index()).addClass(CHOSE).siblings().removeClass(CHOSE);
								// $('.quike').children().eq(t.closest('div').index()).addClass(CHOSE).siblings().removeClass(CHOSE);
								// $('.quike').prev().removeClass(CHOSE);
							}else {
								t.parent().removeClass(ON);
							}
						});
					});
					setContentSize();
				},
				error: function(xhr, ts, xhr) {
					this.mod.children('.loading').remove();
				}
			};
			
			menuRequest = mm.ajax(options);
		});
		
		//首次加载
		if($.trim(kMainContent.html()) == '') {
			$(window).trigger('hashchange');
		}

		$('body').delegate('a:not([target="_blank"])', 'click', function(e) {
			if($(this).attr('href') == location.hash) {
				$(window).trigger('hashchange');
			}
		});

		/**
		 * 处理JSON数据
		 */
		function processJson(data) {
			if(data) {
				if(data.closePopup) {
					$().popup.close();
				}
				if(data.status && data.msg) {
					tooltip.tip(data.status, data.msg);
				}
				if(data.isRefresh) {
					$(window).trigger('hashchange');
				}else if(data.hash) {
					location.hash = data.hash;
				}else if(data.popup){
					$('body').popup({title:data.title, width:1000, height:700, url:data.url});
				}
			}
		}

		/**
		 *  获取用户选中项Id
		 */
		mm.getSelectedId = function(el) {
			el = (!el || el == '') ? '#k-maincontent' : el;
			var params = [];
			$(el).find('.a_content').find('tr').each(function() {
				if($(this).hasClass('chose') && !$(this).children('td').eq(0).find('span.checkbox').hasClass('disabled')) {
					params.push($(this).attr('kid'));
				}
			});
			return {kid: params.join(',')};
		}

		mm.ajaxFilter = function(options, check, e, checkArgs) {
			var settings = $.extend({
				type: 'GET',
				filter: function() {
					location.hash = '#' + this.url + '?' + this.data;
				}
			}, options || {});
			return mm.ajaxForm(settings, check, e, checkArgs);
		}

		mm.ajaxPost = function(options, check, e, checkArgs) {
			var settings = $.extend({
				dataType: 'json',
				success: function(data, ts, xhr) {
					processJson(data);
				}
			}, options || {});
			return mm.ajaxForm(settings, check, e, checkArgs);
		}

		/**
		 * ajax删除
		 */
		mm.ajaxDelete = function(options, confirm, confirmArgs) {
			var ok = true, content;
			
			var settings = $.extend({
				dataType: 'json',
				success: function(data, ts, xhr) {
					processJson(data);
				}
			}, options || {});

			if(!settings.kid) {
				content = '#k-maincontent';
			}

			if(typeof(settings.kid) == 'string') {
				settings.data = {kid: settings.kid};
			}else {
				if(content) {
					settings.kid = content;
				}
				settings.data = mm.getSelectedId(settings.kid);
			}
			if(settings.data && settings.data.kid && $.trim(settings.data.kid) != '') {
				if(confirm && $.isFunction(confirm)) {
					ok = (confirm.call(this, confirmArgs) !== false) ? true : false;
				}
				if(ok) {
					mm.ajax(settings);
				}
			}else {
				tooltip.tip('warning', '请选择要删除的项');
			}
		}

		/**
		 * ajax多级联动
		 */
		mm.ajaxLinkage = function(options) {
			var t = $(options.selectId),
				name = t.attr('name').replace(/\[\]/, ''),
				val = t.val(),
				data;

			if(!options.data) {
				data = {};
				data[name] = val;
			}

			var settings = $.extend({
				data: data
			}, options || {});

			var success = function() {};
			if($.isFunction(settings.success)) {
				success = settings.success;
			}
			settings.success = function(data, ts, xhr) {
				var parent = t.closest('['+ options.mod +'="true"]');
				parent.nextAll('['+ options.mod +'="true"]').remove();
				settings.nextSelect = $(data).insertAfter(parent).find('select');
				if(settings.nextSelectVal) {
					settings.nextSelect.find('option[value="' + settings.nextSelectVal + '"]').attr('selected', 'selected').siblings().removeAttr('selected');
				}
				success.call(settings, data, ts, xhr);
			}

			mm.ajax(settings);
			
		}

		/**
		 * 弹出框筛选
		 */
		mm.ajaxPopupFilter = function(options, check, e, checkArgs) {
			var settings = $.extend({
				dataType: 'html',
				mod: options.mod || $().popup.win[0].find('.content'),
				beforeSend: function(xhr) {
					this.mod.append(LOADING);
				},
				success: function(data, ts, xhr) {
					this.mod.html(data).children('.loading').remove();
				},
				error: function(xhr, ts, xhr) {
					this.mod.children('.loading').remove();
				}
			}, options || {});
			return mm.ajaxForm(settings, check, e, checkArgs);
		}

		/**
		 * 弹出框分页
		 */
		$('body').delegate('.popup .a_page a', 'click', function(e) {
			e.preventDefault();
			var t = $(this),
				url = location.origin + location.pathname.replace(/\/$/, '').replace(/(\/index$|\/index\.html$)/, '') + '/' + t.attr('href').replace(/^#/, '');
			var options = {
				url: url,
				hasCheck: 1,
				hasConfirm: 1,
				mod: t.closest('.content'),
				beforeSend: function(xhr) {
					this.mod.append(LOADING);
				},
				success: function(data, ts, xhr) {
					this.mod.html(data).children('.loading').remove();
				},
				error: function(xhr, ts, xhr) {
					this.mod.children('.loading').remove();
				}
			};
			mm.ajax(options);
		});

		/*===========================================================表格选中规则===========================================================
		 * 工具栏上的按钮分为两类：
		 * A类：不受表格操作影响；
		 * B类：受表格操作影响；
		 * 1、点击表格tr选中（该tr添加一个类，改变行背景颜色），其他行取消选中，右边简介栏用ajax请求该行内容。
		 * 2、点击表格tr下第一个td：
		 * a.该tr为选中状态时，取消该tr及表头第一个th的选中状态，如果当前有选中状态的tr，右边简介栏用ajax请求第一个选中tr的内容，否则右边简介栏用ajax请求第一个tr内容。
		 * b.该tr没有选中状态时，选中该tr，右边简介栏用ajax请求该tr的内容，如果全部tr都为选中，表头第一个th选中。
		 * c.可多行选中。
		 * 3、点击表头上面的第一个th：
		 * a.该th为选中状态时，取消该th及表格内容下的tr选中状态，A类按钮可操作，右边简介栏用ajax请求表格内容下第一个tr的内容。
		 * b.该th没有选中状态时，选中该th及表格内容下的tr，B类按钮可操作。
		=============================================================================================================================================================*/
		kMainContent.delegate('.b_table tr','click', function(e) {//单选
			var t = $(this),
				origin = t.attr('origin') || null;

			if(origin) {
				mm.ajax({
					url: origin,
					mod: $('.a_detailbar'),
					beforeSend: function(xhr) {
						this.mod.addClass(LOADING);
					},
					success: function(data, ts, xhr) {
						this.mod.removeClass(LOADING);
						this.mod.html(data);
					},
					error: function(xhr, ts, xhr) {
						this.mod.removeClass(LOADING);
					}
				});
			}

			t.addClass(CHOSE);
			if(!t.children('td').eq(0).children('span.checkbox').hasClass("disabled")){
				t.children('td').eq(0).children('span.checkbox').addClass(CHOSE);
				if(t.siblings().length == 0){
					$('.a_thbar th span.checkbox').addClass(CHOSE);
				}else {
					$('.a_thbar th span.checkbox').removeClass(CHOSE);
				}
			}
			t.siblings('.chose').removeClass(CHOSE).children('td').children('span.checkbox').removeClass(CHOSE);
			
		}).delegate('tr td span.checkbox', 'click', function(e) {//复选
			e.stopPropagation();
			var t = $(this),
				tr = t.closest('tr');
			if(!t.hasClass("disabled")){
				if(t.hasClass(CHOSE)) {
					t.removeClass(CHOSE);
					tr.removeClass(CHOSE);
					$('.a_thbar th span.checkbox').removeClass(CHOSE);
				}else {
					tr.addClass(CHOSE);
					t.addClass(CHOSE);
					var other = tr.siblings().find("span.checkbox").not(".disabled"),
						len = other.length;
					other.each(function(index, val) {
						if(!$(this).hasClass(CHOSE)){
							return false;
						}else {
							if(index == len - 1) {
								$('.a_thbar th span.checkbox').addClass(CHOSE);
							}
						}
					});	
				}
			}
		}).delegate('.a_thbar th span.checkbox', 'click', function(e) {//全选
			var t = $(this),
				content = t.closest('.a_thbar').next('.a_content');
			if(!content.children('span.checkbox').hasClass("disabled")){
				if(t.hasClass(CHOSE)) {
					content.find('tr.chose').removeClass(CHOSE).find('.chose').removeClass(CHOSE);
					t.removeClass(CHOSE);
				}else {
					t.addClass(CHOSE);
					content.find('span.checkbox').not(".disabled").addClass(CHOSE).closest('tr').addClass(CHOSE);
				}
			}
		}).delegate('.saction button', 'click', function(e) {
			e.stopPropagation();
			var t = $(this);
			if(t.hasClass('more')) {
				var more = t.next();
				if(more.css('display') != 'none') {
					more.hide();
					t.removeClass('on');
				}else {
					t.addClass('on');
					more.show().closest('tr').siblings('tr').find('.maction').hide().prev('.more').removeClass('on');
				}
			}else {
				t.nextAll('.maction').hide().prev('.more').removeClass('on');
			}
		}).delegate('a:not([target="_blank"])', 'click', function(e) {
			e.stopPropagation();
		});

		/**
		 * 菜单
		 */
		$navItems.click(function (e) {
			var $this = $(this),
				i = $this.index();
			$this.addClass(CHOSE).siblings().removeClass(CHOSE);
			kMainMenu.show().children().children().eq(i).show().siblings().hide();
		});

		// var quike = $('.quike'),
		// 	logo = quike.prev();

		// quike.children().bind('click', function(e) {
		// 	var t = $(this),
		// 		idx = t.index();
		// 	t.addClass(CHOSE).siblings().removeClass(CHOSE);
		// 	logo.removeClass(CHOSE);
		// 	kMainMenu.show().children().children().eq(idx).show().siblings().hide();
		// });

		// logo.bind('click', function(e) {
		// 	$(this).addClass(CHOSE);
		// 	quike.find('h1.' + CHOSE).removeClass(CHOSE);
		// 	kMainMenu.children().children().hide().find('.' + ON).removeClass(ON).end().find('.open').removeClass('open');
		// });

		kMainMenu.find('h2').bind('click', function(e) {
			var t = $(this),
				choseLi = kMainMenu.find('li.on'),
				ul = t.next('ul');

			if(ul.css('display') != 'none') {
				t.removeClass('open');
				ul.hide();
			}else {
				t.addClass('open').siblings('h2').removeClass('open');
				ul.show().siblings('ul').hide();
			}
		});
		
		kMainContent.delegate('.ssearch .more', 'click', function(e){
			e.stopPropagation();
			var t = $(this),
				nd = t.closest('.ssearch').next();
			if(nd.css('display') != 'none' ){
				nd.css({'display' :'none'});
			}else{
				nd.css({'display' :'block'});
			}
		}).delegate('.drop_down .showall:not([has-param=1])', 'click', function() {
			var hash = location.hash,
				newHash = hash.replace(/(\?.*|-page-\d*)/, '');
			if(hash == newHash) {
				$(window).trigger('hashchange');
			}else {
				location.hash = newHash;
			}
		}).delegate('.fillter .drop_down', 'click', function(e) {
			e.stopPropagation();
		}).delegate('.maction', 'click', function(e) {
			e.stopPropagation();
			$(this).hide();
		});



		$(window).bind('resize.content', function() {
			setContentSize();
		});

		//右键菜单
		var contextmenu = $('.contextmenu');

		$(document).bind('click', function(e) {
			$('.fillter .drop_down').hide();
			$('.saction .maction').hide().prev('.more').removeClass('on');

			contextmenu.hide();//隐藏右键菜单
		}).bind('contextmenu', function(e) {
			contextmenu.hide();
		}).bind('mousedown',function(e){
			contextmenu.hide();
		}).bind('mousewheel',function(e){
			contextmenu.hide();
		});

		contextmenu.bind('click', function(e) {
			contextmenu.hide();
		}).bind('contextmenu', function(e) {
			contextmenu.hide();
			e.preventDefault();
		}).bind('mousedown',function(e){
			e.stopPropagation();//冒泡
		});

		$('body').delegate('.b_table tr', 'contextmenu', function(e) {
			e.preventDefault();//阻止默认事件
			e.stopPropagation();//冒泡

			var t = $(this);

			t.click();

			var content = t.children('td').last(),
				contentA = content.find('a.abutton'),
				contentSa = content.find('.saction'),
				contentB = contentSa.children('button').first(),
				contentD = contentSa.children('dl'),
				hasConent = 0;

			contextmenu.empty();//清空右键div内容

			if(contentA.length > 0) {
				hasConent = 1;
				contextmenu.append(contentA.clone());
			}

			if(contentB.length > 0) {
				hasConent = 1;
				contextmenu.append(contentB.clone());
			}

			if(contentD.length > 0 && contentD.children('dd').length > 0) {
				hasConent = 1;
				contextmenu.append(contentD.clone().show());
			}
			
			if(!hasConent) {
				return;
			}

			if(e.pageX+195 <= $(document).width() ){
				contextmenu.css({left: e.pageX, right: 'auto'});
			}else{
				contextmenu.css({left: 'auto', right:0});
			}
			if(e.pageY + contextmenu.height() <= $(document).height() ){
				contextmenu.css({top: e.pageY, bottom: 'auto'});
			}else{
				contextmenu.css({top: 'auto', bottom: 0});
			}

			contextmenu.show();
		});	

		//图标按钮提示
		var actionTip = $('.action_tip'),
			aTSpan = actionTip.children('span');
			aTEma = actionTip.children('em.arrow_downbefore');
			aTEmb = actionTip.children('em.arrow_downafter');
		$('body').delegate('.action .add, .action .del, .action .export, .action .import, .action .back, .action .save', 'mouseenter', function(e){
			var t = $(this);
			var top = t.offset().top,
				left = t.offset().left,
				sWidth, sHeight, emTop, emLeft,
				tipText = '',
				classArr = 'add del import export back save'.split(' '),
				textArr = '添加 删除 导入 导出 返回 保存'.split(' ');

			classArr.forEach(function(name, idx, arr) {
				if(t.hasClass(name)) {
					tipText = textArr[idx];
					arr.length = 0;//跳出循环
					return;
				}
			});
			aTSpan.text(tipText);
			
			actionTip.css({'display': 'block', 'visibility': 'hidden'});
			sWidth = actionTip.outerWidth();
			sHeight = actionTip.outerHeight();
			top = top - sHeight - 8;
			left =left - (sWidth -44)/2;
			
			emLeft = sWidth/2-6;
			aTEma.css({left: emLeft});
			aTEmb.css({left: emLeft+1});
			

			actionTip.css({top: top, left: left, visibility: 'visible', display: 'block'});

		}).delegate('.action .add, .action .del, .action .export, .action .import, .action .back, .action .save', 'mouseleave mousedown', function(e) {
			actionTip.hide();
		});

	});
})($, path, mm);