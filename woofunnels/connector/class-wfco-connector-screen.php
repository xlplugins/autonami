<?php

class WFCO_Connector_Screen {

	private $slug = '';
	private $image = '';
	private $name = '';
	private $desc = '';
	private $is_active = false;
	private $activation_url = '';
	private $file = '';
	private $connector_class = '';
	private $source = '';
	private $support = [];
	private $type = 'Autonami';
	private $show_setting_btn = true;

	public function __construct( $slug, $data ) {
		$this->slug = $slug;

		if ( is_array( $data ) && count( $data ) > 0 ) {
			foreach ( $data as $property => $val ) {
				if ( is_array( $val ) ) {
					$this->{$property} = $val;
					continue;
				}
				if ( is_bool( $val ) || in_array( $val, [ 'true', 'false' ], true ) ) {
					$this->{$property} = (bool) $val;
					continue;
				}
				$this->{$property} = trim( $val );
			}
		}
	}

	public function get_logo() {
		return $this->image;
	}

	public function is_active() {
		return $this->is_active;
	}

	public function is_installed() {
	}

	public function activation_url() {
		return $this->activation_url;
	}

	public function get_path() {
		return $this->file;
	}

	public function get_class() {
		return $this->connector_class;
	}

	public function get_source() {
		return $this->source;
	}

	public function get_support() {
		return $this->support;
	}

	public function get_slug() {
		return $this->slug;
	}

	public function print_card() {
		?>
        <div class="wfco-col-md-4">
            <div class="wfco-connector-wrap" data-type="<?php echo $this->get_type() ?>">
                <div class="wfco-connector_card_outer">
                    <div class="wfco-connector-img-outer">
                        <div class="wfco-connector-img">
                            <div class="wfco-connector-img-section">
                                <img src="<?php echo $this->image; ?>"/>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="wfco_connector_info">
                        <div class="wfco_connector_info_head"><?php echo $this->get_name(); ?></div>
                        <div class="wfco_connector_info_details"><?php echo $this->get_desc(); ?></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="wfco-connector-action">
                    <div class="wfco-connector-btns">
						<?php $this->button(); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	public function get_type() {
		return $this->type;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_desc() {
		return $this->desc;
	}

	private function button() {
		if ( true === apply_filters( 'wfco_do_not_print_connector_button', false, $this->slug, $this ) ) {
			do_action( 'wfco_print_connector_button_placeholder', $this );

			return;
		}

		if ( ! class_exists( 'BWFAN_Pro' ) ) {
			?>
            <a href="javascript:void(0)" CLASS="wfco_save_btn_style wfco_locked_button" data-izimodal-open="#modal-show-upgrade-to-pro"><?php echo __( 'Locked', 'woofunnels' ); ?> </a>
			<?php
			return;
		}

		$edit_nonce    = wp_create_nonce( 'wfco-connector-edit' );
		$install_nonce = wp_create_nonce( 'wfco-connector-install' );
		$delete_nonce  = wp_create_nonce( 'wfco-connector-delete' );
		$sync_nonce    = wp_create_nonce( 'wfco-connector-sync' );
		/**
		 * @var $connector BWF_CO
		 */
		// Plugin activated
		if ( $this->is_activated() ) {
			$source_slug = $this->slug;
			$connector   = WFCO_Load_Connectors::get_connector( $source_slug );

			if ( false === $connector->has_settings() ) {
				/** Showing settings button if after connect setting is opt to show. Hide for slack oAuth case */

				?>
                <a href="javascript:void(0)" class="wfco_save_btn_style button-secondary"><?php echo __( 'Installed', 'woofunnels' ); ?> </a>
				<?php
				return;
			}


			//connector data present or not
			if ( isset( WFCO_Common::$connectors_saved_data[ $source_slug ] ) && true === $connector->has_settings() ) {
				$id          = WFCO_Common::$connectors_saved_data[ $source_slug ]['id'];
				$modal_title = __( 'Connect with ', 'woofunnels' ) . $this->name;
				/** Settings and Installed button */

				/** Showing settings button if after connect setting is opt to show. Hide for slack oAuth case */
				if ( true === $this->show_setting_btn() ) {
					?>
                    <a href="javascript:void(0)" data-nonce="<?php echo $delete_nonce; ?>" data-id="<?php echo $id; ?>" data-slug="<?php echo $source_slug; ?>" class=" wfco-connector-delete">
                        <i class="dashicons dashicons-no-alt"></i> <?php echo __( 'Disconnect', 'woofunnels' ); ?> </a>

					<?php
				}


				/** Sync button */
				if ( $connector->is_syncable() ) {
					?>
                    <a href="javascript:void(0)" data-nonce="<?php echo $sync_nonce; ?>" data-id="<?php echo $id; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-sync"><?php echo __( 'Sync', 'woofunnels' ); ?> </a>
					<?php
				}
				?>
                <a href="javascript:void(0)" data-nonce="<?php echo $edit_nonce; ?>" data-id="<?php echo $id; ?>" data-slug="<?php echo $source_slug; ?>" class="button wfco-connector-edit" data-izimodal-open="#modal-edit-connector" data-iziModal-title="<?php echo $modal_title; ?>" data-izimodal-transitionin="comingIn"><?php echo __( 'Settings', 'woofunnels' ); ?> </a>
				<?php

			} else {
				// api data not set for current connector;
				$connector_has_settings = ( true === $connector->has_settings() ) ? 'yes' : 'no';

				$modal_title = __( 'Connect with ', 'woofunnels' ) . $this->name;
				?>
                <a href="javascript:void(0)" data-settings="<?php echo $connector_has_settings; ?>" data-slug="<?php echo $source_slug; ?>" class="wfco_save_btn_style wfco-connector-connect" data-izimodal-open="#wfco-modal-connect" data-iziModal-title="<?php echo $modal_title; ?>" data-izimodal-transitionin="comingIn"><?php echo __( 'Connect', 'woofunnels' ); ?> </a>
				<?php
			}
		} elseif ( $this->is_present() ) {
			$activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $this->file ) . '&amp;plugin_status=all&amp;paged=1', 'activate-plugin_' . $this->file );
			?>
            <a href="<?php echo $activate_url; ?>" data-id="" data-slug="<?php echo $this->slug; ?>" class="wfco_save_btn_style wfco-connector-installed" target="_blank"><?php esc_html_e( 'Activate', 'woofunnels' ); ?></a>
			<?php
		} else {
			?>
            <a href="javascript:void(0)" data-nonce="<?php echo $install_nonce; ?>" data-connector="<?php echo $this->slug; ?>" class="wfco_save_btn_style wfco_connector_install" data-load-text="<?php echo __( 'Installing..', 'woofunnels' ); ?>" data-text="<?php echo esc_attr__( 'Install', 'woofunnels' ); ?>" data-connector-slug="BWFAN_PRO_ENCODE" data-type="<?php echo $this->type ?>"><?php echo esc_html__( 'Install', 'woofunnels' ); ?> </a>
			<?php
		}
	}

	public function is_activated() {
		if ( class_exists( $this->connector_class ) ) {
			return true;
		}

		return false;
	}

	public function show_setting_btn() {
		return $this->show_setting_btn;
	}

	public function is_present() {
		$plugins = get_plugins();
		$file    = trim( $this->file );
		if ( '' !== $this->file && isset( $plugins[ $file ] ) ) {
			return true;
		}

		return false;
	}
}
