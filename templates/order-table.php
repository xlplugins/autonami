<?php

if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
	$order = wc_get_orders( array(
		'numberposts' => 1,
	) );
	if ( is_array( $order ) && count( $order ) > 0 ) {
		$this->order = $order[0];
	}
}

add_action( 'bwfan_output_email_style', function () { ?>
    .bwfan-email-order-table > table {
    width: 100%;
    border-collapse: collapse;
    max-width:700px;
    }
<?php } );

$show_downloads = $this->order->has_downloadable_item() && $this->order->is_download_permitted();

echo "<div class='bwfan-email-order-table'>";
?>
    <!--[if mso]>
    <table>
        <tr>
            <td width="700">
    <![endif]-->

<?php

if ( $show_downloads ) {
	$downloads = $this->order->get_downloadable_items();
	$columns   = apply_filters( 'woocommerce_email_downloads_columns', array(
		'download-product' => __( 'Product', 'woocommerce' ),
		'download-expires' => __( 'Expires', 'woocommerce' ),
		'download-file'    => __( 'Download', 'woocommerce' ),
	) );

	wc_get_template( 'emails/email-downloads.php', array(
		'order'         => $this->order,
		'sent_to_admin' => false,
		'plain_text'    => false,
		'email'         => '',
		'downloads'     => $downloads,
		'columns'       => $columns,
	) );

}

wc_get_template( 'emails/email-order-details.php', array(
	'order'         => $this->order,
	'sent_to_admin' => false,
	'plain_text'    => false,
	'email'         => '',
) );
?>
    <!--[if mso]>
    </td></tr></table>
    <![endif]-->
<?php
echo '</div>';

add_action( 'bwfan_output_email_style', function () { ?>

    .bwfan-email-order-table #template_header {
    width: 100% !important;
    }

    .bwfan-email-order-table table img {
    max-width: 75px;
    }
<?php } ); ?>