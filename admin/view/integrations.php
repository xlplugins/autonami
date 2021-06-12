<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$all_actions             = BWFAN_Core()->integration->get_actions();
$db_saved_value          = $saved_integrations;
$repeater_count          = ( is_array( $db_saved_value ) && count( $db_saved_value ) > 0 ) ? ( count( $db_saved_value ) - 1 ) : '0';
$is_edit                 = ( is_array( $db_saved_value ) && count( $db_saved_value ) > 0 ) ? 'yes' : 'no';
$db_filtered_saved_value = BWFAN_Core()->automations->get_filtered_automation_saved_data( $db_saved_value );

$delay_types = [
	'immediately' => __( 'Immediately', 'wp-marketing-automations' ),
	'after_delay' => __( 'After a delay', 'wp-marketing-automations' ),
	'fixed'       => __( 'Fixed', 'wp-marketing-automations' )
];
$delay_types = apply_filters( 'bwfan_delay_types', $delay_types );
?>
    <div class="bwfan-saved-data bwfan-display-none">
		<?php
		if ( is_array( $db_saved_value ) && count( $db_saved_value ) > 0 ) {
			foreach ( $db_saved_value as $group_id => $group_actions ) {
				foreach ( $group_actions as $key1 => $value1 ) {
					if ( isset( $value1['integration_slug'] ) && isset( $all_actions[ $value1['action_slug'] ] ) && $all_actions[ $value1['action_slug'] ]->is_editor_supported() ) {
						$unique_id = $value1['action_slug'] . '-' . $key1;
						echo '<div class="" id="' . esc_attr( $unique_id ) . '">' . esc_html( stripslashes( $value1['data']['body'] ) ) . '</div>';
					}
				}
			}
		}
		?>
    </div>
    <div id="bwfan_output_div" data-is-edit="<?php echo esc_attr( $is_edit ); ?>" data-db-value='' data-repeator-count="<?php echo esc_attr( $repeater_count ); ?>"></div>
<?php

/**
 * @var $action_object BWFAN_Action
 */

