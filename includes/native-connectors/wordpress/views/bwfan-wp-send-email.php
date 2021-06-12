<?php
$unique_slug = $this->get_slug();
?>
    <script type="text/html" id="tmpl-action-<?php esc_html_e( $unique_slug ); ?>">
        <#
        selected_event_src = BWFAN_Auto.uiDataDetail.trigger.source;
        selected_event = BWFAN_Auto.uiDataDetail.trigger.event;

        email_merge_tag = '';
        email_sub = '';
        email_body = '';

        ae = bwfan_automation_data.all_triggers_events;

        if(_.has(ae, selected_event_src) &&
        _.has(ae[selected_event_src], selected_event) &&
        _.has(ae[selected_event_src][selected_event], 'customer_email_tag')) {
        email_merge_tag = ae[selected_event_src][selected_event].customer_email_tag;
        }

        if(selected_event=='ab_cart_abandoned'){
        email_sub = 'We\'re still holding the cart for you';
        email_body = '<p>Hi {{cart_billing_first_name}},</p>' +
        "<p>I noticed that you were trying to purchase but couldn\'t complete the process.</p>" +
        "<p> {{cart_items template = 'cart-table'}} </p>"+
        '<p>We have reserved the cart for you, <a href="{{cart_recovery_link}}">Click here</a> to complete your purchase.</p>' +
        '<p>If you have any questions, feel free to get in touch with us.</p>' +
        '<p>Hit reply and I\'ll be happy to answer your questions.</p>' +
        '<p>Thanks!</p>';
        }

        selected_template = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'template')) ? data.actionSavedData.data.template : 'raw_template';

        is_enable_wysiwyg = ('raw_template' === selected_template || 'wc_template' === selected_template) ? '' : 'bwfan-display-none';
        is_enable_textarea = ('raw' === selected_template) ? '' : 'bwfan-display-none';

        email_heading = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'email_heading')) ? data.actionSavedData.data.email_heading : '';
        to = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'to')) ? data.actionSavedData.data.to : email_merge_tag;
        subject = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'subject')) ? data.actionSavedData.data.subject : email_sub;
        body = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'body')) ? data.actionSavedData.data.body : email_body;
        body_raw = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'body_raw')) ? data.actionSavedData.data.body_raw : email_body;

        show_email_heading = (selected_template=='wc_template' || selected_template=='') ? '' : 'bwfan-display-none';
        is_promotional = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'promotional_email')) ? 'checked' : '';
        is_append_utm = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'append_utm')) ? 'checked' : '';
        show_utm_parameters = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'append_utm')) ? '' : 'bwfan-display-none';

        entered_utm_source = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_source')) ? data.actionSavedData.data.utm_source : '';
        email_preheading = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'preheading')) ? data.actionSavedData.data.preheading : '';
        entered_utm_medium = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_medium')) ? data.actionSavedData.data.utm_medium : '';
        entered_utm_campaign = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_campaign')) ? data.actionSavedData.data.utm_campaign : '';
        entered_utm_term = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'utm_term')) ? data.actionSavedData.data.utm_term : '';

        #>
        <div data-element-type="bwfan-editor" data-temp="{{selected_template}}" class="bwfan-<?php esc_html_e( $unique_slug ); ?>">
            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'Template', 'wp-marketing-automations' ); ?>
				<?php
				$message = "<strong>Rich Text Template:</strong> " . __( "Use this template for more control over the email body. Rich text style is auto-applied.", 'wp-marketing-automations' );
				//$message .= "<br/><br/><strong>WooCommerce Template:</strong> " . __( "Use native WooCommerce header/footer template. Write content inside the email body using a combination of text and code.", 'wp-marketing-automations' );
				$message .= "<br/><br/><strong>Raw HTML Template:</strong> " . __( "Use this template for complete control by pasting any Custom HTML/CSS (usually designed using an external email editor).", 'wp-marketing-automations' );
				echo $this->add_description( $message, '3xl', 'right', false ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <#
                if(_.has(data.actionFieldsOptions, 'template_options') && _.isObject(data.actionFieldsOptions.template_options) ) {
                _.each( data.actionFieldsOptions.template_options, function( value, key ){
                if(selected_template != 'wc_template' && key == 'wc_template') {
                return;
                }
                selected = (key == selected_template) ? 'checked="checked"' : '';
                #>
                <label class="bwf-radio-button">
                    <input type="radio" name="bwfan[{{data.action_id}}][data][template]" value="{{key}}" {{selected}} class="bwfan_email_template bwf-radio-hide">
                    <span class="button bwfan_button">{{value}}</span>
                </label>
                <# })
                }
                #>
            </div>
            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'To', 'wp-marketing-automations' ); ?>
				<?php
				$message = __( 'Receiver email address', 'wp-marketing-automations' );
				echo $this->add_description( esc_html__( $message ), 'xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
				<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <input required type="text" class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][to]" placeholder="E.g. customer_email@gmail.com" value="{{to}}"/>
            </div>
            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'Subject', 'wp-marketing-automations' ); ?>
				<?php
				$message = __( 'Email subject', 'wp-marketing-automations' );
				echo $this->add_description( esc_html__( $message ), 'm', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
				<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <input required type="text" id='bwfan_email_subject' class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][subject]" placeholder="Enter Subject" value="{{subject}}"/>
            </div>
            <label for="" class="bwfan-label-title">
				<?php esc_html_e( 'Pre Header', 'wp-marketing-automations' ); ?>
				<?php
				$message = __( 'Email pre header', 'wp-marketing-automations' );
				echo $this->add_description( esc_html__( $message ), 'm', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
				?>
				<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </label>
            <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                <input required type="text" id='bwfan_email_preheader' class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][preheading]" placeholder="Enter Pre Heading" value="{{email_preheading}}"/>
            </div>
            <div class="bwfan_email_template {{show_email_heading}}">
                <label for="" class="bwfan-label-title">
					<?php esc_html_e( 'Email Heading', 'wp-marketing-automations' ); ?>
					<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </label>
                <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15">
                    <input type="text" id='bwfan_email_heading' class="bwfan-input-wrapper bwfan-field-<?php esc_html_e( $unique_slug ); ?>" name="bwfan[{{data.action_id}}][data][email_heading]" placeholder="Your Store Name" value="{{email_heading}}"/>
                </div>
            </div>
            <div style="display: flex;
                flex-direction: row;
                width: 100%;
                justify-content: space-between;
                margin: 10px 0;"
            >
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Body', 'wp-marketing-automations' ); ?></label>
                <?php echo $this->inline_template_selector_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </div>
            <div class="bwfan-email-content bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15 bwfan-email-wysiwyg {{is_enable_wysiwyg}}">
                <textarea class="bwfan-input-wrapper" id="bwfan-editor" rows="6" placeholder="<?php esc_html_e( 'Email Message', 'wp-marketing-automations' ); ?>" name="bwfan[{{data.action_id}}][data][body]">{{body}}</textarea>
            </div>
            <div class="bwfan-email-content bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan-mb-15 bwfan-email-textarea {{is_enable_textarea}}">
                <textarea class="bwfan-input-wrapper" id="bwfan-raw_textarea" rows="15" placeholder="<?php esc_html_e( 'Email Message with HTML/CSS', 'wp-marketing-automations' ); ?>" name="bwfan[{{data.action_id}}][data][body_raw]">{{body_raw}}</textarea>
            </div>

			<?php do_action( 'bwfan_action_send_email_editors' ); ?>

            <div class="bwfan_preview_email_container bwfan-mb-15">
                <a href="javascript:void(0);" class="bwfan_preview_email"><?php esc_html_e( 'Generate Preview', 'wp-marketing-automations' ); ?></a>
            </div>

            <label for="" class="bwfan-label-title"><?php esc_html_e( 'Send Test Mail', 'wp-marketing-automations' ); ?></label>
            <div class="bwfan_send_test_email bwfan-mb-15">
                <input type="email" name="test_email" id="bwfan_test_email">
                <input type="button" id="bwfan_test_email_btn" class="button bwfan-btn-inner" value="<?php esc_html_e( 'Send', 'wp-marketing-automations' ); ?>">
            </div>

            <div class="bwfan-input-form bwfan-row-sep"></div>
            <label for="" class="bwfan-label-title">Other</label>
            <div class="bwfan_email_tracking bwfan-mb-15">
                <label for="bwfan_promotional_email">
                    <input type="checkbox" name="bwfan[{{data.action_id}}][data][promotional_email]" id="bwfan_promotional_email" value="1" {{is_promotional}}/>
					<?php
					esc_html_e( 'Mark as Promotional', 'wp-marketing-automations' );
					$message = __( "Email marked as promotional will not be send to the unsubscribers.", 'wp-marketing-automations' );
					echo $this->add_description( esc_html__( $message ), 'xl' ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
            </div>
			<?php
			do_action( 'bwfan_' . $this->get_slug() . '_setting_html', $this )
			?>
        </div>
    </script>
    <script>
        jQuery(document).ready(function ($) {
            /** Email heading functionality for woocommerce */
            $('body').on('change', '.bwfan_email_template', function (event) {
                var $this = jQuery(this);
                var selected_template = $this.val();
                $this.parents('.bwfan-wp_sendemail').attr('data-temp', selected_template);
                if ('raw' === selected_template) {
                    jQuery('.bwfan_email_template').hide();
                    jQuery('.bwfan-email-content').hide();
                    jQuery('.bwfan-email-textarea').show();
                    jQuery('.bwfan-email-editor').addClass('bwfan-display-none');
                } else if ('raw_template' === selected_template || 'wc_template' === selected_template) {
                    jQuery('.bwfan_email_template').hide();
                    jQuery('.bwfan-email-content').hide();
                    jQuery('.bwfan-email-wysiwyg').show();
                    jQuery('.bwfan-email-editor').addClass('bwfan-display-none');
                } else if ('editor' === selected_template) {
                    jQuery('.bwfan_email_template').hide();
                    jQuery('.bwfan-email-content').hide();
                    jQuery('.bwfan-email-editor').removeClass('bwfan-display-none');
                }
            });

            /** UTM parameters functionality */
            $('body').on('change', '#bwfan_append_utm', function (event) {
                var $this = jQuery(this);
                if ($this.is(":checked")) {
                    jQuery('.bwfan_utm_sources').show();
                } else {
                    jQuery('.bwfan_utm_sources').hide();
                }
            });

        });
    </script>

<?php do_action( 'bwfan_action_send_email_template' ) ?>