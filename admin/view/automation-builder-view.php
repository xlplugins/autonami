<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$automation_id   = $this->get_automation_id();
$automation_meta = BWFAN_Model_Automations::get_automation_with_data( $automation_id );

if ( false === $automation_id || ! is_array( $automation_meta ) || 0 === count( $automation_meta ) ) {
	wp_die( esc_html__( 'Automation doesn\'t exists, something is wrong.', 'wp-marketing-automations' ) );
}

$trigger_events      = isset( $automation_meta['event'] ) ? $automation_meta['event'] : '';
$saved_integrations  = array();
$parent_source       = isset( $automation_meta['source'] ) ? $automation_meta['source'] : '';
$a_track_id          = isset( $automation_meta['meta']['a_track_id'] ) ? $automation_meta['meta']['a_track_id'] : '';
$trigger_events_meta = isset( $automation_meta['meta']['event_meta'] ) ? $automation_meta['meta']['event_meta'] : [];
$saved_integrations  = isset( $automation_meta['meta']['actions'] ) ? $automation_meta['meta']['actions'] : [];

$automation_sticky_line = __( 'Now Building', 'wp-marketing-automations' );
$automation_onboarding  = true;
$automation_title       = ( isset( $automation_meta['meta'] ) && isset( $automation_meta['meta']['title'] ) ) ? $automation_meta['meta']['title'] : '';
$status                 = ( 1 === absint( $automation_meta['status'] ) ) ? 'publish' : 'sandbox';

if ( class_exists( 'BWFAN_Header' ) ) {
	$header_ins = new BWFAN_Header();
	$header_ins->set_level_1_navigation_active( 'automations' );
	$header_ins->set_back_link( 1, admin_url( 'admin.php?page=autonami&path=/automations' ) );
	$header_ins->set_level_2_side_type( 'both' );
	$header_ins->set_level_2_title( $automation_title );

	$automation_edit_html = '<a class="bwfan_header_l2_edit" href="javascript:void(0)" data-izimodal-open="#modal-update-automation" data-izimodal-transitionin="comingIn"><i class="dashicons dashicons-edit"></i></a>';
	$header_ins->set_level_2_post_title( $automation_edit_html );
	$header_ins->set_level_2_side_navigation( BWFAN_Header::level_2_navigation_single_automation( $automation_id ) );
	$header_ins->set_level_2_navigation_pos( 'right' );
	ob_start();
	?>
    <div class="bwfan_head_mr" data-status="<?php echo ( 'publish' !== $status ) ? 'sandbox' : 'live'; ?>">
        <span class="bwfan_head_automation_state_on" <?php echo ( 'publish' !== $status ) ? ' style="display:none"' : ''; ?>><?php esc_html_e( 'Active', 'wp-marketing-automations' ); ?></span>
        <span class="bwfan_head_automation_state_off" <?php echo ( 'publish' === $status ) ? 'style="display:none"' : ''; ?>> <?php esc_html_e( 'Inactive', 'wp-marketing-automations' ); ?></span>
        <div class="automation_state_toggle bwfan_toggle_btn">
            <input name="offer_state" id="state<?php echo esc_html( $automation_id ); ?>" data-id="<?php echo esc_html( $automation_id ); ?>" type="checkbox" class="bwfan-tgl bwfan-tgl-ios" <?php echo ( 'publish' === $status ) ? 'checked="checked"' : ''; ?> <?php echo esc_html__( BWFAN_Core()->automations->current_automation_sync_state ); ?> />
            <label for="state<?php echo esc_html( $automation_id ); ?>" class="bwfan-tgl-btn bwfan-tgl-btn-small"></label>
        </div>
    </div>
	<?php
	$status = ob_get_clean();
	$header_ins->set_level_2_right_html( $status );

	echo $header_ins->render();
}
?>
<style>
    #wpwrap {
        background: #fff;
    }
</style>
<div class="bwfan_body bwfan_sec_automation">
    <div class="bwfan_wrap bwfan_box_size">
        <div class="bwfan_p20 bwfan_box_size">
            <div class="bwfan_wrap_inner">
				<?php
				/**
				 * Any registered section should also apply an action in order to show the content inside the tab
				 * like if action is 'stats' then add_action('bwfan_dashboard_page_stats', __FUNCTION__);
				 */
				if ( false === has_action( 'bwfan_dashboard_page_' . $this->get_automation_section() ) ) {
					include_once( $this->admin_path . '/view/section-' . $this->get_automation_section() . '.php' );
				} else {
					/**
					 * Allow other add-ons to show the content
					 */
					do_action( 'bwfan_dashboard_page_' . $this->get_automation_section() );
				}
				do_action( 'bwfan_automation_page', $this->get_automation_section(), $automation_id );
				?>
                <div class="bwfan_clear"></div>
            </div>
        </div>
    </div>
