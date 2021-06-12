var BWFAN_Public;
(function ($) {
    "use strict";
    /* Serialize the form object */
    $.fn.bwfanac_serializeAndEncode = function () {
        return $.map(this.serializeArray(), function (val) {
                var field = $("input[name='" + val.name + "']");
                if (field.attr('type') == 'checkbox') {
                    if (field.prop("checked")) {
                        return [val.name, encodeURIComponent('1')].join('=');
                    } else {
                        return [val.name, encodeURIComponent('0')].join('=');
                    }
                } else {
                    return [val.name, encodeURIComponent(val.value)].join('=');
                }
            }
        ).join('&');
    };

    BWFAN_Public = {

        checkout_form: $('form.checkout'),
        last_edit_field: '',
        current_step: '',
        checkout_fields_data: {},
        capture_email_xhr: null,
        checkout_fields: [],
        updateCheckout: 0,

        init: function () {
            this.checkout_fields = [
                'billing_first_name',
                'billing_last_name',
                'billing_company',
                'billing_phone',
                'billing_country',
                'billing_address_1',
                'billing_address_2',
                'billing_city',
                'billing_state',
                'billing_postcode',
                'shipping_first_name',
                'shipping_last_name',
                'shipping_company',
                'shipping_country',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_postcode',
            ];

            if (bwfanParamspublic.bwfan_custom_checkout_field != undefined && bwfanParamspublic.bwfan_custom_checkout_field != null) {
                this.checkout_fields = _.union(this.checkout_fields, bwfanParamspublic.bwfan_custom_checkout_field);
            }

            $.each(BWFAN_Public.checkout_fields, function (i, field_name) {
                BWFAN_Public.checkout_fields_data[field_name] = '';
            });

            this.checkout_form.find('input,select').on('change', function () {
                var id = $(this).attr('id');
                if ("undefined" !== id) {
                    BWFAN_Public.last_edit_field = id;
                }
            });


        },
        bwfan_check_for_checkout_fields: function () {
            if ($('.wfacp_page').length === 0) {
                return;
            }
            var checkout_fields_data = bwfanParamspublic.bwfan_checkout_js_data;
            if ('no' === checkout_fields_data) {
                return;
            }
            var localstorage_data = window.localStorage.getItem('wfacp_form_values');
            if (null == localstorage_data) {
                localstorage_data = "";
            }
            var final_data = checkout_fields_data.fields;

            if ('' !== localstorage_data) {
                localstorage_data = JSON.parse(localstorage_data);
                for (var key in localstorage_data) {

                    if (localstorage_data.hasOwnProperty(key)) {
                        final_data[key] = localstorage_data[key];
                    }
                }

            }
            if (final_data.hasOwnProperty('shipping_method')) {
                delete final_data.shipping_method;
            }

            if (final_data.hasOwnProperty('ship_to_different_address')) {
                $('#ship-to-different-address-checkbox').prop('checked', true).trigger('change');
                delete final_data.shipping_method;
            }

            window.localStorage.setItem('wfacp_form_values', JSON.stringify(final_data));
            populate_fields_value();
        },
        urlParam: function (name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            var value = 'undefined';

            if (null !== results) {
                value = results[1];
            }

            return value;
        },
        bwfan_capture_email: function () {
            $(document).on('focusout', '#billing_email', function () {
                    BWFAN_Public.bwfan_get_checkout_data();
                }
            );

            var billing_email = jQuery('#billing_email').val();
            if (billing_email !== '' && BWFAN_Public.bwfan_isValidEmailAddress(billing_email)) {
                BWFAN_Public.bwfan_capture_data_on_page_load();
                BWFAN_Public.bwfan_process_email(billing_email);
            }
        },
        bwfan_get_checkout_data: function () {
            var email = $('#billing_email').val();
            if (email !== '' && BWFAN_Public.bwfan_isValidEmailAddress(email)) {
                BWFAN_Public.bwfan_process_email(email);
            }
        },
        bwfan_capture_data_on_page_load: function () {
            $.each(BWFAN_Public.checkout_fields, function (i, field_name) {
                var $this = $('#' + field_name);
                BWFAN_Public.checkout_fields_data[field_name] = $this.val();
            });
        },
        bwfan_captureCheckoutField: function () {

            if (BWFAN_Public.updateCheckout === 0) {
                return;
            }
            var field_name = $(this).attr('name');
            /** for checking checkbox fields **/
            if ($(this).attr('type') == 'checkbox') {
                if ($(this).prop('checked')) {
                    $(this).val(1);
                } else {
                    $(this).val(0);
                }
            }
            if (!field_name || BWFAN_Public.checkout_fields.indexOf(field_name) === -1) {
                return;
            }

            if (!$(this).val() || BWFAN_Public.checkout_fields_data[field_name] === $(this).val()) {
                return;
            }

            var checkout_formdata = BWFAN_Public.checkout_form.bwfanac_serializeAndEncode();
            checkout_formdata = bwfanac_deserialize_obj(checkout_formdata);
            BWFAN_Public.checkout_fields_data = checkout_formdata;

        },
        bwfan_process_email: function (email = '') {
            if (('0' == bwfanParamspublic.bwfan_ab_enable || bwfanParamspublic.bwfan_ab_enable == "")) {
                return;
            }

            if (BWFAN_Public.updateCheckout === 0) {
                return;
            }

            /**
             * Removed abort as it was making a loop when bonanza is enabled
             */
            if (null !== BWFAN_Public.capture_email_xhr) {
                return;
            }
            if ('' === email) {
                return;
            }
            if ($('#bwfan_email_consent').length && 1 != $('#bwfan_email_consent').val()) {
                return;
            }

            var aero_id = ($('#wfacp_aero_checkout_id').length > 0) ? $('#wfacp_aero_checkout_id').attr('content') : '';
            var step = '';
            if (aero_id) {
                step = (BWFAN_Public.current_step) ? BWFAN_Public.current_step : 'single_step';
            }

            var timezone = '';

            if (typeof Intl === "object" || typeof Intl.DateTimeFormat() === "object") {
                let resolved = Intl.DateTimeFormat().resolvedOptions();
                if (resolved.hasOwnProperty('timeZone')) {
                    timezone = resolved.timeZone;
                }
            }

            BWFAN_Public.capture_email_xhr = $.post(bwfanParamspublic.wc_ajax_url.toString().replace('%%endpoint%%', 'bwfan_insert_abandoned_cart'), {
                    'email': email,
                    'action': 'bwfan_insert_abandoned_cart',
                    'checkout_fields_data': BWFAN_Public.checkout_fields_data,
                    'last_edit_field': BWFAN_Public.last_edit_field,
                    'current_step': step,
                    'current_page_id': bwfanParamspublic.current_page_id,
                    'timezone': timezone,
                    'aerocheckout_page_id': aero_id,
                    '_wpnonce': bwfanParamspublic.ajax_nonce
                }, function () {
                    BWFAN_Public.capture_email_xhr = null;
                }
            );
        },
        bwfan_isValidEmailAddress: function (emailAddress) {
            var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);

            return pattern.test(emailAddress);
        }
    };

    /**
     * Abandoned cart JS Function Initiate here
     */
    BWFAN_Public.abandoned_cart = function () {
        if ('0' != bwfanParamspublic.bwfan_ab_enable && bwfanParamspublic.bwfan_ab_enable != "") {
            BWFAN_Public.bwfan_capture_email();
        }
    };

    BWFAN_Public.get_selected_unsubscribe_lists = function () {
        const enabled = $('#bwfan-unsubscribe-lists').length > 0;
        if (!enabled) {
            return [];
        }

        const lists = [];
        $('.bwfan-unsubscribe-list-checkbox input').each(function () {
            if ($(this).attr('id') === 'bwfan-list-unsubscribe-all') {
                $(this).is(':checked') && lists.push('all');
                return true;
            }

            if (!$(this).is(':checked')) {
                lists.push(parseInt($(this).val()));
            }
        });
        return lists;
    };

    /**
     * Unsubscribe event to unsubscribe user on a link click
     */
    BWFAN_Public.unsubscribe_event = function () {
        $('#bwfan_unsubscribe').on('click', function (event) {
            event.preventDefault();
            var $this = $(this);
            if ($this.hasClass('bwfan_loading')) {
                return;
            }
            $this.addClass('bwfan_loading');

            var urlParams = new URLSearchParams(window.location.search);

            var recipient = $('#bwfan_unsubscribe_recipient');
            recipient = (0 === recipient.length) ? urlParams.get('subscriber_recipient') : recipient.html();

            var automation_id = $('#bwfan_automation_id');
            automation_id = (0 === automation_id.length) ? urlParams.get('automation_id') : automation_id.val();

            var broadcast_id = $('#bwfan_broadcast_id');
            broadcast_id = (0 === broadcast_id.length) ? urlParams.get('broadcast_id') : broadcast_id.val();

            var form_feed_id = $('#bwfan_form_feed_id');
            form_feed_id = (0 === form_feed_id.length) ? urlParams.get('form_feed_id') : form_feed_id.val();

            var bwfan_nonce = $('#bwfan_unsubscribe_nonce');

            if (0 === recipient.length || (!!parseInt(automation_id) && !!parseInt(broadcast_id))) {
                return;
            }

            let lists = BWFAN_Public.get_selected_unsubscribe_lists();
            if (lists.includes('all')) {
                $('.bwfan-unsubscribe-list-checkbox input').removeAttr('checked');
                $('#bwfan-list-unsubscribe-all').prop('checked', true);
            }
            lists = JSON.stringify(lists);

            $.ajax({
                method: 'post',
                dataType: 'json',
                url: bwfanParamspublic.ajax_url,
                data: {
                    action: 'bwfan_unsubscribe_user',
                    recipient: recipient,
                    automation_id: automation_id,
                    broadcast_id: broadcast_id,
                    form_feed_id: form_feed_id,
                    unsubscribe_lists: lists,
                    _nonce: bwfan_nonce.val(),
                },
                success: function (result) {
                    $this.removeClass('bwfan_loading');

                    var response_generated = $this.parent().find('.bwfan_response').html();
                    if ('undefined' === typeof response_generated) {
                        $this.after("<div class='bwfan_response'></div>");
                    }

                    if (0 === result.success) {
                        $('.bwfan_response').addClass('bwfan_error');
                        $('.bwfan_response').fadeIn().html(result.message);
                        setTimeout(function () {
                            $('.bwfan_response').fadeOut("slow");
                        }, 2500);

                        return;
                    }

                    $('.bwfan_response').addClass('bwfan_success');
                    $('.bwfan_response').fadeIn().html(result.message);
                    setTimeout(function () {
                        $('.bwfan_response').fadeOut("slow");
                    }, 2500);
                }
            });
        });
    };

    /* Initialize */
    BWFAN_Public.init();

    $(document).ready(function () {
        if ('0' != bwfanParamspublic.bwfan_ab_enable && bwfanParamspublic.bwfan_ab_enable != '') {
            var bwfan_email_consent_message = '';
            if ('0' != bwfanParamspublic.bwfan_ab_email_consent && bwfanParamspublic.bwfan_ab_email_consent != '') {
                bwfan_email_consent_message = '<label>' + bwfanParamspublic.bwfan_ab_email_consent_message + '</label>';

                // Remove/Strip slashes
                bwfan_email_consent_message = bwfan_email_consent_message.replace(new RegExp("\\\\", "g"), "");
                var start = bwfan_email_consent_message.search("{{no_thanks label=");
                var end = bwfan_email_consent_message.search("}}");
                if (-1 != start && -1 != end) {
                    var temp_start = start + 19;
                    var temp_end = end - 1;
                    var no_thanks_merge_tag = bwfan_email_consent_message.substring(start, (end + 2));
                    var no_thanks_label = bwfan_email_consent_message.substring(temp_start, temp_end);
                    no_thanks_label = no_thanks_label ? no_thanks_label : bwfanParamspublic.bwfan_no_thanks;
                    bwfan_email_consent_message = bwfan_email_consent_message.replace(no_thanks_merge_tag, "<a class='bwfan_email_consent_no_thanks' style='text-decoration:underline;cursor: pointer;'>" + no_thanks_label + "</a>");
                } else {
                    bwfan_email_consent_message = bwfan_email_consent_message.replace("{{no_thanks}}", "<a class='bwfan_email_consent_no_thanks' style='text-decoration:underline;cursor: pointer;'>" + bwfanParamspublic.bwfan_no_thanks + "</a>");
                }
            }

            var emailConsentHtml = '<input type="hidden" id="bwfan_email_consent" value="1" />';
            if ('' === bwfan_email_consent_message) {
                $('#billing_email_field').after(emailConsentHtml);
            } else {
                bwfan_email_consent_message += emailConsentHtml;
                $('#billing_email_field').after('<p class="form-row form-row-wide wfacp-form-control-wrapper wfacp-col-full wfacp-anim-wrap">' + bwfan_email_consent_message + '</p>');
            }

            $('.bwfan_email_consent_no_thanks').on('click', function (event) {
                event.preventDefault();

                $.post(bwfanParamspublic.wc_ajax_url.toString().replace('%%endpoint%%', 'bwfan_delete_abandoned_cart'), {
                        'email': $('#billing_email').val(),
                        'action': 'bwfan_delete_abandoned_cart',
                        '_wpnonce': bwfanParamspublic.ajax_nonce
                    }, function () {
                        //
                    }
                );
                $('#bwfan_email_consent').val('0');
                $(this).parent().fadeOut("slow");
            });
        }
    });

    $(window).on('load', function () {
        BWFAN_Public.abandoned_cart();
        BWFAN_Public.unsubscribe_event();
        BWFAN_Public.bwfan_check_for_checkout_fields();
        $(document.body).on('wfacp_step_switching', function (e, v) {

            BWFAN_Public.current_step = v.current_step;
            BWFAN_Public.bwfan_process_email($('#billing_email').val());
        });

        /**
         * Detect change and save data in database
         */
        BWFAN_Public.checkout_form.on('change', 'select', BWFAN_Public.bwfan_captureCheckoutField);
        BWFAN_Public.checkout_form.on('click change', '.input-checkbox', BWFAN_Public.bwfan_captureCheckoutField);
        BWFAN_Public.checkout_form.on('blur change', '.input-text', BWFAN_Public.bwfan_captureCheckoutField);
        BWFAN_Public.checkout_form.on('focusout', '.input-text', BWFAN_Public.bwfan_captureCheckoutField);
        $(document).on('blur change', '#billing_email,.input-text,.input-checkbox', BWFAN_Public.bwfan_get_checkout_data);
    });

    $(document).on('updated_checkout', function () {
        /** Update Checkout is triggered */
        BWFAN_Public.updateCheckout = 1;

        BWFAN_Public.bwfan_captureCheckoutField();
        BWFAN_Public.bwfan_process_email($('#billing_email').val());
    });


})(jQuery);

/* Deserialize the form object */
function bwfanac_deserialize_obj(query) {
    var setValue = function (root, path, value) {
        if (path.length > 1) {
            var dir = path.shift();
            if (typeof root[dir] == 'undefined') {
                root[dir] = path[0] === '' ? [] : {};
            }

            arguments.callee(root[dir], path, value);
        } else {
            if (root instanceof Array) {
                root.push(value);
            } else {
                root[path] = value;
            }
        }
    };

    var nvp = query.split('&');
    var data = {};

    for (var i = 0; i < nvp.length; i++) {
        var pair = nvp[i].split('=');
        var name = decodeURIComponent(pair[0]);
        var value = decodeURIComponent(pair[1]);

        var path = name.match(/(^[^\[]+)(\[.*\]$)?/);
        var first = path[1];
        if (path[2]) {
            //case of 'array[level1]' || 'array[level1][level2]'
            path = path[2].match(/(?=\[(.*)\]$)/)[1].split('][')
        } else {
            //case of 'name'
            path = [];
        }
        path.unshift(first);

        setValue(data, path, value);
    }

    return data;
}
