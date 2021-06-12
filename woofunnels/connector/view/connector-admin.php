<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
    <div class="wrap wfco_connectors_listing wfco_global">
        <div class="wfco_head_bar">
            <div class="wfco_bar_head"><?php esc_html_e( 'WooFunnels Connectors', 'woofunnels' ); ?></div>
        </div>
        <div id="poststuff">
            <div class="inside">
                <div class="wrap wfco_global wfco_connector_listing">
                    <div class="wfco_connector_listing_wrap wfco_clearfix">
                        <div class="wfco-row">
                            <form method="GET">
                                <input type="hidden" name="page" value="connector"/>
                                <input type="hidden" name="status" value="<?php echo( filter_input(INPUT_GET,'status',FILTER_SANITIZE_STRING) ? filter_input(INPUT_GET,'status',FILTER_SANITIZE_STRING) : '' );  ?>"/>
								<?php
								WFCO_Connector_Screen_Factory::print_screens( 'autonami' );
								?>
                            </form>
                        </div>
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
<?php

do_action( 'wfco_connector_screen' );
