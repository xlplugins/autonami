<?php
$all_triggers          = BWFAN_Load_Sources::get_sources_obj();
$all_registered_events = BWFAN_Core()->sources->get_events();
$db_saved_value        = $trigger_events;

if ( is_array( $db_saved_value ) && count( $db_saved_value ) === 0 ) {
	$db_saved_value = '';
}
$db_eventmeta_saved_value = '';
if ( isset( $trigger_events_meta ) && is_array( $trigger_events_meta ) && count( $trigger_events_meta ) > 0 ) {
	$db_eventmeta_saved_value = $trigger_events_meta;
}

$repeater_count = 0;
$is_edit        = ( '' !== $db_saved_value ) ? 'yes' : 'no';
$parent_trigger = $parent_source;

?>
    <div id="bwfan_events_output_div" data-parent-trigger="<?php esc_attr_e( $parent_trigger ); ?>" data-is-edit="<?php esc_attr_e( $is_edit ); ?>" data-db-eventmeta-value='<?php echo wp_json_encode( $db_eventmeta_saved_value ); ?>' data-db-value='<?php esc_attr_e( $db_saved_value ); ?>' data-repeator-count="<?php esc_attr_e( $repeater_count ); ?>"></div>

    <script type="text/html" id="tmpl-single-source">
        <div class="bwfan-form-inner clearfix bwfan-mb-0 bwfan-b-0 bwfan-p-0 bwfan-mt-0">
            <div class="bwfan-row">
                <div class="bwfan-input-form clearfix">
                    <div style="display:none" class="bwfan-input-form clearfix bwfan-mb-0">
                        <div class="bwfan-col-sm-12">
                            <select required id="bwfan-selected-source" class="bwfan-input-wrapper bwfan-selected-source" name="source">
                                <# _.each( data.sources, function( value, key ){ #>
                                <option value="{{key}}">{{value.nice_name}}</option>
                                <# }) #>
                            </select>
                        </div>
                    </div>
                    <div class="bwfan-input-form bwfan-mb0 clearfix">
                        <div class="bwfan-col-sm-12">
                            <div id="bwfan-source-events"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="text/html" id="tmpl-single-event">
        <div class="clearfix">
            <div class="bwfan-row">
                <div class="bwfan-input-form clearfix bwfan_m0">
                    <div style="display: none" class="bwfan-input-form clearfix">
                        <div class="bwfan-col-sm-12">
                            <select required id="bwfan-selected-trigger" class="bwfan-input-wrapper bwfan-selected-trigger" name="event">
                                <option value=""><?php esc_html_e( 'Choose An Event', 'wp-marketing-automations' ); ?></option>
                                <# _.each( data.triggers_events, function( value, key ){ #>
                                <optgroup label="{{key}}">
                                    <# _.each( value, function( value1, key1 ){
                                    disabled=(value1.available=='no')?'disabled':'';
                                    #>
                                    <option value="{{key1}}" {{disabled}}>{{value1.name}}</option>
                                    <# }) #>
                                </optgroup>
                                <# }) #>
                            </select>
                        </div>
                    </div>
                    <div class="bwfan-input-form bwfan-mb0 clearfix">
                        <div class="bwfan-col-sm-12">
                            <div class="bwfan-events-meta"></div>
							<?php
							do_action( 'bwfan_external_event_settings' );
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>
<?php

/**
 * @var $event_object BWFAN_Event;
 */
foreach ( $all_registered_events as $event_slug => $event_object ) {
	$event_object->set_event_saved_data( $db_eventmeta_saved_value );
	$event_object->get_view( $db_eventmeta_saved_value );
}


unset( $db_saved_value );
unset( $repeater_count );
unset( $is_edit );
