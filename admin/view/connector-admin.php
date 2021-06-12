<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$woofunnels_transient_obj = WooFunnels_Transient::get_instance();
$woofunnels_transient_obj->delete_transient( 'get_available_connectors' );

$status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : ''; //phpcs:ignore WordPress.Security.NonceVerification

if ( class_exists( 'BWFAN_Header' ) ) {
	$header_ins = new BWFAN_Header();
	$header_ins->set_level_1_navigation_active( 'automations' );
	$header_ins->set_level_2_side_navigation( BWFAN_Header::level_2_navigation_automations() );
	$header_ins->set_level_2_side_navigation_active( 'connectors' );
	echo $header_ins->render();
}
?>
	<div class="wrap bwfan_global bwfan_global_settings bwfan_connectors">
		<div class="bwfan_global_settings_wrap">
			<div class="wrap wfco_global wfco_connector_listing">
				<div class="wfco_connector_listing_wrap wfco_clearfix">
					<div class="wfco-row">
						<form method="GET">
							<input type="hidden" name="page" value="connector"/>
							<input type="hidden" name="status" value="<?php esc_attr_e( $status ); ?>"/>
							<?php
							WFCO_Connector_Screen_Factory::print_screens( 'autonami' );
							?>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="wfco_izimodal_default" style="display: none" id="wfco-modal-connect">
		<div class="sections">
			<form class="wfco_add_connector" id="wfco-autoresponder" method="post" data-bwf-action="save_connector">
				<div class="wfco_vue_forms" id="part-add-funnel">
					<div id="wfco_connector_fields"></div>
				</div>
			</form>
			<div class="wfco-connector-create-success-wrap wfco-display-none">
				<div class="wfco-connector-connect-success-logo">
					<div class="swal2-icon swal2-success swal2-animate-success-icon" style="display: flex;">
						<span class="swal2-success-line-tip"></span>
						<span class="swal2-success-line-long"></span>
						<div class="swal2-success-ring"></div>
					</div>
				</div>
				<div class="wfco-connector-connect-message"><?php esc_html_e( 'Connector connected successfully. Redirecting the page...', 'woofunnels' ); ?></div>
			</div>
		</div>
	</div>
	<div class="wfco_izimodal_default" style="display: none" id="modal-edit-connector">
		<div class="sections">
			<form class="wfco_update_connector" id="wfco-autoresponder" method="post" data-bwf-action="update_connector">
				<div class="wfco_vue_forms" id="part-add-funnel">
					<div id="wfco_connector_edit_fields"></div>
				</div>
			</form>
			<div class="wfco-automation-update-success-wrap wfco-display-none">
				<div class="wfco-automation-update-success-logo">
					<div class="swal2-icon swal2-success swal2-animate-success-icon" style="display: flex;">
						<span class="swal2-success-line-tip"></span>
						<span class="swal2-success-line-long"></span>
						<div class="swal2-success-ring"></div>
					</div>
				</div>
				<div class="wfco-automation-update-message"><?php esc_html_e( 'We have detected change in the connector during updation.', 'woofunnels' ); ?></div>
			</div>
		</div>
	</div>
	<div class="bwfan_izimodal_default" style="display: none" id="modal-show-upgrade-to-pro">
		<div class="bwfan_izimodal_content">
			<div class="components-modal__content" role="document">
				<div class="bwf_clear"></div>
				<div class="bwf-t-center">
					<div class="bwf-h1 bwf_align_center">
						<span class="dashicon dashicons dashicons-lock" style="margin: 10px 0;"></span>
						<?php esc_html_e( 'This is a PRO Feature!', 'woofunnels' ); ?>
					</div>
					<div class="bwf_clear_20"></div>
					<div class="bwf-p">
						<?php esc_html_e( 'Unlock this Pro feature now and experience the fully-loaded version.', 'woofunnels' ); ?>
					</div>
				</div>
				<div class="bwf_clear_30"></div>
				<div class="bwf-t-center bwf-buttons-wrapper">
					<a href="https://buildwoofunnels.com/wordpress-marketing-automation-autonami/?utm_source=site&utm_medium=plugin&utm_campaign=autonami-upgrade" target="__blank" rel="noopener noreferrer" class="components-button is-primary">
						<?php esc_html_e( 'Unlock the Pro Version', 'woofunnels' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php

do_action( 'wfco_connector_screen' );
