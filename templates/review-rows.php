<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_array( $products ) ) :
	$review_hash_path = apply_filters( 'bwfan_review_section_hash_path', '#tab-reviews' );
	$button_background_color = apply_filters( 'bwfan_wc_email_get_base_color', get_option( 'woocommerce_email_base_color' ) );
	$button_text_color = BWFAN_Common::color_light_or_dark( $button_background_color, '#202020', '#ffffff' );
	$button_text_color = apply_filters( 'bwfan_wc_email_get_text_color', $button_text_color );
	?>
    <style>
        /** don't inline this css - hack for gmail */
        .autonami-button {
            font-weight: bold;
            background-color: <?php echo esc_attr($button_background_color); ?>;
            border-radius: 4px;
            display: inline-block;
            padding: 12px 35px 13px;
            margin: 8px auto;
            font-size: 14px;
            text-align: center;
            color: <?php echo esc_attr($button_text_color); ?>;
            text-decoration: none;
        }

        .bwfan-product-rows img {
            max-width: 75px;
        }
    </style>
    <table cellspacing="0" cellpadding="0" style="width: 100%;" class="bwfan-product-rows">
        <tbody>
		<?php foreach ( $products as $product ) : ?>
            <tr>
                <td class="image" width="100">
					<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail' ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </td>
                <td>
                    <h4><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                </td>
                <td align="right" class="last" width="">
                    <a href="<?php echo esc_url_raw( $product->get_permalink() . $review_hash_path ); //phpcs:ignore WordPress.Security.EscapeOutput ?>" class="autonami-button autonami-button--small"><?php echo apply_filters( 'bwfan_email_review_button_text', esc_html__( 'Leave a review', 'wp-marketing-automations' ) ); ?></a>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
<?php endif;