</div>

<div class="bwfan_izimodal_default" style="display: none" id="modal-update-automation">
    <div class="sections">
        <form class="bwfan_update_automation" data-bwf-action="update_automation">
            <div class="bwfan_vue_forms" id="part-add-funnel">
                <div class="form-group featured field-input">
                    <label for="title"><?php esc_html( __( 'Automation Name', 'wp-marketing-automations' ) ); ?></label>
                    <div class="field-wrap">
                        <div class="wrapper">
                            <input id="title" type="text" name="title" placeholder="<?php echo esc_html( __( 'Enter Automation Name', 'wp-marketing-automations' ) ); ?>" class="form-control" value="<?php echo esc_html( $automation_title ); ?>" required>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="_wpnonce" value="<?php esc_attr_e( wp_create_nonce( 'bwfan-action-admin' ) ); ?>"/>
            </div>
            <fieldset>
                <div class="bwfan_form_submit">
                    <input type="hidden" name="automation_id" value="<?php echo esc_html( $automation_id ); ?>">
                    <input type="submit" class="bwfan-display-none" value="<?php echo esc_html( __( 'Update', 'wp-marketing-automations' ) ); ?>"/>
                    <a href="javascript:void(0)" class="bwfan_update_form_submit bwfan_btn_blue"><?php echo esc_html( __( 'Update', 'wp-marketing-automations' ) ); ?></a>
                </div>
                <div class="bwfan_form_response">
                </div>
            </fieldset>
        </form>
        <div class="bwfan-automation-create-success-wrap bwfan-display-none">
            <div class="bwfan-automation-create-success-logo">
                <div class="swal2-icon swal2-success swal2-animate-success-icon" style="display: flex;">
                    <span class="swal2-success-line-tip"></span>
                    <span class="swal2-success-line-long"></span>
                    <div class="swal2-success-ring"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="bwfan_izimodal_default" style="display: none" id="modal-plus-icon-add">
    <div class="sections bwfan_add_block_wrap">
        <div class="bwfan_add_next_block" data-type="action">
            <div class="bwfan_add_block_icon"><i class="dashicons dashicons-networking"></i></div>
            <div class="bwfan_add_block_label">Direct Action</div>
            <div class="bwfan_add_block_desc">Run Actions directly.</div>
        </div>
        <div class="bwfan_add_next_block" data-type="conditional">
            <div class="bwfan_add_block_icon"><i class="dashicons dashicons-editor-help"></i></div>
            <div class="bwfan_add_block_label">Conditional Action</div>
            <div class="bwfan_add_block_desc">Add condition based action, apply rules which will be executed before Actions.</div>
        </div>
    </div>
</div>

<div class="bwfan_success_modal iziModal" style="display: none" id="modal_automation_success">
</div>

<?php
$event_source        = BWFAN_Core()->sources->get_source_localize_data();
$all_triggers_events = BWFAN_Core()->sources->get_sources_events_localize_data();
$all_source_event    = BWFAN_Load_Sources::get_sources_events_arr();
$group               = [];
$all_groups          = [];

$all_integrations         = BWFAN_Core()->integration->get_integration_actions_localize_data();
$integration_actions_name = BWFAN_Core()->integration->get_mapped_arr_integration_name_with_action_name();
$integrations_object      = BWFAN_Core()->integration->get_integration_localize_data();
$integrations_group       = [];
$sub_integrations         = [];

/** Events */
foreach ( $event_source as $key => $group_data ) {
	if ( ! isset( $group[ $group_data['group_slug'] ] ) ) {
		$group[ $group_data['group_slug'] ] = [
			'label'    => $group_data['group_name'],
			'subgroup' => [ $group_data['slug'] ],
			'priority' => $group_data['priority']
		];
		$all_groups[]                       = $group_data['slug'];
	} else {
		if ( ! in_array( $group_data['slug'], $group[ $group_data['group_slug'] ]['subgroup'] ) ) {
			$group[ $group_data['group_slug'] ]['subgroup'][] = $group_data['slug'];
		}
		$all_groups[] = $group_data['slug'];
	}
}
$group['all'] = [
	'subgroup' => $all_groups,
	'show'     => false,
	'priority' => 999
];

