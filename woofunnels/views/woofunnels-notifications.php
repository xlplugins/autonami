<?php
if ( ! is_array( $notifications_list ) || count( $notifications_list ) === 0 ) {
	return;
}

?>
<div class="wf_notification_wrap">
    <div class="inside">
		<?php
		foreach ( $notifications_list as $nkey => $nvalue ) {
			foreach ( $nvalue as $key => $value ) {

				$combined_class = [ $key, 'wf_notification_content_sec' ];
				if ( isset( $value['type'] ) && $value['type'] !== '' ) {
					$combined_class[] = $value['type'];
				}
				if ( isset( $value['class'] ) && is_array( $value['class'] ) ) {
					$combined_class = array_merge( $combined_class, $value['class'] );
				}

				?>
                <div class="<?php echo implode( ' ', $combined_class ); ?>" wf-noti-key="wf-<?php echo $key; ?>" wf-noti-group="<?php echo $nkey; ?>">
                    <div class="wf_overlay_active "></div>
					<?php
					echo '<div class="wf_notification_html"><p>' . $value['html'] . '</p></div>';


					if ( isset( $value['buttons'] ) && ( is_array( $value['buttons'] ) && count( $value['buttons'] ) > 0 ) ) {

						printf( '<div class="wf_notification_btn_wrap">' );
						foreach ( $value['buttons'] as $btn_key => $btn_val ) {

							$btn_class = [];

							if ( isset( $btn_val['class'] ) && is_array( $btn_val['class'] ) ) {
								$btn_class = array_merge( $btn_class, $btn_val['class'] );
							}

							if ( ! isset( $btn_val['name'] ) || $btn_val['name'] === '' ) {
								continue;
							}

							printf( ' <a href="%s" target="%s" class="%s">%s</a>', isset( $btn_val['url'] ) ? $btn_val['url'] : '#', isset( $btn_val['target'] ) ? $btn_val['target'] : '_blank', implode( ' ', $btn_class ), $btn_val['name'] );
						}

						printf( '</div>' );
					}

					?>
                    <div class="wf_notice_dismiss_link_wrap">
                        <a class="notice-dismiss" href="javascript:void(0)">
							<?php esc_html_e( 'Dismiss' ); ?>
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </div>
				<?php
			}
		}
		?>
    </div>
</div>
