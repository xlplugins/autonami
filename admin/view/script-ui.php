<?php
$text_start_when       = __( 'START WHEN?', 'wp-marketing-automations' );
$text_select_trigger   = __( 'Select An Event', 'wp-marketing-automations' );
$text_select_condition = __( 'Select Conditions', 'wp-marketing-automations' );
$text_what_next        = __( 'WHAT\'S NEXT?', 'wp-marketing-automations' );
$text_add_condition    = __( 'Add Condition', 'wp-marketing-automations' );
$text_add_action       = __( 'Add Action', 'wp-marketing-automations' );
$text_select_action    = __( 'Select Action', 'wp-marketing-automations' );
$text_if               = __( 'IF', 'wp-marketing-automations' );
$text_yes              = __( 'YES', 'wp-marketing-automations' );
$text_no               = __( 'NO', 'wp-marketing-automations' );
$text_then             = __( 'THEN', 'wp-marketing-automations' );
$text_end              = __( 'END', 'wp-marketing-automations' );

$hard_array = array(
	'select_action'    => $text_select_action,
	'select_condition' => $text_select_condition,
);

function bwfan_automation_builder_plus_icon( $color = '#fff', $size = '13' ) {
	ob_start();
	?>
    <svg xmlns="http://www.w3.org/2000/svg" width="<?php echo $size ?>" height="<?php echo $size ?>" viewBox="0 0 13 13">
        <g fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round">
            <g stroke="<?php echo $color ?>" stroke-width="2">
                <g>
                    <path d="M5.5 0v11M0 5.5h11" transform="translate(-287 -372) translate(288 373)"/>
                </g>
            </g>
        </g>
    </svg>
	<?php
	return ob_get_clean();
}

