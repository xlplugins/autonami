const bwf_ajax = function (cls, is_form, cb) {
    let $ = jQuery;
    const self = this;
    let element = null;
    let handler = {};
    let prefix = "bwf_";
    this.action = null;
    this.change_prefix = function (new_prefix) {
        if (new_prefix !== undefined) {
            prefix = new_prefix;
        }
    };
    this.data = function (form_data, formEl = null) {
        return form_data;
    };
    this.before_send = function (formEl) {
    };
    this.async = function (bool) {
        return bool;
    };
    this.method = function (method) {
        return method;
    };
    this.success = function (rsp, fieldset, loader, jqxhr, status) {
    };
    this.complete = function (rsp, fieldset, loader, jqxhr, status) {
    };
    this.error = function (rsp, fieldset, loader, jqxhr, status) {
    };
    this.action = function (action) {
        return action;
    };
    this.element = function () {
        return element;
    };

    this.validate = function (formEl, action, form_data) {

        return true;
    };

    function reset_form(action, fieldset, loader, rsp, jqxhr, status) {
        if (fieldset.length > 0) {
            fieldset.prop('disabled', false);
        }
        loader.remove();
        let loader2;
        loader2 = $(".bwf_ajax_btn_bottom_container");
        loader2.removeClass('ajax_loader_show');

        if (self.hasOwnProperty(action) === true && typeof self[action] === 'function') {
            self[action](rsp, fieldset, loader, jqxhr, status);
        }
    }

    function form_post(action) {
        let formEl = element;
        let ajax_loader = null;

        let form_data = new FormData(formEl);

        form_data.append('action', action);

        form_data.append('bwf_nonce', bwf_secure.nonce);

        let form_method = $(formEl).attr('method');
        if ($(formEl).find("." + action + "_ajax_loader").length === 0) {
            $(formEl).find(".bwf_form_submit").prepend("<span class='" + action + "_ajax_loader spinner" + "'></span>");
            ajax_loader = $(formEl).find("." + action + "_ajax_loader");
        } else {
            ajax_loader = $(formEl).find("." + action + "_ajax_loader");
        }

        let ajax_loader2 = $(".bwf_ajax_btn_bottom_container");
        ajax_loader.addClass('ajax_loader_show');
        ajax_loader2.addClass('ajax_loader_show');

        let fieldset = $(formEl).find("fieldset");
        if (fieldset.length > 0) {
            fieldset.prop('disabled', true);
        }

        self.before_send(formEl, action);

        let data = self.data(form_data, formEl);

        let validated = self.validate(formEl, action, form_data);
        if (true !== validated) {
            return;
        }

        let request = {
            url: ajaxurl,
            async: self.async(true),
            method: self.method('POST'),
            data: data,
            processData: false,
            contentType: false,
            //       contentType: self.content_type(false),
            success: function (rsp, jqxhr, status) {
                if (typeof rsp === 'object' && rsp.hasOwnProperty('nonce')) {
                    bwf_secure.nonce = rsp.nonce;
                    delete rsp.nonce;
                }
                reset_form(action + "_ajax_success", fieldset, ajax_loader, rsp, jqxhr, status);
                self.success(rsp, jqxhr, status, element, ajax_loader, fieldset);
            },
            complete: function (rsp, jqxhr, status) {
                reset_form(action + "_ajax_complete", fieldset, ajax_loader, rsp, jqxhr, status);
                self.complete(rsp, jqxhr, status, element, ajax_loader, fieldset);
            },
            error: function (rsp, jqxhr, status) {
                reset_form(action + "_ajax_error", fieldset, ajax_loader, rsp, jqxhr, status);
                self.error(rsp, jqxhr, status, element, ajax_loader, fieldset);
            }
        };
        if (handler.hasOwnProperty(action)) {
            clearTimeout(handler[action]);
        } else {
            handler[action] = null;
        }
        handler[action] = setTimeout(
            function (request) {
                $.ajax(request);
            },
            200,
            request
        );
    }

    function send_json(action) {
        let formEl = element;
        let data = self.data({}, formEl);
        if (typeof data === 'object') {
            data.action = action;
        } else {
            data = {
                'action': action
            };
        }

        self.before_send(formEl, action);

        let validated = self.validate(formEl, action);
        if (true !== validated) {
            return;
        }

        data.bwf_nonce = bwf_secure.nonce;
        let request = {
            url: ajaxurl,
            async: self.async(true),
            method: self.method('POST'),
            data: data,
            success: function (rsp, jqxhr, status, element) {

                if (typeof rsp === 'object' && rsp.hasOwnProperty('nonce')) {
                    bwf_secure.nonce = rsp.nonce;
                    delete rsp.nonce;
                }
                self.success(rsp, jqxhr, status, element);
            },
            complete: function (rsp, jqxhr, status, element) {

                self.complete(rsp, jqxhr, status, element);
            },
            error: function (rsp, jqxhr, status) {
                self.error(rsp, jqxhr, status, element);
            }
        };
        if (handler.hasOwnProperty(action)) {
            clearTimeout(handler[action]);
        } else {
            handler[action] = null;
        }
        handler[action] = setTimeout(
            function (request) {
                $.ajax(request);
            },
            200,
            request
        );
    }

    this.ajax = function (action, data) {
        if (typeof data === 'object') {
            data.action = action;
        } else {
            data = {
                'action': action
            };
        }
        data.action = prefix + action;
        self.before_send(document.body, action);
        let validated = self.validate(document.body, action, data);
        if (true !== validated) {
            return;
        }
        data.bwf_nonce = bwf_secure.nonce;
        let request = {
            url: ajaxurl,
            async: self.async(true),
            method: self.method('POST'),
            data: data,
            success: function (rsp, jqxhr, status) {
                if (typeof rsp === 'object' && rsp.hasOwnProperty('nonce')) {
                    bwf_secure.nonce = rsp.nonce;
                    delete rsp.nonce;
                }
                self.success(rsp, jqxhr, status, action);
            },
            complete: function (rsp, jqxhr, status) {
                self.complete(rsp, jqxhr, status, action);
            },
            error: function (rsp, jqxhr, status) {
                self.error(rsp, jqxhr, status, action);
            }
        };
        if (handler.hasOwnProperty(action)) {
            clearTimeout(handler[action]);
        } else {
            handler[action] = null;
        }
        handler[action] = setTimeout(
            function (request) {
                $.ajax(request);
            },
            200,
            request
        );
    };

    function form_init(cls) {
        if ($(cls).length === 0) {
            return;
        }
        $(cls).on(
            "submit",
            function (e) {
                e.preventDefault();
                let action = $(this).data('bwf-action');
                if (action !== 'undefined') {
                    action = prefix + action;
                    action = action.trim();
                    element = this;
                    self.action = action;
                    form_post(action);
                }
            }
        );

        if (typeof cb === 'function') {
            cb(self);
        }
    }

    function click_init(cls) {
        if ($(cls).length === 0) {
            return;
        }
        $(cls).on(
            "click",
            function (e) {
                e.preventDefault();
                let action = $(this).data('bwf-action');
                if (action !== 'undefined') {
                    action = prefix + action;
                    action = action.trim();
                    element = this;
                    self.action = action;
                    send_json(action);
                }
            }
        );

        if (typeof cb === 'function') {
            cb(self);
        }
    }

    if (is_form === true) {
        form_init(cls, cb);
        return this;
    }

    if (is_form === false) {
        click_init(cls, cb);
        return this;
    }
    return this;
};
