/*global jQuery*/
/*global Vue*/
/*global VueFormGenerator*/
/*global bwfAdminGen*/


(function ($, doc, win) {
    'use strict';
    Vue.component('multiselect', window.VueMultiselect.default);

    Vue.component("field-desc", {
        mixins: [VueFormGenerator.abstractField],
        template: '<div class="">' + bwfAdminGen.texts.permalink_help_text + '</div>',
        mounted: function () {
        },
    });
    let bwfBuilderCommons = {
        hooks: {action: {}, filter: {}},
        addAction: function (action, callable, priority, tag) {
            this.addHook('action', action, callable, priority, tag);
        },
        addFilter: function (action, callable, priority, tag) {
            this.addHook('filter', action, callable, priority, tag);
        },
        doAction: function (action) {
            this.doHook('action', action, arguments);
        },
        applyFilters: function (action) {
            return this.doHook('filter', action, arguments);
        },
        removeAction: function (action, tag) {
            this.removeHook('action', action, tag);
        },
        removeFilter: function (action, priority, tag) {
            this.removeHook('filter', action, priority, tag);
        },
        addHook: function (hookType, action, callable, priority, tag) {
            if (undefined == this.hooks[hookType][action]) {
                this.hooks[hookType][action] = [];
            }
            var hooks = this.hooks[hookType][action];
            if (undefined == tag) {
                tag = action + '_' + hooks.length;
            }
            if (priority == undefined) {
                priority = 10;
            }

            this.hooks[hookType][action].push({tag: tag, callable: callable, priority: priority});
        },
        doHook: function (hookType, action, args) {

            // splice args from object into array and remove first index which is the hook name
            args = Array.prototype.slice.call(args, 1);
            if (undefined !== this.hooks[hookType][action]) {
                var hooks = this.hooks[hookType][action], hook;
                //sort by priority
                hooks.sort(
                    function (a, b) {
                        return a["priority"] - b["priority"]
                    }
                );
                for (var i = 0; i < hooks.length; i++) {
                    hook = hooks[i].callable;
                    if (typeof hook != 'function') {
                        hook = window[hook];
                    }
                    if ('action' === hookType) {
                        hook.apply(null, args);
                    } else {
                        args[0] = hook.apply(null, args);
                    }
                }
            }
            if ('filter' === hookType) {
                return args[0];
            }
        },
        removeHook: function (hookType, action, priority, tag) {
            if (undefined !== this.hooks[hookType][action]) {
                var hooks = this.hooks[hookType][action];
                for (var i = hooks.length - 1; i >= 0; i--) {
                    if ((undefined === tag || tag == hooks[i].tag) && (undefined === priority || priority === hooks[i].priority)) {
                        hooks.splice(i, 1);
                    }
                }
            }
        }
    };

    let bwfAdminBuilder = function () {
        /****** Declaring vue objects ******/
        this.bwf_flex_vue = null;
        this.bwf_popups_vue = null;

        if ($('#modal-general-settings_success').length > 0) {
            $("#modal-general-settings_success").iziModal(
                {
                    title: bwfAdminGen.texts.settings_success,
                    icon: 'icon-check',
                    headerColor: '#6dbe45',
                    background: '#efefef',
                    borderBottom: false,
                    width: 600,
                    timeout: 4000,
                    timeoutProgressbar: true,
                    transitionIn: 'fadeInUp',
                    transitionOut: 'fadeOutDown',
                    bottom: 0,
                    loop: true,
                    pauseOnHover: true,
                    overlay: false
                }
            );
        }


        function get_funnel_settings_fields() {

            /**
             * handling of localized label/description coming from php to form fields in vue
             */
            /**
             * handling of localized label/description coming from php to form fields in vue
             */
            let global_settings_fb_fields = [
                /** Order section ended **/
                /** Common settings started **/
                {
                    type: "input",
                    inputType: "text",
                    label: "",
                    model: "fb_pixel_key",
                    inputName: 'fb_pixel_key',
                },

                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_purchase_conversion_api",
                    inputName: 'is_fb_purchase_conversion_api',

                },
                {
                    type: "textArea",
                    model: "conversion_api_access_token",
                    inputName: 'conversion_api_access_token',
                    visible: function (modal) {
                        return (modal.is_fb_purchase_conversion_api.length > 0);
                    }
                },

                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_conv_enable_test",
                    inputName: 'is_fb_conv_enable_test',
                    visible: function (modal) {
                        return (modal.is_fb_purchase_conversion_api.length > 0);
                    }
                },
                {
                    type: "input",
                    inputType: "text",
                    model: "conversion_api_test_event_code",
                    inputName: 'conversion_api_test_event_code',
                    visible: function (modal) {
                        return (modal.is_fb_conv_enable_test.length > 0);
                    }
                },
                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_conversion_api_log",
                    inputName: 'is_fb_conversion_api_log',
                    visible: function (modal) {
                        return (modal.is_fb_purchase_conversion_api.length > 0);
                    }
                },

                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_page_view_lp",
                    inputName: 'is_fb_page_view_lp',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_landing_enabled;
                    }
                },
                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_page_view_op",
                    inputName: 'is_fb_page_view_op',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_optin_enabled;
                    }
                },
                /** AERO settings started **/
                {
                    type: "label",
                    label: "",
                    styleClasses: "",
                    model: "label_section_head_fb",
                    inputName: 'label_section_head_fb',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                },
                {
                    type: "checkbox",
                    inputType: "text",
                    model: "pixel_is_page_view",
                    inputName: 'pixel_is_page_view',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                },
                {
                    type: "checkbox",
                    inputType: "text",
                    model: "pixel_initiate_checkout_event",
                    inputName: 'pixel_initiate_checkout_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                }, {
                    type: "checkbox",
                    inputType: "text",
                    model: "pixel_add_to_cart_event",
                    inputName: 'pixel_add_to_cart_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                }, {
                    type: "checkbox",
                    inputType: "text",
                    model: "pixel_add_payment_info_event",
                    inputName: 'pixel_add_payment_info_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                }, {
                    type: "checkbox",
                    inputType: "text",
                    model: "pixel_variable_as_simple",
                    inputName: 'pixel_variable_as_simple',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                }, {
                    type: "select",
                    model: "pixel_content_id_type",
                    inputName: 'pixel_content_id_type',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                }, {
                    type: "input",
                    inputType: "text",
                    model: "pixel_content_id_prefix",
                    inputName: 'pixel_content_id_prefix',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                }, {
                    type: "input",
                    inputType: "text",
                    model: "pixel_content_id_suffix",
                    inputName: 'pixel_content_id_suffix',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                },
                {
                    type: "checkbox",
                    inputType: "text",
                    model: "pixel_exclude_tax",
                    inputName: 'pixel_exclude_tax',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_checkout_enabled;
                    }
                },
                /** AERO settings ENDS **/
                /** UPSTROKE settings STARTS **/
                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_purchase_page_view",
                    inputName: 'is_fb_purchase_page_view',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_purchase_enabled;
                    }
                },
                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_purchase_event",
                    inputName: 'is_fb_purchase_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_purchase_enabled;
                    }
                },


                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "custom_aud_opt_conf",
                    inputName: 'custom_aud_opt_conf',
                    visible: function (modal) {
                        return ('1' === bwfAdminGen.if_fb_purchase_enabled && modal.is_fb_purchase_event.length > 0);
                    },

                },

                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "exclude_from_total",
                    inputName: 'exclude_from_total',
                    visible: function (modal) {
                        return ('1' === bwfAdminGen.if_fb_purchase_enabled && modal.is_fb_purchase_event.length > 0);
                    },

                },

                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_synced_event",
                    inputName: 'is_fb_synced_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_purchase_enabled;
                    }

                },
                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "content_id_variable",
                    inputName: 'content_id_variable',
                    visible: function (modal) {
                        return ('1' === bwfAdminGen.if_fb_purchase_enabled && modal.is_fb_synced_event.length > 0);
                    },

                },

                {
                    type: "select",
                    label: "",
                    model: "content_id_value",
                    inputName: 'content_id_value',
                    selectOptions: {hideNoneSelectedText: true},
                    visible: function (modal) {

                        return ('1' === bwfAdminGen.if_fb_purchase_enabled && modal.is_fb_synced_event.length > 0);
                    },

                },

                {
                    type: "input",
                    inputType: "text",
                    model: "content_id_prefix",
                    inputName: 'content_id_prefix',
                    visible: function (modal) {
                        return ('1' === bwfAdminGen.if_fb_purchase_enabled && modal.is_fb_synced_event.length > 0);
                    },


                },
                {
                    type: "input",
                    inputType: "text",
                    model: "content_id_suffix",
                    inputName: 'content_id_suffix',
                    visible: function (modal) {
                        return ('1' === bwfAdminGen.if_fb_purchase_enabled && modal.is_fb_synced_event.length > 0);
                    },


                },
                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "enable_general_event",
                    inputName: 'enable_general_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_purchase_enabled;
                    }

                },


                {
                    type: "input",
                    inputType: "text",
                    model: "general_event_name",
                    inputName: 'general_event_name',
                    visible: function (modal) {
                        return ('1' === bwfAdminGen.if_fb_purchase_enabled && modal.enable_general_event.length);
                    }

                },
                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_fb_advanced_event",
                    inputName: 'is_fb_advanced_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_purchase_enabled;
                    }

                },
                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "track_traffic_source",
                    inputName: 'track_traffic_source',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_fb_purchase_enabled;
                    }

                },
            ];

            let global_settings_ga_fields = [

                {
                    type: "input",
                    inputType: "text",
                    label: "",
                    model: "ga_key",
                    inputName: 'ga_key',

                },
                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_ga_page_view_lp",
                    inputName: 'is_ga_page_view_lp',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_landing_enabled;
                    }
                },
                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_ga_page_view_op",
                    inputName: 'is_ga_page_view_op',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_optin_enabled;
                    }
                },
                {
                    type: "label",
                    label: "",
                    styleClasses: "",
                    model: "label_section_head_ga",
                    inputName: 'label_section_head_ga',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                },
                {
                    type: "checkbox",
                    inputType: "text",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "google_ua_is_page_view",
                    inputName: 'google_ua_is_page_view',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                },
                {
                    type: "checkbox",
                    inputType: "text",
                    model: "google_ua_add_to_cart_event",
                    inputName: 'google_ua_add_to_cart_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                }, {
                    type: "checkbox",
                    inputType: "text",
                    model: "google_ua_initiate_checkout_event",
                    inputName: 'google_ua_initiate_checkout_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                }, {
                    type: "checkbox",
                    inputType: "text",
                    model: "google_ua_add_payment_info_event",
                    inputName: 'google_ua_add_payment_info_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                },
                {
                    type: "checkbox",
                    inputType: "text",
                    model: "google_ua_exclude_tax",
                    inputName: 'google_ua_exclude_tax',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                },
                {
                    type: "checkbox",
                    inputType: "text",
                    model: "google_ua_variable_as_simple",
                    inputName: 'google_ua_variable_as_simple',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                }, {
                    type: "select",
                    model: "google_ua_content_id_type",
                    inputName: 'google_ua_content_id_type',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                }, {
                    type: "input",
                    inputType: "text",
                    model: "google_ua_content_id_prefix",
                    inputName: 'google_ua_content_id_prefix',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                }, {
                    type: "input",
                    inputType: "text",
                    model: "google_ua_content_id_suffix",
                    inputName: 'google_ua_content_id_suffix',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_checkout_enabled;
                    }
                },
                {
                    type: "checklist",
                    listBox: true,
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_ga_purchase_page_view",
                    inputName: 'is_ga_purchase_page_view',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_purchase_enabled;
                    }
                },
                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "is_ga_purchase_event",
                    inputName: 'is_ga_purchase_event',
                    visible: function (modal) {
                        return '1' === bwfAdminGen.if_ga_purchase_enabled;
                    }

                },
                {
                    type: "checklist",
                    listBox: true,
                    label: "",
                    styleClasses: "wfocu_gsettings_sec_chlist",
                    model: "ga_track_traffic_source",
                    inputName: 'ga_track_traffic_source',
                    visible: function (modal) {
                        return ('1' === bwfAdminGen.if_ga_purchase_enabled && modal.is_ga_purchase_event.length > 0);
                    },
                },

            ];

            for (let keyfields in global_settings_fb_fields) {
                let model = global_settings_fb_fields[keyfields].model;
                for (var k in bwfAdminGen.globalOptionsFields.fields['facebook_pixel'].fields) {
                    if (bwfAdminGen.globalOptionsFields.fields['facebook_pixel'].fields[k].key === model) {
                        $.extend(global_settings_fb_fields[keyfields], bwfAdminGen.globalOptionsFields.fields['facebook_pixel'].fields[k]);
                    }
                }

            }
            for (let keyfields in global_settings_ga_fields) {
                let model = global_settings_ga_fields[keyfields].model;

                for (var k in bwfAdminGen.globalOptionsFields.fields['google_analytics'].fields) {
                    if (bwfAdminGen.globalOptionsFields.fields['google_analytics'].fields[k].key === model) {
                        $.extend(global_settings_ga_fields[keyfields], bwfAdminGen.globalOptionsFields.fields['google_analytics'].fields[k]);
                    }
                }
            }

            let global_settings_permalinks_fields = bwfBuilderCommons.applyFilters('bwf_common_permalinks_fields', []);
            for (let keyfields in global_settings_permalinks_fields) {
                let model = global_settings_permalinks_fields[keyfields].model;
                for (var k in bwfAdminGen.globalOptionsFields.fields['permalinks'].fields) {
                    if (bwfAdminGen.globalOptionsFields.fields['permalinks'].fields[k].key === model) {
                        $.extend(global_settings_permalinks_fields[keyfields], bwfAdminGen.globalOptionsFields.fields['permalinks'].fields[k]);
                    }
                }

            }
            // global_settings_permalinks_fields[global_settings_permalinks_fields.length] = {
            // 	type: "desc",
            // 	label: "",
            // 	styleClasses: "bwf_custom_desc"
            // };
            let settings_config = [
                {
                    legend: bwfAdminGen.globalOptionsFields.legends_texts.permalinks,
                    fields: global_settings_permalinks_fields
                },
                {
                    legend: bwfAdminGen.globalOptionsFields.legends_texts.fb,
                    fields: global_settings_fb_fields
                }, {
                    legend: bwfAdminGen.globalOptionsFields.legends_texts.ga,
                    fields: global_settings_ga_fields
                },


            ];
            if ('1' === bwfAdminGen.is_gad_enabled) {
                let global_settings_gad_fields = [

                    {
                        type: "input",
                        inputType: "text",
                        label: "",
                        model: "gad_key",
                        inputName: 'gad_key',

                    },
                    {
                        type: "input",
                        inputType: "text",
                        model: "gad_conversion_label",
                        inputName: 'gad_conversion_label',


                    },
                    {
                        type: "checklist",
                        listBox: true,
                        label: "",
                        styleClasses: "wfocu_gsettings_sec_chlist",
                        model: "is_gad_purchase_event",
                        inputName: 'is_gad_purchase_event',

                    },

                    {
                        type: "checklist",
                        listBox: true,
                        label: "",
                        styleClasses: "wfocu_gsettings_sec_chlist",
                        model: "gad_exclude_from_total",
                        inputName: 'gad_exclude_from_total',
                        visible: function (modal) {
                            return (modal.is_gad_purchase_event.length > 0);
                        },

                    },

                    {
                        type: "input",
                        inputType: "text",
                        model: "id_prefix_gad",
                        inputName: 'id_prefix_gad',
                        visible: function (modal) {
                            return (modal.is_gad_purchase_event.length > 0);
                        },

                    },
                    {
                        type: "input",
                        inputType: "text",
                        model: "id_suffix_gad",
                        inputName: 'id_suffix_gad',
                        visible: function (modal) {
                            return (modal.is_gad_purchase_event.length > 0);
                        },


                    },


                ];
                for (let keyfields in global_settings_gad_fields) {
                    let model = global_settings_gad_fields[keyfields].model;

                    for (var k in bwfAdminGen.globalOptionsFields.fields['google_ads'].fields) {
                        if (bwfAdminGen.globalOptionsFields.fields['google_ads'].fields[k].key === model) {
                            $.extend(global_settings_gad_fields[keyfields], bwfAdminGen.globalOptionsFields.fields['google_ads'].fields[k]);
                        }
                    }
                }
                settings_config.push({
                    legend: bwfAdminGen.globalOptionsFields.legends_texts.gad,
                    fields: global_settings_gad_fields
                });
            }

            if ('1' === bwfAdminGen.is_pinterest_enabled) {


                let global_settings_pint_fields = [

                    {
                        type: "input",
                        inputType: "text",
                        label: "",
                        model: "pint_key",
                        inputName: 'pint_key',


                    },

                    {
                        type: "checklist",
                        listBox: true,
                        styleClasses: "wfocu_gsettings_sec_chlist",
                        model: "is_pint_purchase_event",
                        inputName: 'is_pint_purchase_event',


                    },
                ];
                for (let keyfields in global_settings_pint_fields) {
                    let model = global_settings_pint_fields[keyfields].model;

                    for (var k in bwfAdminGen.globalOptionsFields.fields['pinterest'].fields) {
                        if (bwfAdminGen.globalOptionsFields.fields['pinterest'].fields[k].key === model) {
                            $.extend(global_settings_pint_fields[keyfields], bwfAdminGen.globalOptionsFields.fields['pinterest'].fields[k]);
                        }
                    }
                }
                settings_config.push({
                    legend: bwfAdminGen.globalOptionsFields.legends_texts.pint,
                    fields: global_settings_pint_fields
                });
            }


            return settings_config;
        }

        new Vue(
            {
                el: "#bwf_general_settings_vue_wrap",
                components: {
                    "vue-form-generator": VueFormGenerator.component,
                    Multiselect: window.VueMultiselect.default
                },
                methods: {
                    onSubmit: function () {
                        this.errorMsg = '';
                        try {
                            this.checkCheckoutSlug();
                            this.checkEmptyslugs();
                            $(".bwf_save_btn_style").addClass('disabled');
                            $('.bwf_loader_global_save').addClass('ajax_loader_show');
                            let tempSetting = JSON.stringify(this.model);
                            tempSetting = JSON.parse(tempSetting);
                            let data = {"data": tempSetting, '_nonce': bwfAdminGen.nonce_general_settings};
                            data.action = 'bwf_general_settings_update';
                            $.post(window.ajaxurl, data, function (rsp) {
                                if (typeof rsp === "string") {
                                    rsp = JSON.parse(rsp);
                                }
                                $('#modal-general-settings_success').iziModal('open');
                                $(".bwf_save_btn_style").removeClass('disabled');
                                $('.bwf_loader_global_save').removeClass('ajax_loader_show');
                            });
                        } catch (e) {
                            this.errorMsg = e;
                        }

                        return false;
                    },
                    checkCheckoutSlug: function () {
                        if (this.model.checkout_page_base === bwfAdminGen.checkout_page_slug) {
                            throw(bwfAdminGen.errors.checkout_slug);
                        }
                    },
                    checkEmptyslugs: function () {
                        var anyEmpty = false;
                        var inst = this;
                        var urlbase = ['landing_page_base', 'checkout_page_base', 'optin_page_base', 'optin_ty_page_base', 'wfocu_page_base', 'ty_page_base'];

                        for (var i in urlbase) {
                            if ($.inArray(urlbase[i], Object.keys(inst.model)) && '' === inst.model[urlbase[i]]) {
                                anyEmpty = true;
                                break;
                            }
                        }
                        console.log(anyEmpty);
                        if (true === anyEmpty && '/%postname%/' !== bwfAdminGen.permalink_structure) {
                            throw(bwfAdminGen.errors.empty_base);
                        }
                    },
                },

                created: function () {
                    setTimeout(function () {
                        $('.form-group.field-checkbox').each(function () {
                            let label = $(this).find('label');
                            let label_text = label.text();
                            label.remove();
                            $(this).find('.field-wrap').append(label_text);
                        });

                    }, 1000);
                },
                data: {
                    model: bwfAdminGen.globalOptionsFields.options,
                    schema: {
                        groups: get_funnel_settings_fields(),

                    },
                    errorMsg: '',
                    formOptions: {
                        validateAfterLoad: false,
                        validateAfterChanged: true
                    },
                    is_initialized: '1',
                }
            }
        );

        function bwf_tabs() {

            let wfctb = $('.bwf-widget-tabs .bwf-tab-title');
            $(document.body).on(
                'click', '.bwf-widget-tabs .bwf-tab-title',
                function () {
                    let $this = $(this).closest('.bwf-widget-tabs');
                    let tabindex = $(this).attr('data-tab');

                    $this.find('.bwf-tab-title').removeClass('bwf-active');

                    $this.find('.bwf-tab-title[data-tab=' + tabindex + ']').addClass('bwf-active');

                    $($this).find('.bwf_forms_wrap .vue-form-generator fieldset').removeClass('bwf-activeTab');
                    $($this).find('.bwf_forms_wrap .vue-form-generator fieldset').hide();
                    $($this).find('.bwf_forms_wrap .vue-form-generator fieldset').eq(tabindex - 1).addClass('bwf-activeTab');

                    $($this).find('.bwf_forms_wrap .vue-form-generator fieldset').eq(tabindex - 1).show();

                }
            );
            if (wfctb.length > 0) {
                wfctb.eq(0).trigger('click');
            }

        }

        bwf_tabs();
    };

    $(win).on('load',
        function () {
            window.bwfAdminBuilder = new bwfAdminBuilder();
        }
    );
    window.bwfBuilderCommons = bwfBuilderCommons;
})(jQuery, document, window);
///window.__VUE_DEVTOOLS_GLOBAL_HOOK__.Vue = window.constructor;
