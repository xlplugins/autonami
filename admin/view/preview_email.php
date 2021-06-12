<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<style type="text/css">
    .bwfan_preview_email {
        background-color: #ffffff;
        padding: 20px !important;
    }

    #wpbody {
        padding-top: 0 !important;
    }
</style>
<div class="bwfan_body">
	<?php
	if ( isset( $_GET['type'] ) && 'loading' === sanitize_text_field( $_GET['type'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
		esc_html_e( 'Loading ...', 'wp-marketing-automations' );
	} else {
		BWFAN_Merge_Tag_Loader::set_data( array(
			'is_preview' => true,
		) );
		$automation_id = $_GET['edit'];
		if ( absint( $automation_id ) < 1 ) {
			echo 'Automation ID missing';
		}
		$email_data = BWFAN_Model_Automationmeta::get_meta( $automation_id, 'email_preview' );

		$email_data['event_data']['event_slug'] = $email_data['event'];
		$action_object                          = BWFAN_Core()->integration->get_action( 'wp_sendemail' );
		$action_object->is_preview              = true;
		$data_to_set                            = $action_object->make_data( '', $email_data );

		echo $data_to_set['body'];
	}
	?>

</div>
