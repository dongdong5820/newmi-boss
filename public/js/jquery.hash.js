(function($, undefined) {
    const LOADING = 'loading';
    function empty() {}
    $.ajaxSetup({
        headers: {
            //'Accept': 'application/json',
            'Aq': '1'
        },
    });
    var mm = window.mm = window.mm || {
        ajaxSetting: {
            context: null,
            hasCheck: false,
            hasConfirm: false,
            beforeSend: empty,
            success: empty,
            error: empty,
            complete: empty
        },
        ajaxFormSetting: {
            dataType: 'json',
            context: null,
            hasCheck: false,
            hasConfirm: false,
            beforeSend: empty,
            success: empty,
            error: empty,
            complete: empty
        },
        ajax: function(options) {
            var that = this,
                options = $.extend({}, options || {}),
                settings;

            for(key in that.ajaxSetting) {
                if(options[key] === undefined) {
                    options[key] = that.ajaxSetting[key];
                }
            }
            settings = $.extend({}, options || {});
            if(!settings.context) {
                settings.context = settings;
            }

            settings.beforeSend = function(xhr) {
                if(settings.hasCheck) mm.check = function() {};
                if(settings.hasConfirm) mm.confirm = function() {};
                options.beforeSend.call(settings.context, xhr);
            }

            settings.success = function(data, ts, xhr) {
                options.success.call(settings.context, data, ts, xhr);
            }

            // ts: "timeout", "error", "abort", "parsererror"
            settings.error = function(xhr, ts, error) {
                options.error.call(settings.context, xhr, ts, error);
            }
            return $.ajax(settings);
        },
        ajaxForm: function(options, check, e, checkArgs) {
            var that = this,
                options = options || {},
                settings,
                form = $(options.formId),
                submitBtn = form.find('[type=submit]'),
                resetBtn = form.find('[type=reset]'),
                checkSuccess = true,   //表单提交是否验证成功
                checkArgs = checkArgs;

            for(key in that.ajaxFormSetting) {
                if(options[key] === undefined) {
                    options[key] = that.ajaxFormSetting[key];
                }
            }

            settings = $.extend({}, options || {});
            if(!settings.context) {
                settings.context = settings;
            }

            if(e) {
                if(!(Object.prototype.toString.call(e) === '[object Event]' || Object.prototype.toString.call(e) === '[object Object]')) {
                    checkArgs = e;
                    e = event;
                }
            }else {
                e = event;
            }
            e.preventDefault();

            if(check && $.isFunction(check)) {
                checkSuccess = (check.call(that, checkArgs) !== false) ? true : false;
            }else {
                check = empty;
            }

            if(checkSuccess) {
                if(!settings.type) {
                    var m = form.attr('method');
                    settings.type = !m ? 'GET' : m.toLocaleUpperCase();
                }

                if(!settings.url) {
                    var action = form.attr('action');
                    settings.url = !action ? window.location.href : action;
                }

                if(!settings.dataType) {
                    settings.dataType = 'json';  
                }

                if(form.attr('enctype') == 'multipart/form-data') {
                    settings.cache = false;
                    settings.contentType = false;
                    settings.processData = false;
                    settings.data = new FormData(form[0]);
                }else {
                    settings.data = form.serialize();//form序列化, 自动调用了encodeURIComponent方法将数据编码了
                }

                if(options.filter && !options.filter.call(settings.context)) {  
                    return false;
                }

                settings.beforeSend = function(xhr) {
                    if(settings.hasCheck) mm.check = function() {};
                    if(settings.hasConfirm) mm.confirm = function() {};
                    submitBtn.attr('disabled', 'disabled');
                    resetBtn.attr('disabled', 'disabled');
                    options.beforeSend.call(settings.context, xhr);
                }

                settings.success = function(data, ts, xhr) {
                    submitBtn.removeAttr('disabled');
                    resetBtn.removeAttr('disabled');
                    options.success.call(settings.context, data, ts, xhr);
                }

                // ts: "timeout", "error", "abort", "parsererror"
                settings.error = function(xhr, ts, error) {
                    submitBtn.removeAttr('disabled');
                    resetBtn.removeAttr('disabled');
                    options.error.call(settings.context, xhr, ts, error);
                }
                
                $.ajax(settings);
            }
        }
    };

    /**
     * 检测历史记录前进、后退
     */
    (function(mm) {
        var list = [],
            idx = -1,
            isB = false,
            isF = false,
            hash = {
                isBack: function() {
                    return isB;
                },
                isForward: function() {
                    return isF;
                },
                push: function(hash) {
                    list.splice(idx + 1, list.length - idx - 1);
                    list.push(hash);
                    this.end();
                },
                index: function() {
                    return idx;
                },
                stack: function() {
                    return list;
                },
                end: function() {
                    idx = list.length - 1;
                },
                checkHash: function() {
                    var hash = location.hash.substr(1);
                    if (list[idx - 1] === hash) {
                        isB = true;
                        isF = false;
                        idx--;
                    } else if (list[idx + 1] === hash) {
                        isB = false;
                        isF = true;
                        idx++;
                    } else {
                        isB = false;
                        isF = false;
                        this.push(hash);
                    }
                }
            };
        mm.hash = hash;
    })(mm);

})($);