/** Actions */
foreach ( $integrations_object as $key => $data ) {
	if ( ! isset( $integrations_group[ $data['group_slug'] ] ) ) {
		$integrations_group[ $data['group_slug'] ] = [
			'label'    => $data['group_name'],
			'subgroup' => [ $data['slug'] => $data['nice_name'] ],
			'priority' => isset( $data['priority'] ) ? $data['priority'] : 1000
		];
		$sub_integrations[ $data['slug'] ]         = $data['nice_name'];
	} else {
		if ( ! in_array( $data['slug'], $integrations_group[ $data['group_slug'] ]['subgroup'] ) ) {
			$integrations_group[ $data['group_slug'] ]['subgroup'][ $data['slug'] ] = $data['nice_name'];
		}
		$sub_integrations[ $data['slug'] ] = $data['nice_name'];
	}
}

$integrations_group = apply_filters( 'bwfan_modify_actions_groups', $integrations_group );
$all_integrations   = apply_filters( 'bwfan_modify_integrations', $all_integrations );

$integrations_group['all'] = [
	'subgroup' => $sub_integrations,
	'show'     => false,
	'priority' => 999
];
$templates = bwfan_is_autonami_pro_active() ? BWFAN_Model_Templates::bwfan_get_templates( 0, 0, '', [] ) : [];

if ( ! empty( $templates ) ) {
    $templates = array_map( function ( $template ) {
        if(! empty($template['data'])){
            $template['data'] = json_decode( $template['data'] );
        }
        return $template;
    }, $templates );
}

$localized_data = [
	'actions'            => $all_triggers_events,
	'all_source'         => $event_source,
	'groupdata'          => $group,
	'source_event'       => $all_source_event,
	'integration_list'   => $all_integrations,
	'integration_group'  => $integrations_group,
	'woocommerce_enable' => BWFAN_Plugin_Dependency::woocommerce_active_check(),
    'templates'          => $templates,
];
echo '<script id="bwfanAutomationEvents">
    var automationEventActionData = ' . wp_json_encode( $localized_data ) . ';
</script>';
?>

<div class="bwfan_izimodal_default" style="display: none" id="modal-autonami-event">
    <div class="bwfan-search-filter-modal-wrap">
        <div class="bwfan-modal-header bwfan_p15">
            <div class="modal-header-title bwfan_heading_l bwfan_head_mr"><?php _e( 'Select an Event' ) ?></div>
            <div class="modal-header-search">
                <span class="dashicons dashicons-search modal-search-icon"></span>
                <input type="search" id="modal-search-field" placeholder="Search Event">
            </div>
            <span class="dashicons dashicons-no-alt bwfan_btn_close bwfan_modal_close" data-izimodal-close></span>
        </div>
        <div class="bwfan-modal-content">
            <div class="bwfan-modal-sidebar bwfan_p15">
                <div class="bwfan-modal-widget-wrap">
                    <label class="bwfan-widget-checkbox-wrap" id="bwf-event-search-row" style="display: none">
                        <input type="radio" name="widget_filter" value="all" class="bwfan-widget-filter ">
                        <span class="bwfan-widget-checkbox-label">Search results</span>
                    </label>
					<?php
					uasort( $group, function ( $a, $b ) {
						return $a['priority'] <= $b['priority'] ? - 1 : 1;
					} );
					foreach ( $group as $key => $filter ) {
						if ( isset( $filter['show'] ) && ! $filter['show'] ) {
							continue;
						}
						?>
                        <label class="bwfan-widget-checkbox-wrap">
                            <input type="radio" name="widget_filter" value="<?php echo $key ?>" class="bwfan-widget-filter ">
                            <span class="bwfan-widget-checkbox-label"><?php echo $filter['label'] ?></span>
                        </label>
						<?php
					}
					?>
                </div>
            </div>
            <div class="bwfan-modal-content-content bwfan_p15" id="bwfan-modal-content-content">
            </div>
        </div>
        <div class="bwfan-modal-footer bwfan_p15 bwfan_tr">
            <button type="button" class="button button-primary fixed_button" id="bwf-modal-event-continue" disabled><?php _e( 'Continue' ) ?></button>
        </div>
    </div>
