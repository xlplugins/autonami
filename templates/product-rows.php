<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$currency = is_array( $data ) && isset( $data['currency'] ) ? $data['currency'] : '';
if ( is_array( $products ) ) :

	add_action( 'bwfan_output_email_style', function () { ?>
        .bwfan-email-product-rows .bwfan-product-rows {
        width: 100%;
        border: 2px solid #e5e5e5;
        border-collapse: collapse;
        max-width:700px;
        }
        #body_content .bwfan-email-product-rows .bwfan-product-rows td {
        padding: 10px 12px;
        }
	<?php } ); ?>

    <div class='bwfan-email-product-rows bwfan-email-table-wrap'>
        <!--[if mso]>
        <table>
            <tr>
                <td width="700">
        <![endif]-->
        <table cellspacing="0" cellpadding="0" style="width: 100%;" class="bwfan-product-rows">
            <tbody>
			<?php
			$disable_product_link      = BWFAN_Common::disable_product_link();
			$disable_product_thumbnail = BWFAN_Common::disable_product_thumbnail();

			if ( false !== $cart ) {
				$suffix      = get_option( 'woocommerce_price_display_suffix' );
				$tax_display = get_option( 'woocommerce_tax_display_cart' );
				foreach ( $cart as $item ) :
					$product = wc_get_product( $item['data']->get_id() );
					if ( ! $product ) {
						continue; // don't show items if there is no product
					}
					$line_total = $item['line_subtotal'];
					?>
                    <tr>
						<?php
						if ( false === $disable_product_thumbnail ) {
							?>
                            <td class="image" width="100">
								<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail', false, 100 ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                            </td>
							<?php
						} ?>
                        <td width="">
                            <h4 style="vertical-align:middle;"><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                        </td>
                        <td align="right" class="last" width="">
							<?php
							$line_tax   = wc_tax_enabled() && ! empty( $item['line_tax'] ) ? $item['line_tax'] : 0;
							$line_total += $line_tax;
							echo wp_kses_post( BWFAN_Common::price( $line_total, $currency ) );
							?>
							<?php if ( $suffix && wc_tax_enabled() ): ?>
                                <small><?php echo $suffix; ?></small>
							<?php endif; ?>
                        </td>
                    </tr>

				<?php endforeach;
			} else {
				foreach ( $products as $product ) {
					?>
                    <tr>
						<?php
						if ( true === $disable_product_link ) {
							if ( false === $disable_product_thumbnail ) {
								?>
                                <td class="image" width="100">
									<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail', false, 100 ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                                </td>
								<?php
							} ?>
                            <td width="">
                                <h4 style="margin:0;"><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                            </td>
							<?php
						} else {
							if ( false === $disable_product_thumbnail ) {
								?>
                                <td class="image" width="100">
                                    <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail', false, 100 ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?></a>
                                </td>
								<?php
							}
							?>
                            <td width="">
                                <h4 style="margin:0;">
                                    <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></a>
                                </h4>
                            </td>
							<?php
						}
						?>
                        <td align="right" class="last" width="">
                            <p class="price" style="margin: 18px 0 8px;"><?php echo wp_kses_post( $product->get_price_html() ); //phpcs:ignore WordPress.Security.EscapeOutput ?></p>
                        </td>
                    </tr>
					<?php
				}
			}
			?>
            </tbody>
        </table>
        <!--[if mso]>
        </td></tr></table>
        <![endif]-->
    </div>

<?php endif;
