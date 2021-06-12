<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$n = 1;

$button_background_color = apply_filters( 'bwfan_wc_email_get_base_color', get_option( 'woocommerce_email_base_color' ) );
$button_text_color       = apply_filters( 'bwfan_wc_email_get_text_color', get_option( 'woocommerce_email_text_color' ) );

if ( is_array( $products ) ) : ?>

    <style>
        /** don't inline this css - hack for gmail */
        .bwfan-product-grid .bwfan-product-grid-item-2-col img {
            height: auto !important;
        }

        .bwfan-product-grid {
            width: 100%;
        }

        .bwfan-product-grid-item-2-col {
            width: 46%;
            display: inline-block;
            text-align: left;
            padding: 0 0 30px;
            vertical-align: top;
            word-wrap: break-word;
            margin-right: 6%;
            font-size: 14px;
        }

        .bwfan-product-grid .bwfan-product-image {
            width: 100%;
        }

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
    </style>

    <table cellspacing="0" cellpadding="0" class="bwfan-product-grid bwfan-reviews-grid">
        <tbody>
        <tr>
            <td style="padding: 0;">
                <div class="bwfan-product-grid-container">
					<?php foreach ( $products as $product ) : ?>
                        <div class="bwfan-product-grid-item-2-col bwfan-reviews-grid__item" style="<?php echo( $n % 2 ? '' : 'margin-right: 0;' ); ?>">

							<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                            <h4><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                            <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>#tab-reviews" class="autonami-button"><?php echo apply_filters( 'bwfan_email_review_button_text', esc_html__( 'Leave a review', 'wp-marketing-automations' ) ); ?></a>
                        </div>
						<?php
						$n ++;
					endforeach;
					?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

<?php endif;
