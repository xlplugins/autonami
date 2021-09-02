<?php
$n        = 1;
$currency = is_array( $data ) && isset( $data['currency'] ) ? $data['currency'] : '';
if ( is_array( $products ) ) :

	add_action( 'bwfan_output_email_style', function () { ?>
        .bwfan-email-product-2-col .bwfan-product-grid {
        width: 100%;
        border-collapse: collapse;
        max-width:700px;
        }
        .bwfan-email-product-2-col .bwfan-product-grid-item-2-col {
        width: 46%;
        display: inline-block;
        text-align: center;
        padding: 0 0 20px;
        vertical-align: top;
        word-wrap: break-word;
        margin-right: 6%;
        font-size: 14px;
        }
        #body_content .bwfan-email-product-2-col .bwfan-product-grid-item-2-col h4 {
        text-align: center;
        }
        #body_content .bwfan-email-product-2-col .bwfan-product-grid-item-2-col p.price {
        margin-bottom: 0;
        }
	<?php } ); ?>

    <div class='bwfan-email-product-2-col bwfan-email-table-wrap'>
        <table cellspacing="0" cellpadding="0" class="bwfan-product-grid">
            <tbody>
            <tr>
                <td style="padding: 0;">
                    <div class="bwfan-product-grid-container">
						<?php
						$disable_product_link      = BWFAN_Common::disable_product_link();
						$disable_product_thumbnail = BWFAN_Common::disable_product_thumbnail();

						if ( false !== $cart ) {
							$suffix      = get_option( 'woocommerce_price_display_suffix' );
							$tax_display = get_option( 'woocommerce_tax_display_cart' );
							foreach ( $cart as $item ) {
								$product = wc_get_product( $item['data']->get_id() );
								if ( ! $product ) {
									continue; // don't show items if there is no product
								}
								$line_total = $item['line_subtotal'];
								?>
                                <div class="bwfan-product-grid-item-2-col bwfan-product-type-cart" style="<?php echo( $n % 2 ? '' : 'margin-right: 0;' ); ?>">
									<?php echo ( false === $disable_product_thumbnail ) ? wp_kses_post( BWFAN_Common::get_product_image( $product, 'shop_catalog', false, 200 ) ) : ''; //phpcs:ignore WordPress.Security.EscapeOutput ?>
                                    <h4 style="vertical-align:middle;"><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
                                    <p class="price" style="vertical-align:middle;">
                                        <strong>
											<?php
											$line_tax   = wc_tax_enabled() && ! empty( $item['line_tax'] ) ? $item['line_tax'] : 0;
											$line_total += $line_tax;
											echo BWFAN_Common::price( $line_total, $currency ); //phpcs:ignore WordPress.Security.EscapeOutput
											?>
                                        </strong>
										<?php if ( $suffix && wc_tax_enabled() ): ?>
                                            <small><?php echo $suffix; ?></small>
										<?php endif; ?>
                                    </p>
                                </div>
								<?php
								$n ++;
							}
						} else {
							foreach ( $products as $product ) {
								if ( ! $product instanceof WC_Product ) {
									continue;
								}
								?>
                                <div class="bwfan-product-grid-item-2-col bwfan-product-type-product" style="<?php echo( $n % 2 ? '' : 'margin-right: 0;' ); ?>">
									<?php
									if ( true === $disable_product_link ) {
										echo ( false === $disable_product_thumbnail ) ? BWFAN_Common::get_product_image( $product, 'shop_catalog', false, 200 ) : ''; //phpcs:ignore WordPress.Security.EscapeOutput ?>
                                        <h4><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></h4>
										<?php
									} else {
										if ( false === $disable_product_thumbnail ) {
											?>
                                            <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'shop_catalog', false, 200 ) ); //phpcs:ignore WordPress.Security.EscapeOutput ?></a>
											<?php
										}
										?>
                                        <h4 style="vertical-align:middle;">
                                            <a href="<?php echo esc_url_raw( $product->get_permalink() ); //phpcs:ignore WordPress.Security.EscapeOutput ?>"><?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?></a>
                                        </h4>
										<?php
									}
									?>
                                    <p class="price" style="vertical-align:middle;">
                                        <strong><?php echo wp_kses_post( $product->get_price_html() ); //phpcs:ignore WordPress.Security.EscapeOutput ?></strong></p>
                                </div>
								<?php
								$n ++;
							}
						}
						?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
<?php endif;