</div>

<div class="bwfan_izimodal_default" style="display: none" id="modal-autonami-event-action">
    <div class="bwfan-search-filter-modal-wrap">
        <div class="bwfan-modal-header bwfan_p15">
            <div class="modal-header-title bwfan_heading_l bwfan_head_mr"><?php _e( 'Select an Action' ) ?></div>
            <div class="modal-header-search">
                <span class="dashicons dashicons-search modal-search-icon"></span>
                <input type="search" id="modal-search-action-field" placeholder="Search Action">
            </div>
            <span class="dashicons dashicons-no-alt bwfan_btn_close bwfan_modal_close" data-izimodal-close></span>
        </div>
        <div class="bwfan-modal-content">
            <div class="bwfan-modal-sidebar bwfan_p15">
                <div class="bwfan-modal-widget-wrap">
                    <label class="bwfan-widget-checkbox-wrap" id="bwf-action-search-row" style="display: none">
                        <input type="radio" name="widget_filter" value="all" class="bwfan-widget-filter ">
                        <span class="bwfan-widget-checkbox-label">Search results</span>
                    </label>
					<?php
					uasort( $integrations_group, function ( $a, $b ) {
						return $a['priority'] <= $b['priority'] ? - 1 : 1;
					} );
					foreach ( $integrations_group as $key => $filter ) {
						if ( isset( $filter['show'] ) && ! $filter['show'] ) {
							continue;
						}
						?>
                        <label class="bwfan-widget-checkbox-wrap">
                            <input type="radio" name="widget_filter" value="<?php echo $key ?>" class="bwfan-widget-filter ">
                            <span class="bwfan-widget-checkbox-label"><?php echo $filter['label'] ?></span>
                        </label>
						<?php
					}
					?>
                </div>
            </div>
            <div class="bwfan-modal-content-content bwfan_p15" id="bwfan-modal-content-content">
            </div>
        </div>
        <div class="bwfan-modal-footer bwfan_p15 bwfan_tr">
            <input type="hidden" name="selected_action_group_id"/>
            <input type="hidden" name="selected_action_action_id"/>
            <button type="button" class="button button-primary fixed_button" id="bwf-modal-action-continue" disabled><?php _e( 'Continue' ) ?></button>
        </div>
    </div>
</div>
<?php 

if ( ! empty( $templates ) ) {
    ?>
        <div class="bwfan_izimodal_default" style="display: none" id="modal-autonami-template-selector">
        <div class="bwfan-search-filter-modal-wrap">
            <div class="bwfan-modal-header bwfan_p15">
                <div class="modal-header-title bwfan_heading_l bwfan_head_mr"><?php _e( 'My Templates' ) ?></div>
                <div class="modal-header-search">
                    <span class="dashicons dashicons-search modal-search-icon"></span>
                    <input type="search" id="modal-search-template-field" placeholder="Search by name">
                </div>
                <span class="dashicons dashicons-no-alt bwfan_btn_close bwfan_modal_close" id="bwfan-modal-template-close" data-izimodal-close></span>
            </div>
            <div class="bwfan-modal-content">
                <div class="bwfan-modal-sidebar bwfan_p15">
                    <div class="bwfan-modal-widget-wrap">
                        <label class="bwfan-widget-checkbox-wrap">
                            <input type="radio" name="widget_filter" value="all" class="bwfan-widget-filter is-selected" checked>
                            <span class="bwfan-widget-checkbox-label">All</span>
                        </label>
                        <label class="bwfan-widget-checkbox-wrap">
                            <input type="radio" name="widget_filter" value="1" class="bwfan-widget-filter ">
                            <span class="bwfan-widget-checkbox-label">Text</span>
                        </label>
                        <label class="bwfan-widget-checkbox-wrap">
                            <input type="radio" name="widget_filter" value="3" class="bwfan-widget-filter ">
                            <span class="bwfan-widget-checkbox-label">HTML</span>
                        </label>
                        <label class="bwfan-widget-checkbox-wrap">
                            <input type="radio" name="widget_filter" value="4" class="bwfan-widget-filter ">
                            <span class="bwfan-widget-checkbox-label">Drag and Drop</span>
                        </label>
                    </div>
                </div>
                <div class="bwfan-modal-content-content bwfan_p15" id="bwfan-modal-template-content">
                </div>
            </div>
        </div>
    </div>
    <?php
    }
?>