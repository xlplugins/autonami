<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$subtotal     = 0;
$subtotal_tax = 0;
$total        = 0;
$text_align   = is_rtl() ? 'text-align:right;' : 'text-align:left;';

$disable_product_thumbnail = BWFAN_Common::disable_product_thumbnail();
$currency                  = is_array( $data ) & isset( $data['currency'] ) ? $data['currency'] : '';
$colspan                   = ' colspan="2"';
$colspan_foot              = ' colspan="3"';
if ( true === $disable_product_thumbnail ) {
	$colspan      = '';
	$colspan_foot = ' colspan="2"';
}

add_action( 'bwfan_output_email_style', function () { ?>
    .bwfan-email-cart-table #template_header {
    width: 100%;
    }

    .bwfan-email-cart-table table {
    border: 2px solid #e5e5e5;
    border-collapse: collapse;
    max-width:700px;
    }

    .bwfan-email-cart-table table tr th, .bwfan-email-cart-table table tr td {
    border: 2px solid #e5e5e5;
    }
<?php } ); ?>
<div class='bwfan-email-cart-table bwfan-email-table-wrap'>
    <table cellspacing="0" cellpadding="6" border="1" width="100%">
        <thead>
        <tr>
            <th class="td" scope="col" <?php echo $colspan ?> style="<?php echo $text_align; ?>"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="td" scope="col" style="width:90px;<?php echo $text_align; ?>"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
            <th class="td" scope="col" style="width:90px;<?php echo $text_align; ?>"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
        </tr>
        </thead>
        <tbody>

		<?php
		$tax_display = get_option( 'woocommerce_tax_display_cart' );
		if ( false !== $cart ) {
			foreach ( $cart as $item ) :
				$product = wc_get_product( $item['data']->get_id() );
				if ( ! $product ) {
					continue; // don't show items if there is no product
				}

				if ( false === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
					$subtotal     += BWFAN_Common::get_line_subtotal( $item );
					$subtotal_tax += BWFAN_Common::get_line_subtotal_tax( $item );
					$line_total   = ( 'excl' === $tax_display ) ? BWFAN_Common::get_line_subtotal( $item ) : BWFAN_Common::get_line_subtotal( $item ) + BWFAN_Common::get_line_subtotal_tax( $item );
					$total        += $line_total;
				} else {
					$line_total = $product->get_price();
				}
				?>
                <tr>
					<?php
					if ( false === $disable_product_thumbnail ) {
						?>
                        <td class="image" width="100">
							<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail', false, 100 ) ); ?>
                        </td>
						<?php
					}
					?>
                    <td>
                        <h4 style="vertical-align:middle; <?php echo $text_align; ?>">
							<?php echo wp_kses_post( BWFAN_Common::get_name( $product ) ); ?>
                        </h4>
                    </td>
                    <td style="vertical-align:middle; <?php echo $text_align; ?>">
						<?php
						if ( false === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
							esc_html_e( BWFAN_Common::get_quantity( $item ) );
						} else {
							esc_html_e( 1 );
						}
						?>
                    </td>
                    <td style="vertical-align:middle; <?php echo $text_align; ?>">
						<?php echo wp_kses_post( BWFAN_Common::price( $line_total, $currency ) ); ?>
                    </td>
                </tr>

			<?php
			endforeach;
		} else {
			foreach ( $products as $product ) {
				?>
                <tr>
					<?php
					if ( false === $disable_product_thumbnail ) {
						?>
                        <td width="100">
							<?php echo wp_kses_post( BWFAN_Common::get_product_image( $product, 'thumbnail', false, 100 ) ); ?>
                        </td>
						<?php
					}
					?>
                    <td style="vertical-align:middle; <?php echo $text_align; ?>">
						<?php echo wp_kses_post( 'Test Product' ); ?>
                    </td>
                    <td style="vertical-align:middle; <?php echo $text_align; ?>">1</td>
                    <td style="vertical-align:middle; <?php echo $text_align; ?>"><?php echo wp_kses_post( BWFAN_Common::price( 0, $currency ) ); ?></td>
                </tr>
				<?php
			}
		}
		?>
        </tbody>
        <tfoot>
		<?php if ( is_array( $data ) && isset( $data['shipping_total'] ) && ! empty( $data['shipping_total'] ) && '0.00' !== $data['shipping_total'] ): ?>
            <tr>
                <th scope="row" <?php echo $colspan_foot ?> style=" <?php echo $text_align; ?>"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?>
					<?php if ( wc_tax_enabled() && $tax_display !== 'excl' ): ?>
                        <small><?php echo wp_kses_post( sprintf( __( '(includes %s tax)', 'woocommerce' ), BWFAN_Common::price( esc_attr( $data['shipping_tax_total'] ), $currency ) ) ) ?></small>
					<?php endif; ?>
                </th>
                <td><?php echo BWFAN_Common::price( esc_attr( $data['shipping_total'] ), $currency ); //phpcs:ignore WordPress.Security.EscapeOutput ?></td>
            </tr>
		<?php endif; ?>

		<?php if ( is_array( $data ) && isset( $data['coupons'] ) && ! empty( $data['coupons'] ) ): ?>
            <tr>
				<?php
				$discount     = 0;
				$coupon_names = array();
				foreach ( $data['coupons'] as $coupon_name => $coupon ) {
					$discount       += $coupon['discount_incl_tax'];
					$coupon_names[] = $coupon_name;
				}
				$coupon_names = implode( ', ', $coupon_names );
				$coupon_names = apply_filters( 'bwfan_modify_coupon_names', $coupon_names, $data['coupons'] );
				$total        = isset( $data['total'] ) ? $data['total'] : 0;
				?>
                <th scope="row" <?php echo $colspan_foot ?> style="<?php echo $text_align; ?>">
					<?php esc_html_e( 'Discount:', 'woocommerce' ); ?>
					<?php if ( ! empty( $coupon_names ) ) { ?>
                        <small><?php echo wp_kses_post( $coupon_names ) ?></small>
					<?php } ?>
                </th>
                <td><?php echo '-' . BWFAN_Common::price( esc_attr( $discount ), $currency ); //phpcs:ignore WordPress.Security.EscapeOutput ?></td>
            </tr>
		<?php endif; ?>

		<?php if ( wc_tax_enabled() && $tax_display === 'excl' && $subtotal_tax ): ?>
            <tr>
                <th scope="row" <?php echo $colspan_foot ?> style="<?php echo $text_align; ?>"><?php esc_html_e( 'Tax', 'woocommerce' ); ?></th>
                <td><?php echo wp_kses_post( BWFAN_Common::price( $subtotal_tax, $currency ) ); ?></td>
            </tr>
		<?php endif; ?>

        <tr>
            <th scope="row" <?php echo $colspan_foot ?> style="<?php echo $text_align; ?>">
				<?php esc_html_e( 'Total', 'woocommerce' ); ?>
				<?php if ( wc_tax_enabled() && $tax_display !== 'excl' ): ?>
                    <small><?php echo wp_kses_post( '( ' . BWFAN_Common::price( esc_attr( $subtotal_tax ), $currency ) . ' ' . get_option( 'woocommerce_price_display_suffix' ) . ' )' ) ?></small>
				<?php endif; ?>
            </th>
            <td><?php echo wp_kses_post( BWFAN_Common::price( $total, $currency ) ); ?></td>
        </tr>
        </tfoot>

    </table>
</div>
