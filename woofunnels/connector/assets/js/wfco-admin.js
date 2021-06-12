(function ($) {
    'use strict';
    $(document).ready(
        function () {
            wfco_models();
            wfco_update_connector();
            wfco_add_connector();
            wfco_sync_connector();
            wfco_handle_delete_connector();
            wfco_connector_settings_html();
            wfco_open_success_swal();
            wfco_install_connector();
            get_autoresponder_fields();
            wfco_load_int_settings();

            /** Metabox panel close */
            $(".wfco_allow_panel_close .hndle").on(
                "click",
                function () {
                    var $this = $(this);
                    var parentPanel = $(this).parents(".wfco_allow_panel_close");
                    parentPanel.toggleClass("closed");
                }
            );

            $("html body").on(
                "click",
                ".wfco-copy-clipboard",
                function () {
                    var elem = jQuery(this)[0];
                    elem.select();
                    copyToClipboard(elem);
                }
            );
            $("html body").on(
                "click",
                ".wfco-text-copy-btn",
                function (e) {
                    e.preventDefault();
                    var $this = jQuery(this);
                    $this.siblings('.wrapper').find(".wfco-copy-clipboard").trigger("click");
                }
            );
        }
    );

    function wfco_models() {

        var modal_connect = $("#wfco-modal-connect");
        if (modal_connect.length > 0) {
            modal_connect.iziModal({
                headerColor: '#f3f8f9',
                background: '#ffffff',
                borderBottom: false,
                history: false,
                width: 600,
                radius: 20,
                overlayColor: 'rgba(0, 0, 0, 0.6)',
                transitionIn: 'comingIn',
                transitionOut: 'comingOut',
                navigateCaption: true,
                navigateArrows: "false",
                onOpening: function (modal) {
                    modal.startLoading();
                },
                onOpened: function (modal) {
                    modal.stopLoading();
                    $('.wfco_submit_btn_style').text(wfcoParams.texts.update_btn);
                },
                onClosed: function (modal) {
                    //console.log('onClosed');
                }
            });
        }

        var model_edit = $("#modal-edit-connector");
        if (model_edit.length > 0) {
            model_edit.iziModal({
                headerColor: '#f3f8f9',
                background: '#ffffff',
                borderBottom: false,
                history: false,
                width: 600,
                radius: 20,
                overlayColor: 'rgba(0, 0, 0, 0.6)',
                transitionIn: 'comingIn',
                transitionOut: 'comingOut',
                navigateCaption: true,
                navigateArrows: "false",
                onOpening: function (modal) {
                    modal.startLoading();
                },
                onOpened: function (modal) {
                    modal.stopLoading();
                },
                onClosed: function (modal) {
                    //console.log('onClosed');
                }
            });
        }
    }

    function wfco_update_connector() {
        if ($('.wfco_update_connector').length > 0) {
            let wp_ajax = new bwf_ajax('.wfco_update_connector', true);

            wp_ajax.before_send = function (element, action) {
                $('.wfco_update_btn_style').val(wfcoParams.texts.update_btn_process);
            };
            wp_ajax.success = function (rsp) {
                if (true === rsp.status || 'success' === rsp.status) {
                    if (rsp.data_changed == 1) {
                        $("#modal-edit-connector").iziModal('close');
                        swal({
                            title: wfcoParams.texts.update_int_prompt_title,
                            type: "success",
                            text: wfcoParams.texts.update_int_prompt_text,
                        });
                    } else {
                        swal({
                            title: wfcoParams.texts.update_int_prompt_title,
                            type: "success",
                            showConfirmButton: false,
                        });
                        setTimeout(
                            function () {
                                window.location.reload();
                            },
                            1000
                        );
                    }
                } else {
                    let resp_message = _.has(rsp, 'message') ? rsp.message : (_.has(rsp, 'msg') ? rsp.msg : '');
                    $('.wfco_form_response').html(resp_message);
                    $('.wfco_update_connector').find("input[type=submit]").prop('disabled', false);
                    $('.wfco_save_btn_style').val('update');
                }

            };
        }
    }

    /**
     * Save connector  i.e. add connector ajax call
     */
    function wfco_add_connector() {
        if ($('.wfco_add_connector').length > 0) {
            let wp_ajax = new bwf_ajax('.wfco_add_connector', true);
            wp_ajax.before_send = function (element, action) {
                $('.wfco_save_btn_style').val(wfcoParams.texts.connect_btn_process);
            };

            wp_ajax.success = function (rsp) {
                var response_div = $('.wfco_form_response');
                if (true === rsp.status || 'success' === rsp.status) {
                    swal({
                        title: wfcoParams.texts.connect_success_title,
                        type: "success",
                        showConfirmButton: false,
                    });
                    response_div.removeClass('wfco_form_error');
                    $('.wfco-connector-connect').removeClass('wfco_btn_spin');
                    setTimeout(
                        function () {
                            window.location.reload();
                        },
                        3000
                    );
                } else {
                    response_div.addClass('wfco_form_error');
                    let resp_message = _.has(rsp, 'message') ? rsp.message : (_.has(rsp, 'msg') ? rsp.msg : '');
                    response_div.html(resp_message);
                    $('.wfco_add_connector').find("input[type=submit]").prop('disabled', false);
                    $('.wfco_save_btn_style').val('save');
                }
            };
        }
    }

    function wfco_connector_settings_html() {

        /**
         * Connect Button
         */
        $(document).on(
            'click',
            '.wfco-connector-connect',
            function () {
                var $this = $(this);
                var selected_value = $this.attr('data-slug');
                var title = $this.attr('data-iziModal-title');
                if (selected_value != '') {
                    var selected_connector = wp.template('connector-' + selected_value);
                    $('#wfco_connector_fields').html('');
                    wfco_make_html(1, '#wfco_connector_fields', selected_connector());
                } else {
                    $('#wfco_connector_fields').html('');
                }
                $("#wfco-modal-connect").iziModal('setTitle', title);
            }
        );

        /**
         * Setting button
         */
        $(document).on(
            'click',
            '.wfco-connector-edit',
            function () {
                var $this = $(this);
                var selected_value = $this.data('slug');
                var Title = $this.attr('data-iziModal-title');
                if (selected_value != '') {
                    var selected_connector = wp.template('connector-' + selected_value);
                    $('#wfco_connector_edit_fields').html('');
                    wfco_make_html(1, '#wfco_connector_edit_fields', selected_connector());
                } else {
                    $('#wfco_connector_edit_fields').html('');
                }
                $("#modal-edit-connector").iziModal('setTitle', Title);
            }
        );
    }

    function wfco_sync_connector() {
        jQuery(document).on('click', '.wfco-connector-sync', function () {
            var sync_nonce = jQuery(this).attr('data-nonce');
            var connector_id = jQuery(this).attr('data-id');
            var slug = jQuery(this).attr('data-slug');
            var sync_swal = {
                title: wfcoParams.texts.sync_title,
                text: wfcoParams.texts.sync_text,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#db4040",
                confirmButtonText: "Proceed",
                cancelButtonColor: "#646464",
                cancelButtonText: "Cancel",
                allowOutsideClick: false,
            };

            var swal_then = swal(sync_swal);
            swal_then.then(
                function (result) {
                    if (true !== result) {
                        return;
                    }

                    var swal_data = {
                        title: wfcoParams.texts.sync_wait,
                        text: wfcoParams.texts.sync_progress,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        onOpen: () => {
                            swal.showLoading();
                        }
                    };
                    // swal.showLoading();
                    swal(swal_data);

                    var ajax = new bwf_ajax();
                    ajax.ajax('sync_connector', {'sync_nonce': sync_nonce, 'id': connector_id, 'slug': slug});
                    ajax.success = function (resp) {
                        if (true === resp.status || 'success' === resp.status) {
                            if (resp.data_changed == 1) {
                                swal({
                                    title: wfcoParams.texts.sync_success_title,
                                    type: "success",
                                    text: wfcoParams.texts.sync_success_text,
                                });
                            } else {
                                swal({
                                    title: wfcoParams.texts.sync_success_title,
                                    type: "success",
                                    showConfirmButton: false,
                                });
                                setTimeout(function () {
                                    window.location.reload();
                                }, 1000);
                            }
                        } else {
                            let resp_message = _.has(resp, 'message') ? resp.message : (_.has(resp, 'msg') ? resp.msg : wfcoParams.texts.oops_text);
                            swal({
                                title: wfcoParams.texts.oops_title,
                                text: resp_message,
                                type: "error",
                            });
                        }
                    };
                }
            );

            swal_then.catch(function (result) {
            });
        });
    }

    function wfco_handle_delete_connector() {
        jQuery(document).on('click', '.wfco-connector-delete', function () {
            var delete_nonce = jQuery(this).attr('data-nonce');
            var connector_id = jQuery(this).attr('data-id');
            var swal_object = swal({
                title: wfcoParams.texts.delete_int_prompt_title,
                text: wfcoParams.texts.delete_int_prompt_text,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#db4040",
                confirmButtonText: "Proceed",
                cancelButtonColor: "#646464",
                cancelButtonText: "Cancel",
                allowOutsideClick: false,
            });

            swal_object.then(
                function (result) {
                    if (result) {
                        // swal.showLoading();
                        swal({
                            title: wfcoParams.texts.delete_int_wait_title,
                            text: wfcoParams.texts.delete_int_wait_text,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            onOpen: () => {
                                swal.showLoading();
                            }
                        });
                        var ajax = new bwf_ajax();
                        ajax.ajax('delete_connector', {'delete_nonce': delete_nonce, 'id': connector_id});
                        ajax.success = function (resp) {
                            if (resp.status == true) {
                                swal({
                                    title: wfcoParams.texts.delete_int_success,
                                    type: "success",
                                    showConfirmButton: false,
                                });
                                setTimeout(
                                    function () {
                                        window.location.reload();
                                    },
                                    1000
                                );
                            } else {
                                swal({
                                    title: wfcoParams.texts.oops_title,
                                    text: wfcoParams.texts.oops_text,
                                    type: "error",
                                });
                            }
                        };
                    }
                }
            );
            swal_object.catch(function (result) {

            });
        });

    }

    /**
     * This function run when Oauth redirection happening to client page successfully authentication
     *
     * @returns {boolean}
     */
    function wfco_open_success_swal() {
        var connector = wfco_getUrlParameter('wfco_connector');
        if (connector == '') {
            return;
        }
        var access_token = wfco_getUrlParameter('access_token');
        var connectors = wfcoParams.oauth_connectors;
        if (connectors.indexOf(connector) > -1 && access_token != '') {
            swal({
                title: wfcoParams.texts.sync_wait,
                text: wfcoParams.texts.save_progress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                onOpen: () => {
                    swal.showLoading();
                }
            });
            let wp_ajax = new bwf_ajax();
            let add_query = {"_wpnonce": wfcoParams.oauth_nonce, "wfco_connector": connector, "access_token": access_token};

            wp_ajax.ajax('save_connector', add_query);

            wp_ajax.success = function (rsp) {
                if (rsp.status === true) {
                    swal({
                        title: wfcoParams.texts.connect_success_title,
                        text: "",
                        type: "success",
                    });
                    setTimeout(
                        function () {
                            window.location.href = rsp.redirect_url;
                        }, 3000);
                } else {
                    $("#wfco-modal-connect").iziModal('close');
                    setTimeout(
                        function () {
                            swal({
                                title: "Oops",
                                text: 'There was some error. Please try again later.',
                                type: "error",
                            });
                        }, 1000);
                }
            };
            return false;
        }
    }

    function wfco_install_connector() {
        $(document).on(
            'click',
            '.wfco_connector_install',
            function () {
                var $this = jQuery(this);
                var sync_nonce = jQuery(this).attr('data-nonce');
                var connector_slug = jQuery(this).attr('data-connector');
                var loading_text = jQuery(this).attr('data-load-text');
                var type = jQuery(this).data('type');
                var page_text = jQuery(this).attr('data-text');
                var slug = jQuery(this).attr('data-connector-slug');
                $this.text(loading_text);

                var add_query = {
                    'install_nonce': sync_nonce,
                    'connector_slug': connector_slug,
                    'type': type,
                    'slug': slug,
                };

                let wp_ajax = new bwf_ajax();
                wp_ajax.ajax('connector_install', add_query);
                wp_ajax.success = function (resp) {

                    var redirect = /({.+})/img;
                    var matches = redirect.exec(resp);
                    var responseObj = resp;
                    if (null !== matches && matches.length > 0) {
                        responseObj = jQuery.parseJSON(matches[0]);
                    }

                    if (responseObj.status == true) {
                        let connector_licence = new bwf_ajax();
                        connector_licence.ajax('create_connector_license', add_query);
                        connector_licence.success = function (resp) {
                            swal({
                                title: '',
                                text: responseObj.msg,
                                type: "success",
                                showConfirmButton: false,
                            });
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        };
                    } else {
                        let msg = responseObj.msg;
                        if (responseObj.hasOwnProperty('error_code')) {
                            if (wfcoParams.errors.hasOwnProperty(responseObj.error_code)) {
                                msg = wfcoParams.errors[responseObj.error_code];
                            }
                        }

                        let sw = swal({
                            title: wfcoParams.texts.oops_title,
                            text: msg,
                            type: "error",
                        });
                        sw.catch(function () {
                            console.log('Cs');
                        });
                    }
                    $this.text(page_text);
                };
            }
        );
    }

    function get_autoresponder_fields() {

        var connector_field = $('.wfco_connector_field');
        if (connector_field.length > 0) {
            var slug = connector_field.data('slug');
            var data_db = connector_field.data('db');
            var data_saved = connector_field.data('saved');

            var selected_task = wp.template(slug);
            $('#wfco_i_field').html('');
            wfco_make_html(1, '#wfco_i_field', selected_task({db_data: data_db}));
            if ((data_saved in data_db) && data_saved != '') {
                $('#wfco_remove_c_i').val(data_saved);
            } else {
                $('#wfco_remove_c_i').val("");
            }

            if ($('.sub-field').length > 0) {
                var s_slug = connector_field.data('s_slug');
                var s_data_db = connector_field.data('s_db');
                var s_data_saved = connector_field.data('s_saved');
                var s_selected_task = wp.template(s_slug);
                wfco_make_html(1, '.wfco-fields-meta', s_selected_task({ajax_data: s_data_db}));

                if ((s_data_saved in s_data_db) && s_data_saved != '') {
                    $('.wfco-fields-meta select').val(s_data_saved);
                } else {
                    $('.wfco-fields-meta select').val("");
                }
            }
        }
    }

    function wfco_load_int_settings() {
        var selected_value = wfco_getUrlParameter('int');
        var $this = jQuery(this);
        if (selected_value != '') {
            var selected_connector = wp.template('connector-' + selected_value);
            jQuery('#wfco_connector_edit_fields').html('');
            wfco_make_html(1, '#wfco_connector_edit_fields', selected_connector());
        } else {
            jQuery('#wfco_connector_edit_fields').html('');
        }
    }

    function wfco_make_html(empty_old_html, container_element, new_html) {
        var output_container = $(container_element);
        if (empty_old_html == 1) {
            $(container_element).html('');
            var output_container_html = $(container_element).html();
            output_container.html(output_container_html + new_html);
        } else if (empty_old_html == 2) {
            output_container.append(new_html);
        }
    }

    function wfco_getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    function copyToClipboard(elem) {
        var targetId = "_hiddenCopyText_";
        var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
        var origSelectionStart, origSelectionEnd;
        if (isInput) {
            target = elem;
            origSelectionStart = elem.selectionStart;
            origSelectionEnd = elem.selectionEnd;
        } else {
            target = document.getElementById(targetId);
            if (!target) {
                var target = document.createElement("textarea");
                target.style.position = "absolute";
                target.style.left = "-9999px";
                target.style.top = "0";
                target.id = targetId;
                document.body.appendChild(target);
            }
            target.textContent = elem.textContent;
        }
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);
        var succeed;
        try {
            succeed = document.execCommand("copy");
            jQuery.toast({
                heading: wfcoParams.texts.text_copied,
                // text: 'Text Copied',
                position: 'bottom-right',
                // icon: 'warning',
                // stack: false,
                allowToastClose: false,
            });
        } catch (e) {
            succeed = false;
        }
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }
        if (isInput) {
            elem.setSelectionRange(origSelectionStart, origSelectionEnd);
        } else {
            target.textContent = "";
        }
        return succeed;
    }
})(jQuery);