foreach ( $all_actions as $action_slug => $action_object ) {
	$action_object->get_view();

	/** Enqueue custom scripts */
	do_action( 'bwfan_' . $action_object->get_slug() . '_add_script', $action_object );
}
?>

    <script type="text/html" id="tmpl-single-action">
        <#
        integration_slug = (_.has(data,'integration_slug') && false === _.isEmpty(data.integration_slug) ) ? data.integration_slug : '';
        merge_tag_show = (_.has(data,'action_slug') && false === _.isEmpty(data.action_slug)) ? true : false;
        merge_tag_show = (false === merge_tag_show && _.has(data,'temp_action_slug') && false === _.isEmpty(data.temp_action_slug)) ? true : merge_tag_show;
        #>
        <div class="bwfan-form-inner clearfix bwfan-container-{{data.action_id}} bwfan-mb-0 bwfan-b-0 bwfan-p-0 bwfan-mt-0">
            <div class="bwfan-row">
                <input type="hidden" class="bwfan-selected-action" data-group-id="{{data.action_id}}" name="bwfan[{{data.action_id}}][action_slug]" value="{{data.action_slug}}">
                <input type="hidden" name="bwfan[{{data.action_id}}][integration_slug]" value="{{integration_slug}}"/>
                <div class="bwfan-input-form bwfan-mb0 clearfix">
                    <div class="bwfan-col-sm-12">
                        <div class="bwfan-fields bwfan-fields-{{data.action_id}}"></div>
                    </div>
                </div>
                <div class="bwfan-input-form clearfix bwfan-mb0">
                    <div class="bwfan-lang-support-container bwfan-lang-{{data.action_id}}" data-group-id="{{data.action_id}}"></div>
                </div>

                <div class="bwfan-input-form bwfan-row-sep bwfan-row-sep-wrap bwfan-row-sep-e"></div>
                <div class="bwfan-input-form clearfix">
                    <div class="bwfan-timer-fields bwfan-timer-{{data.action_id}}" data-group-id="{{data.action_id}}"></div>
                </div>

                <div class="bwfan-input-form bwfan-row-sep bwfan-row-sep-wrap bwfan-row-sep-e"></div>
                <div class="bwfan-input-form clearfix">
                    <div class="bwfan-priority-fields bwfan-priority-{{data.action_id}}" data-group-id="{{data.action_id}}"></div>
                </div>
            </div>
        </div>
    </script>

    <script type="text/html" id="tmpl-all-fields">
        <input required type="text" name="bwfan[{{data.action_id}}][data][]" value="" id="" class="bwfan-input-wrapper">
    </script>

    <script type="text/html" id="tmpl-all-custom">
        <div class="bwfan-single-custom-container clearfix">
            <div class="bwfan-col-sm-5">
                <input required type="text" name="bwfan[{{data.action_id}}][custom][keys][]" value="" id="" class="bwfan-input-wrapper">
            </div>
            <div class="bwfan-col-sm-5">
                <input required type="text" name="bwfan[{{data.action_id}}][custom][values][]" value="" id="" class="bwfan-input-wrapper">
            </div>
            <div class="bwfan-col-sm-2">
                <span class="bwfan-close-button-inner">&#10006;</span>
            </div>
        </div>
    </script>

    <script type="text/html" id="tmpl-single-timer">
        <#
        var quarterHours = ["00", "15", "30", "45"];

        var times = [];
        for(var i = 0; i < 24; i++){
        for(var j = 0; j < 4; j++){
        // Using slice() with negative index => You get always (the last) two digit numbers.
        times.push( ('0' + i).slice(-2) + ":" + quarterHours[j] );
        }
        }

        var days_of_week = {"1":"Monday","2":"Tuesday","3":"Wednesday","4":"Thursday","5":"Friday","6":"Saturday","7":"Sunday"};

        delay_type = (_.has(data.timerData, 'delay_type')) ? data.timerData.delay_type : '';
        time_number = (_.has(data.timerData, 'time_number') && data.timerData.time_number > 0) ? data.timerData.time_number : '1';
        time_type = (_.has(data.timerData, 'time_type')) ? data.timerData.time_type : '';
        scheduled_time_check = (_.has(data.timerData, 'scheduled_time_check')) ? 'checked' : '';
        scheduled_time = (_.has(data.timerData, 'scheduled_time')) ? data.timerData.scheduled_time : '';
        scheduled_time_check_class = (scheduled_time_check == 'checked') ? '' : ' bwfan-display-none';

        scheduled_days_check = (_.has(data.timerData, 'scheduled_days_check')) ? 'checked' : '';
        scheduled_days = (_.has(data.timerData, 'scheduled_days')) ? data.timerData.scheduled_days : '';
        scheduled_days_check_class = (scheduled_days_check == 'checked') ? '' : ' bwfan-display-none';

        fix_cur_utc = new Date().toJSON().slice(0,10).replace(/-/g,'-');
        fixed_date = (_.has(data.timerData, 'fixed_date') && data.timerData.fixed_date != '') ? data.timerData.fixed_date : fix_cur_utc;
        fixed_time = (_.has(data.timerData, 'fixed_time') && data.timerData.fixed_time != '') ? data.timerData.fixed_time : '00:00';

        delay_option_class = 'bwfan-display-none';
        delay_option_fixed_class = 'bwfan-display-none';
        if(delay_type == 'after_delay') {
        delay_option_class = '';
        }
        if(delay_type == 'fixed') {
        delay_option_fixed_class = '';
        }

        /** timer display template */
        template = wp.template('timer-display');
        #>
        <div class="bwfan-col-sm-12">
            <label for="" class="bwfan-label-title">
                Delay
                <div class="bwfan_tooltip" data-size="xl">
                    <span class="bwfan_tooltip_text" data-position="right"><?php echo esc_js( "All actions are performed immediately. Setup delay to perform the action at a later time", 'wp-marketing-automations' ); ?></span>
                </div>
            </label>
            <div class="bwfan-time-container">
                <# print(template({group_id: data.group_id, action_id: data.action_id, timerData: data.timerData})) #>
            </div>
            <div class="bwfan-timer-options-container bwfan-display-none">
                <div class="bwfan-delay-types-container">
					<?php
					foreach ( $delay_types as $delay_type => $name ) { ?>
                        <label class="bwf-radio-button">
                            <input type="radio" name="bwfan[{{data.action_id}}][time][delay_type]" value="<?php echo esc_attr( $delay_type ); ?>" {{(delay_type=='<?php echo esc_attr( $delay_type ); ?>') ?'checked'
                            : ''}} class="bwf-radio-hide bwfan-delay-types" >
                            <span class="button bwfan_button"><?php echo esc_html( $name ); ?></span>
                        </label>
					<?php }
					?>
                </div>
                <div class="bwfan-delay-options-container bwfan_time_after_delay {{delay_option_class}}">
                    <div class="bwfan-col-sm-6 bwfan-pl-0">
                        <input type="number" min="1" value="{{time_number}}" name="bwfan[{{data.action_id}}][time][time_number]" id="bwfan_time_number_{{data.action_id}}" class="bwfan-input-wrapper bwfan-should-require bwfan_time_number" placeholder="1"/>
                    </div>
                    <div class="bwfan-col-sm-6 bwfan-pr-0">
                        <select name="bwfan[{{data.action_id}}][time][time_type]" class="bwfan-input-wrapper bwfan-delay-mode bwfan-should-require" id="bwfan_delay_mode_{{data.action_id}}">
                            <option value="minutes" {{(time_type=='minutes') ?
                            'selected' : ''}}><?php esc_html_e( 'minutes', 'wp-marketing-automations' ); ?></option>
                            <option value="hours" {{(time_type=='hours') ?
                            'selected' : ''}}><?php esc_html_e( 'hours', 'wp-marketing-automations' ); ?></option>
                            <option value="days" {{(time_type=='days') ?
                            'selected' : ''}}><?php esc_html_e( 'days', 'wp-marketing-automations' ); ?></option>
                        </select>
                    </div>
                    <div class="bwfan-col-sm-12 bwfan_mt20 bwfan-pl-0 bwfan-pr-0">
                        <label for="scheduled_time_check"><input {{scheduled_time_check}} id="scheduled_time_check" type="checkbox" name="bwfan[{{data.action_id}}][time][scheduled_time_check]" value="1"/> <?php esc_html_e( 'Delay untill a specific time of day (24 hr)', 'wp-marketing-automations' ); ?>
                        </label>
                        <div class="bwfan-clear"></div>
                        <div class="bwfan-col-sm-6 bwfan-pl-0 bwfan-pr-0 bwfan_time_selection {{scheduled_time_check_class}}" style="padding-top:10px;">
                            <select name="bwfan[{{data.action_id}}][time][scheduled_time]" class="bwfan-input-wrapper">
                                <# _.each( times, function( value, key ){ #>
                                <option value="{{value}}" {{(scheduled_time==value) ?
                                'selected' : ''}}>{{value}}</option>
                                <# }) #>
                            </select>
                        </div>
                    </div>
                    <div class="bwfan-col-sm-12 bwfan_mt20 bwfan-pl-0 bwfan-pr-0">
                        <label for="scheduled_days_check"><input {{scheduled_days_check}} id="scheduled_days_check" type="checkbox" name="bwfan[{{data.action_id}}][time][scheduled_days_check]" value="1"/> <?php esc_html_e( 'Delay untill a specific day(s) of the week', 'wp-marketing-automations' ); ?>
                        </label>
                        <div class="bwfan-clear"></div>
                        <div class="bwfan-col-sm-12 bwfan-pl-0 bwfan-pr-0 bwfan_days_selection {{scheduled_days_check_class}}" style="padding-top:10px;">
                            <select name="bwfan[{{data.action_id}}][time][scheduled_days][]" class="bwfan-input-wrapper bwfan-tags-multiple" multiple="multiple">
                                <# _.each( days_of_week, function( value, key ){ #>
                                <option value="{{key}}" {{(_.contains(scheduled_days, key)) ?
                                'selected' : ''}}>{{value}}</option>
                                <# }) #>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bwfan-delay-options-container bwfan_time_fixed {{delay_option_fixed_class}}">
                    <div class="bwfan-col-sm-6 bwfan-pl-0">
                        <label for=""> <?php esc_html_e( 'Select Date', 'wp-marketing-automations' ); ?></label>
                        <input type="text" class="bwfan-datepicker bwfan_fixed_date" name="bwfan[{{data.action_id}}][time][fixed_date]" id="bwfan_fixed_date_{{data.action_id}}" value="{{fixed_date}}"/>
                    </div>
                    <div class="bwfan-col-sm-6 bwfan-pr-0">
                        <label for=""> <?php esc_html_e( 'Select Time (24 hr)', 'wp-marketing-automations' ); ?></label>
                        <select name="bwfan[{{data.action_id}}][time][fixed_time]" class="bwfan-input-wrapper bwfan_fixed_time" id="bwfan_fixed_time_{{data.action_id}}">
                            <# _.each( times, function( value, key ){ #>
                            <option value="{{value}}" {{(fixed_time==value) ?
                            'selected' : ''}}>{{value}}</option>
                            <# }) #>
                        </select>
                    </div>
                </div>
				<?php do_action( 'bwfan_add_delay_options_container' ); ?>
            </div>
        </div>
    </script>

    <script type="text/html" id="tmpl-timer-display">
        <#
        t_delay_type = (_.has(data.timerData, 'delay_type')) ? data.timerData.delay_type : '';
        t_time_number = (_.has(data.timerData, 'time_number')) ? data.timerData.time_number : '';
        t_time_type = (_.has(data.timerData, 'time_type')) ? data.timerData.time_type : '';

        t_fixed_date = (_.has(data.timerData, 'fixed_date')) ? data.timerData.fixed_date : '';
        t_fixed_time = (_.has(data.timerData, 'fixed_time')) ? data.timerData.fixed_time : '';

        t_timer_text = '<?php esc_html_e( 'immediately', 'wp-marketing-automations' ); ?>';
        if((t_delay_type == 'after_delay')) {
        t_timer_text = '<?php esc_html_e( 'after', 'wp-marketing-automations' ); ?>' + ' ' + t_time_number + ' ' + t_time_type;
        }

        if((t_delay_type == 'fixed')) {
        t_timer_text = '<?php esc_html_e( 'on', 'wp-marketing-automations' ); ?>' + ' ' + t_fixed_date + ' at ' + t_fixed_time;
        }
        if((t_delay_type == 'schedule_with_variable')) {
        t_timer_text = '<?php esc_html_e( 'with scheduled variable', 'wp-marketing-automations' ); ?>';
        }
        #>
		<?php
		do_action( 'bwfan_before_delay_timer_text' );
		esc_html_e( 'Perform this action', 'wp-marketing-automations' );
		?>
        <span class="bwfan-timer-text bwfan-show-timer-text-{{data.action_id}}">{{t_timer_text}}</span>.
        <a href="javascript:void(0);" class="bwfan-show-timer-options bwfan-edit-timer-options-{{data.action_id}}">Change</a>
		<?php do_action( 'bwfan_after_delay_timer_text' ); ?>
    </script>

    <script type="text/html" id="tmpl-lang-support">
        <#
        enable_lang = (_.has(data.language, 'enable_lang')) ? data.language.enable_lang : '';
        selected_lang = (_.has(data.language, 'lang')) ? data.language.lang : '';
        checked_enable_lang = '';

        if ( 1 == enable_lang ) {
        checked_enable_lang = 'checked';//
        }
        #>
        <div class="bwfan-col-sm-12 bwfan_mt20">
            <label for="bwfan_lang_support"><input id="bwfan_lang_support" type="checkbox" name="bwfan[{{data.action_id}}][language][enable_lang]" value="1" {{checked_enable_lang}}/> <?php
				esc_html_e( 'Perform this action for a particular language.', 'wp-marketing-automations' ); ?>
            </label>
            <div class="bwfan-pl-0 bwfan-pr-0 bwfan_lang_support {{(checked_enable_lang == '')?'bwfan-display-none':''}}" style="padding-top:10px;">
                <select name="bwfan[{{data.action_id}}][language][lang]" class="bwfan-input-wrapper">
                    <# _.each( data.lang_options, function( value, key ){ #>
                    <option value="{{key}}" {{(selected_lang== key) ?
                    'selected' : ''}}>{{value}}</option>
                    <# }) #>
                </select>
            </div>
        </div>
    </script>

    <script type="text/html" id="tmpl-action-priority">
        <#
        priority = (_.has(data.priorityData, 'priority')) ? data.priorityData.priority : 10;
        #>
        <div class="bwfan-col-sm-12">
            <div class="bwfan-priority-options-container">
                <div class="bwfan-delay-types-container">
                    <label for="" class="bwfan-label-title">
						<?php
						esc_html_e( 'Execution Priority', 'wp-marketing-automations' );
						$message = __( 'Select execution priority for the task.', 'wp-marketing-automations' );
						?>
                        <div class="bwfan_tooltip" data-size="2xl">
                            <span class="bwfan_tooltip_text" data-position="right"><?php esc_html_e( 'Select execution priority for the task.', 'wp-marketing-automations' ); ?></span>
                        </div>
                    </label>
                    <div class="bwf-priority-option-wrap">
                        <label class="bwf-radio-button">
                            <input type="radio" name="bwfan[{{data.action_id}}][action_priority]" value="30" {{(priority==30) ?
                            'checked' : ''}} class="bwf-radio-hide" >
                            <span class="button bwfan_button"><?php esc_html_e( 'Critical', 'wp-marketing-automations' ); ?></span>
                        </label>
                        <label class="bwf-radio-button">
                            <input type="radio" name="bwfan[{{data.action_id}}][action_priority]" value="20" {{(priority==20) ?
                            'checked' : ''}} class="bwf-radio-hide">
                            <span class="button bwfan_button"><?php esc_html_e( 'High', 'wp-marketing-automations' ); ?></span>
                        </label>
                        <label class="bwf-radio-button">
                            <input type="radio" name="bwfan[{{data.action_id}}][action_priority]" value="10" {{(priority==10) ?
                            'checked' : ''}} class="bwf-radio-hide">
                            <span class="button bwfan_button"><?php esc_html_e( 'Medium', 'wp-marketing-automations' ); ?></span>
                        </label>
                        <label class="bwf-radio-button">
                            <input type="radio" name="bwfan[{{data.action_id}}][action_priority]" value="6" {{(priority==6) ?
                            'checked' : ''}} class="bwf-radio-hide">
                            <span class="button bwfan_button"><?php esc_html_e( 'Low', 'wp-marketing-automations' ); ?></span>
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </script>

    <script type="text/html" id="tmpl-section-merge-tags">
        <div class="bwfan-merge-tag-list">
            <div class="clearfix" style="box-sizing: border-box;padding:10px;margin-bottom: 10px;">
                <input required="" type="text" class="bwfan-input-wrapper search_merge_tag" name="search_merge_tag" placeholder="<?php esc_html_e( 'Search Merge Tags', 'wp-marketing-automations' ); ?>">
            </div>
            <div class="bwfan-input-form bwfan-merge-tags-container clearfix">
                <table class="bwfan-table-merge-tags">
                    <# _.each( data.merge_tags, function( value, key ){ #>
                    <# _.each( value, function( value1, key1 ){ #>
                    <tr>
                        <td>
                            <span class="bwfan-merge-tag-desc desc_{{key1}}">{{value1.tag_description}}</span><br>
                            {{value1.tag_name}}
                        </td>
                        <td>
                            <span class="bwfan-selected-merge-tag" data-class="{{key1}}"><?php esc_html_e( 'Select', 'wp-marketing-automations' ); ?></span>
                            <span class="bwfan-display-none">{{key1}}</span>
                        </td>
                    </tr>
                    <# }) #>
                    <# }) #>
                </table>
            </div>
        </div>
        <form class="bwfan_form_merge_tags" id="bwfan-merge-tag-settings">

        </form>
    </script>

    <div class="bwfan_izimodal_default" style="display: none" id="modal-show-merge-tags">
        <div class="sections" style="padding: 20px 0 0">
            <div id="bwfan-section-merge-tags"><h3 class="bwfan-text-center"><?php esc_html_e( 'Please Select An Event', 'wp-marketing-automations' ); ?></h3></div>
        </div>
    </div>


<?php
$all_merge_tags = BWFAN_Merge_Tag_Loader::get_registered_merge_tags();
if ( is_array( $all_merge_tags ) && count( $all_merge_tags ) > 0 ) {
	foreach ( $all_merge_tags as $group_key => $merge_tag ) {
		foreach ( $merge_tag as $class => $tag_instance ) {
			?>
            <script type="text/html" data-text id="tmpl-<?php echo esc_attr( $class ); ?>" data-group="<?php echo esc_attr( $group_key ); ?>" data-class="<?php echo esc_attr( $class ); ?>" data-tag="<?php echo esc_attr( $tag_instance->get_name() ); ?>">
				<?php echo $tag_instance->get_view(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
            </script>
			<?php
		}
	}
}