?>
    <script>
        var bwfan_hard_texts = <?php echo wp_json_encode( $hard_array ); //phpcs:ignore WordPress.Security.EscapeOutput ?>;
        var adminImgPath = '<?php echo BWFAN_PLUGIN_URL . '/admin/assets/img/' ?>';
    </script>

    <!-- Add trigger template -->
    <script type="text/html" id="tmpl-add_trigger">
        <div class="workflow_item" data-ui="{{data.ui}}">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_start_when ); ?></div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_add_trigger">
							<?php esc_html_e( $text_select_trigger ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <!-- Select trigger template -->
    <script type="text/html" id="tmpl-select_trigger">
        <div class="workflow_item" data-ui="{{data.ui}}">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_start_when ); ?></div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_trigger">
							<?php esc_html_e( $text_select_trigger ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <!-- Selected trigger template -->
    <script type="text/html" id="tmpl-selected_trigger">
        <div class="workflow_item" data-ui="{{data.ui}}">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_start_when ); ?></div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_trigger">
                            <div class="bwfan_name_wrap">
                                <div class="bwfan_small_name">{{data.trigger_name.integration}}</div>
                                {{data.trigger_name.action}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line item_wrap_line_top"></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html_add">
					<?php echo bwfan_automation_builder_plus_icon() ?>
                </div>
            </div>
        </div>
    </script>

    <!-- Select condition template -->
    <script type="text/html" id="tmpl-select_condition">
        <div class="workflow_item workflow_type_condition" data-ui="{{data.ui}}" data-group="{{data.group_id}}">
            <div class="workflow_item_data workflow_item_hidden_line workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select">
						<?php esc_html_e( $text_if ); ?>
                        <i class="dashicons dashicons-trash"></i>
                    </div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_condition item_condition_default">
                            <div class="item_single_l_icon">
                                <div class="item_icon_add">
									<?php echo bwfan_automation_builder_plus_icon( '#444', '11' ); ?>
                                </div>
                            </div>
							<?php esc_html_e( $text_select_condition ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert bwfan_no_select"><?php esc_html_e( $text_yes ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_then ); ?></div>
                    <div class="item_wrap_conditions">
                        <# _.each( data.actions, function( value, key ){
                        if(_.isEmpty(value)) {
                        return;
                        }
                        #>
                        <div class="item_wrap_single item_modify_action" data-action="{{key}}">
                            <div class="bwfan_name_wrap">
                                <div class="action_text">
                                    <# if(_.has(value, 'action_name') && _.has(value.action_name, 'integration')) { #>
                                    <div class="bwfan_small_name">{{value.action_name.integration}}</div>
                                    <div>{{value.action_name.action}}</div>
                                    <# if(_.has(value, 'time') && _.has(value.time, 'delay_type') && 'immediately' !== value.time.delay_type) {
                                    let timeText = BWFAN_Actions.get_action_timer_ui(value.time);
                                    #>
                                    <div class='item_single_icon_timer'><img src='{{adminImgPath}}timer.svg'/>Delay ({{{timeText}}})</div>
                                    <# } #>
                                    <# if(_.has(value, 'action_slug') && 'wp_sendemail' === value.action_slug) {
                                    let sub = '';
                                    if(_.has(value, 'data') && _.has(value.data, 'subject') && '' != value.data.subject) {
                                    sub = value.data.subject;
                                    }
                                    if('' != sub) {
                                    #>
                                    <div class='item_single_icon_email'><img src='{{adminImgPath}}email.svg'/><span>{{sub}}</span></div>
                                    <# }
                                    } #>
                                    <# } else { print(bwfan_hard_texts.select_action) }
                                    #>
                                </div>
                                <div class="item_actions"><i class="dashicons dashicons-ellipsis"></i></div>
                                <div class="item_actions_list">
                                    <ul>
                                        <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                                        <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <# }) #>
                        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
                        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
                        <# } #>
                        <div class="item_wrap_single item_add_action">
                            <div class="item_single_l_icon">
                                <div class="item_icon_add">
									<?php echo bwfan_automation_builder_plus_icon( '#444', '11' ); ?>
                                </div>
                            </div>
							<?php esc_html_e( $text_add_action ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert item_wrap_html_right bwfan_no_select"><?php esc_html_e( $text_end ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
        </div>
    </script>

    <!-- Selected condition template -->
    <script type="text/html" id="tmpl-selected_condition">
        <div class="workflow_item workflow_type_condition" data-ui="{{data.ui}}" data-group="{{data.group_id}}">
            <div class="workflow_item_data workflow_item_hidden_line workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select">
						<?php esc_html_e( $text_if ); ?>
                        <i class="dashicons dashicons-trash"></i>
                    </div>
                    <div class="item_wrap_conditions">
                        <div class="item_wrap_single item_modify_condition {{('' == data.rulesHtml) ? 'item_condition_default' : ''}}">
                            <# print(data.rulesHtml); #>
                        </div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert bwfan_no_select"><?php esc_html_e( $text_yes ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select"><?php esc_html_e( $text_then ); ?></div>
                    <div class="item_wrap_conditions">
                        <#
                        _.each( data.actions, function( value, key ){
                        if(_.isEmpty(value)) {
                        return;
                        }
                        #>
                        <div class="item_wrap_single item_modify_action" data-action="{{key}}">
                            <div class="bwfan_name_wrap">
                                <div class="action_text">
                                    <# if(_.has(value, 'action_name') && _.has(value.action_name, 'integration')) { #>
                                    <div class="bwfan_small_name">{{value.action_name.integration}}</div>
                                    <div>{{value.action_name.action}}</div>
                                    <# if(_.has(value, 'time') && _.has(value.time, 'delay_type') && 'immediately' !== value.time.delay_type) {
                                    let timeText = BWFAN_Actions.get_action_timer_ui(value.time);
                                    #>
                                    <div class='item_single_icon_timer'><img src='{{adminImgPath}}timer.svg'/>Delay ({{{timeText}}})</div>
                                    <# } #>
                                    <# if(_.has(value, 'action_slug') && 'wp_sendemail' === value.action_slug) {
                                    let sub = '';
                                    if(_.has(value, 'data') && _.has(value.data, 'subject') && '' != value.data.subject) {
                                    sub = value.data.subject;
                                    }
                                    if('' != sub) {
                                    #>
                                    <div class='item_single_icon_email'><img src='{{adminImgPath}}email.svg'/><span>{{sub}}</span></div>
                                    <# }
                                    } #>
                                    <# } else { print(bwfan_hard_texts.select_action) }
                                    #>
                                </div>
                                <div class="item_actions"><i class="dashicons dashicons-ellipsis"></i></div>
                                <div class="item_actions_list">
                                    <ul>
                                        <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                                        <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <# }) #>
                        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
                        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
                        <# } #>
                        <div class="item_wrap_single item_add_action">
                            <div class="item_single_l_icon">
                                <div class="item_icon_add">
									<?php echo bwfan_automation_builder_plus_icon( '#444', '11' ); ?>
                                </div>
                            </div>
							<?php esc_html_e( $text_add_action ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="workflow_item_btn workflow_flex_col">
                <div class="item_wrap_html_vert item_wrap_html_right bwfan_no_select"><?php esc_html_e( $text_end ); ?></div>
                <div class="item_wrap_hor_line"></div>
            </div>
        </div>
    </script>

    <!-- End html template -->
    <script type="text/html" id="tmpl-end_html">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html bwfan_no_select"><?php esc_html_e( $text_end ); ?></div>
            </div>
        </div>
    </script>

    <!-- Vertical line gap template -->
    <script type="text/html" id="tmpl-vertical_line_gap">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line"></div>
            </div>
        </div>
    </script>

    <!-- Vertical line gap top template -->
    <script type="text/html" id="tmpl-vertical_line_gap_top">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line item_wrap_line_top"></div>
            </div>
        </div>
    </script>

    <!-- No html template -->
    <script type="text/html" id="tmpl-no_html">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html bwfan_no_select"><?php esc_html_e( $text_no ); ?></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line"></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html_add"><?php echo bwfan_automation_builder_plus_icon() ?></div>
            </div>
        </div>
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_vert_line"></div>
            </div>
        </div>
    </script>

    <!-- Add block template -->
    <script type="text/html" id="tmpl-add_block">
        <div class="workflow_item">
            <div class="workflow_item_data workflow_flex_col">
                <div class="item_wrap_html_add"><?php echo bwfan_automation_builder_plus_icon() ?></div>
            </div>
        </div>
    </script>

    <!-- Select action template -->
    <script type="text/html" id="tmpl-select_action">
        <div class="item_wrap_single item_modify_action" data-action="{{data.action_id}}">
            <div class="bwfan_name_wrap">
                <div class="action_text"><?php esc_html_e( $text_select_action ); ?></div>
                <div class="item_actions bwfan_hide_hard"><i class="dashicons dashicons-ellipsis"></i></div>
                <div class="item_actions_list">
                    <ul>
                        <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                        <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </script>

    <!-- Add action template -->
    <script type="text/html" id="tmpl-add_action">
        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
        <# } #>
        <div class="item_wrap_single item_add_action">
            <div class="item_single_l_icon">
                <div class="item_icon_add">
					<?php echo bwfan_automation_builder_plus_icon( '#444', '11' ); ?>
                </div>
            </div>
			<?php esc_html_e( $text_add_action ); ?>
        </div>
    </script>

    <!-- Action only template -->
    <script type="text/html" id="tmpl-action_only">
        <div class="workflow_item workflow_type_action" data-ui="{{data.ui}}" data-group="{{data.group_id}}">
            <div class="workflow_item_data workflow_item_hidden_line workflow_flex_col">
                <div class="item_wrap">
                    <div class="item_wrap_top bwfan_no_select">
						<?php esc_html_e( $text_then ); ?>
                        <i class="dashicons dashicons-trash"></i>
                    </div>
                    <div class="item_wrap_conditions">
                        <# _.each( data.actions, function( value, key ){
                        if(_.isEmpty(value)) {
                        return;
                        }
                        #>
                        <div class="item_wrap_single item_modify_action" data-action="{{key}}">
                            <div class="bwfan_name_wrap">
                                <div class="action_text">
                                    <# if(_.has(value, 'action_name') && _.has(value.action_name, 'integration')) { #>
                                    <div class="bwfan_small_name">{{value.action_name.integration}}</div>
                                    <div>{{value.action_name.action}}</div>
                                    <# if(_.has(value, 'time') && _.has(value.time, 'delay_type') && 'immediately' !== value.time.delay_type) {
                                    let timeText = BWFAN_Actions.get_action_timer_ui(value.time);
                                    #>
                                    <div class='item_single_icon_timer'><img src='{{adminImgPath}}timer.svg'/>Delay ({{{timeText}}})</div>
                                    <# } #>
                                    <# if(_.has(value, 'action_slug') && 'wp_sendemail' === value.action_slug) {
                                    let sub = '';
                                    if(_.has(value, 'data') && _.has(value.data, 'subject') && '' != value.data.subject) {
                                    sub = value.data.subject;
                                    }
                                    if('' != sub) {
                                    #>
                                    <div class='item_single_icon_email'><img src='{{adminImgPath}}email.svg'/><span>{{sub}}</span></div>
                                    <# }
                                    } #>
                                    <# } else { print(bwfan_hard_texts.select_action) }
                                    #>
                                </div>
                                <div class="item_actions"><i class="dashicons dashicons-ellipsis"></i></div>
                                <div class="item_actions_list">
                                    <ul>
                                        <li><a href="javascript:void(0)" data-type="copy"><i class="dashicons dashicons-admin-page"></i>Copy</a></li>
                                        <li><a href="javascript:void(0)" data-type="delete"><i class="dashicons dashicons-trash"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <# }) #>
                        <# if( _.has(BWFAN_Auto.uiCopiedAction,'data') ) { #>
                        <div class="item_wrap_single item_paste_action"><?php esc_html_e( 'Click to insert the copied action' ); ?></div>
                        <# } #>
                        <div class="item_wrap_single item_add_action">
                            <div class="item_single_l_icon">
                                <div class="item_icon_add">
									<?php echo bwfan_automation_builder_plus_icon( '#444', '11' ); ?>
                                </div>
                            </div>
							<?php esc_html_e( $text_add_action ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>


    <!-- Sidebar views -->

    <!-- Condition Rule selection -->
    <script type="text/html" id="tmpl-rule_data">
        <div class="automation_data" data-type="condition" data-node="{{data.group_id}}">
            <p>Modify Rules data here</p>
            <a class="button button-primary item_assign_manual_rules_data" href="javascript:void(0)">Add manual rules</a>
        </div>
    </script>

    <script type="text/html" id="tmpl-single-action-html">
        <div class="item_wrap_single item_show_close bwfan-select-action" data-group-id="{{data.group_id}}" data-filled="0">
            Select Action
        </div>
    </script>

    <script type="text/html" id="tmpl-bwfan-events-form-container">
        <form action="" method="post" data-type="events" id="bwfan-events-form-container">
            <div id="bwfan-events-form" class="bwfan-right-content-container">

            </div>
            <input type="submit" value="<?php esc_html_e( 'Save Event', 'wp-marketing-automations' ); ?>" class="bwfan-display-none"/>
        </form>
    </script>

    <script type="text/html" id="tmpl-bwfan-actions-form-container">
        <form action="" method="post" data-type="actions" id="bwfan-actions-form-container">
            <div id="bwfan-actions-form" class="bwfan-right-content-container">

            </div>
            <input type="hidden" id="bwfan_group_id" name="bwfan_group_id"/>
            <input type="hidden" id="bwfan_action_id" name="bwfan_action_id"/>
            <input type="hidden" id="bwfan_temp_action_slug" name="bwfan_temp_action_slug"/>
            <input type="submit" value="<?php esc_html_e( 'Save Action', 'wp-marketing-automations' ); ?>" class="bwfan-display-none"/>
        </form>
    </script>

    <script type="text/html" id="tmpl-bwfan-condition-form-container">
        <form action="" method="post" data-type="rules" id="bwfan-condition-form-container" class="bwfan_rules_form">
            <div id="bwfan-condition-form" class="bwfan-rules-builder bwfan-right-content-container">

            </div>
            <div class="rules-basic-actions-wrapper bwfan_tc">
                <button class="button  bwfan-add-rule-group">Add another condition</button>
            </div>
            <input type="hidden" id="bwfan_action_id" name="bwfan_action_id"/>
            <input type="hidden" id="bwfan_group_id" name="bwfan_group_id" value="{{data.groupid}}"/>
            <input type="submit" value="<?php esc_html_e( 'Save Rules', 'wp-marketing-automations' ); ?>" class="bwfan-display-none"/>
        </form>
    </script>

    <script type="text/template" id="bwfan-rule-template">
		<?php include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/views/metabox-rules-rule-template-basic.php'; ?>
    </script>

    <script type="text/html" id="tmpl-bwfan-sidebar-top">
        <#
        heading = '';
        desc = '';
        if(_.has(data.head,'integration') == true) {
        heading = data.head.integration + ': ' + data.head.action;
        } else if(_.has(data,'head') == true && data.head != '') {
        heading = data.head;
        }
        #>
        <div class="wr_tw_h">{{heading}}</div>
        <div class="wr_tw_d"><# (_.has(data,'desc') == true && data.desc != '') ? print(data.desc) : '' #></div>
    </script>

    <script type="text/html" id="tmpl-bwfan-no-action-found">
        <p><?php esc_html_e( 'Selected action is not present.', 'wp-marketing-automations' ); ?></p>
    </script>

    <script type="text/html" id="tmpl-bwfan-tags-select-html">
        <# _.each( data.tags, function( value, key ){ #>
        <option selected>{{value}}</option>
        <# }); #>
    </script>

    <script type="text/html" id="tmpl-bwfan-tags-cancel-ui">
        <# _.each( data.tags, function( value, key ){
        var tagVal = (_.has(data.tagNames,value)) ? data.tagNames[value] : value;
        #>
        <li data-key="{{value}}">
            <button type="button" class="ntdelbutton">
                <span class="remove-tag-icon" aria-hidden="true"></span>
            </button>
            {{tagVal}}
        </li>
        <# }); #>
    </script>

    <script type="text/html" id="tmpl-bwfan-copied-action">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16">
            <g fill="none" fill-rule="evenodd">
                <g fill="#1DAAFC" fill-rule="nonzero">
                    <g>
                        <g>
                            <path d="M4.594 0C3.273 0 2.187 1.044 2.187 2.316v9.263c0 1.271 1.086 2.316 2.407 2.316h7c1.32 0 2.406-1.045 2.406-2.316V2.316C14 1.044 12.915 0 11.594 0h-7zm0 1.263h7c.611 0 1.094.464 1.094 1.053v9.263c0 .589-.483 1.053-1.094 1.053h-7c-.612 0-1.094-.464-1.094-1.053V2.316c0-.589.482-1.053 1.094-1.053zm-3.282.842l-.533.342C.292 2.76 0 3.286 0 3.85V12c0 2.21 1.86 4 4.156 4h5.845c.585 0 1.132-.281 1.456-.75l.355-.513H4.157c-1.57 0-2.843-1.225-2.843-2.737V2.105z" transform="translate(-501 -152) translate(490 145) translate(11 7)"/>
                        </g>
                    </g>
                </g>
            </g>
        </svg>
        <div class="bwfan_discard_blue_font"><?php esc_html_e( 'You have copied', 'wp-marketing-automations' ); ?> ({{data.integration}} >
            {{data.action}}). <?php esc_html_e( 'Paste in the available dash section', 'wp-marketing-automations' ); ?></div>
        <a href="javascript:void(0)" class="bwfan_discard_btn_cross">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                <g fill="none" fill-rule="evenodd">
                    <g>
                        <g>
                            <g>
                                <g fill="#FFF" opacity=".409" transform="translate(-960 -150) translate(490 145) translate(470 5) rotate(90 10 10)">
                                    <ellipse cx="10" cy="10" rx="10" ry="9.655"/>
                                </g>
                                <g stroke="#E6283F" stroke-linecap="round">
                                    <g>
                                        <path d="M.25 4H8" transform="translate(-960 -150) translate(490 145) translate(470 5) rotate(46 -.068 14.068)"/>
                                        <path d="M0 4h8" transform="translate(-960 -150) translate(490 145) translate(470 5) rotate(46 -.068 14.068) rotate(-90 4 4)"/>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </g>
                </g>
            </svg>
        </a>
    </script>
<?php
