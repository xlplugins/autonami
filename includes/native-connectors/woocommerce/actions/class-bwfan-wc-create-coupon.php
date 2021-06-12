<?php

final class BWFAN_WC_Create_Coupon extends BWFAN_Action {

	private static $ins = null;

	protected function __construct() {
		$this->action_name     = __( 'Create Coupon', 'wp-marketing-automations' );
		$this->action_desc     = __( 'This action creates a personalized coupon for the customer', 'wp-marketing-automations' );
		$this->required_fields = array( 'coupon', 'coupon_name' );

		$this->action_priority = 5;
	}

	public function load_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 98 );
		add_action( 'bwfan_delete_expired_coupons', array( $this, 'handle_delete_expired_coupons' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Localize data for html fields for the current action.
	 */
	public function admin_enqueue_assets() {
		if ( BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			$coupons            = [];
			$searched_coupons   = array();
			$action_slug        = $this->get_slug();
			$automation_actions = BWFAN_Core()->automations->get_automation_details();

			if ( is_array( $automation_actions ) && count( $automation_actions ) > 0 ) {
				foreach ( $automation_actions as $single_row_actions ) {
					if ( is_array( $single_row_actions ) && count( $single_row_actions ) > 0 ) {
						foreach ( $single_row_actions as $action_details ) {
							if ( isset( $action_details['action_slug'] ) && $action_details['action_slug'] === $action_slug ) {
								$coupons[] = $action_details['data']['coupon'];
							}

							if ( ! is_array( $action_details ) ) {
								continue;
							}

							foreach ( $action_details as $single_action ) {
								if ( isset( $single_action['action_slug'] ) && $single_action['action_slug'] === $action_slug && isset( $single_action['data']['searched_coupon'] ) ) {
									$searched_coupon = $single_action['data']['searched_coupon'];
									$searched_coupon = json_decode( $searched_coupon, ARRAY_A );
									if ( isset( $searched_coupon['id'] ) ) {
										$searched_coupons[ $searched_coupon['id'] ] = get_the_title( absint( $searched_coupon['id'] ) );
									}

								}
							}
						}
					}
				}
			}
			$coupons = array_unique( $coupons );

			if ( is_array( $coupons ) && count( $coupons ) > 0 ) {
				$args = array(
					'post__in'    => $coupons,
					'orderby'     => 'title',
					'order'       => 'asc',
					'post_type'   => 'shop_coupon',
					'post_status' => 'publish',
				);

				$coupons_found = get_posts( $args );
				$result        = [];
				if ( is_array( $coupons_found ) && count( $coupons_found ) > 0 ) {
					foreach ( $coupons_found as $coupon ) {
						$result[ $coupon->ID ] = $coupon->post_title;
					}
				}
				$coupons = $result;
			}

			$restrict_data = array(
				'yes' => __( 'Restrict coupon with user email', 'wp-marketing-automations' ),
			);

			BWFAN_Core()->admin->set_select2ajax_js_data( 'coupon', $coupons );
			BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'restrict_options', $restrict_data );
			if ( ! empty( $searched_coupons ) ) {
				BWFAN_Core()->admin->set_actions_js_data( $this->get_class_slug(), 'automation_searched_coupons', $searched_coupons );
			}
		}
	}

	/**
	 * Show the html fields for the current action.
	 */
	public function get_view() {
		?>

        <script>
            jQuery(document).ready(function ($) {
                $('body').on('change', '.bwfan-coupon-search', function () {
                    var temp_coupon = {id: $(this).val(), name: $(this).find(':selected').text()};

                    $(this).parent().find('.bwfan_searched_coupon_name').val(JSON.stringify(temp_coupon));
                });
            });
        </script>

        <script type="text/html" id="tmpl-action-<?php esc_attr_e( $this->get_slug() ); ?>">
            <#
            automation_searched_coupons = _.has(data.actionFieldsOptions, 'automation_searched_coupons') && _.isObject(data.actionFieldsOptions.automation_searched_coupons) ? data.actionFieldsOptions.automation_searched_coupons : '';
            searched_coupon = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'searched_coupon')) ? data.actionSavedData.data.searched_coupon : '';
            if(!_.isEmpty(searched_coupon) && !_.isEmpty(automation_searched_coupons) ) {
            try {
            searched_coupon = JSON.parse(searched_coupon);
            searched_coupon.name = automation_searched_coupons[searched_coupon.id];
            }
            catch(e) {
            //Do Nothing
            }
            }

            selected_coupon = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'coupon')) ? data.actionSavedData.data.coupon : '';
            entered_coupon_name = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'coupon_name')) ? data.actionSavedData.data.coupon_name : '';
            entered_expiry = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'expiry')) ? data.actionSavedData.data.expiry : '';
            entered_restrict = (_.has(data.actionSavedData, 'data') && _.has(data.actionSavedData.data, 'restrict')) ? data.actionSavedData.data.restrict : '';

            selected_event = BWFAN_Auto.uiDataDetail.trigger.event;
            if(selected_event=='ab_cart_abandoned' && '' == entered_coupon_name){
            entered_coupon_name = '{{cart_billing_first_name}}SHOP{{cart_abandoned_id}}';
            }
            #>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php esc_html_e( 'Select Coupon', 'wp-marketing-automations' ); ?>
					<?php
					$message = __( "The selected Coupon data will be used to generate a new coupon", 'wp-marketing-automations' );
					echo $this->add_description( esc_html__( $message ), '2xl', 'right' ); //phpcs:ignore WordPress.Security.EscapeOutput
					?>
                </label>
                <select required id="" data-search="coupon" data-search-text="<?php esc_attr_e( 'Select Coupon', 'wp-marketing-automations' ); ?>" class="bwfan-select2ajax-single bwfan-coupon-search bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][coupon]">
                    <option value=""><?php esc_html_e( 'Choose Coupon', 'wp-marketing-automations' ); ?></option>
                    <#
                    if(_.size(searched_coupon) >0) {
                    temp_selected_coupon = _.isObject(searched_coupon) ? searched_coupon : JSON.parse(searched_coupon);
                    if(temp_selected_coupon.id == selected_coupon){
                    #>
                    <option value="{{temp_selected_coupon.id}}" selected>{{temp_selected_coupon.name}}</option>
                    <#
                    }
                    }

                    stringify_searched_coupon = _.isObject(searched_coupon) ? JSON.stringify(searched_coupon) : searched_coupon;
                    #>
                </select>
                <input type="hidden" class="bwfan_searched_coupon_name" name="bwfan[{{data.action_id}}][data][searched_coupon]" value="{{stringify_searched_coupon}}"/>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title">
					<?php esc_html_e( 'New Coupon Code', 'wp-marketing-automations' ); ?>
					<?php echo $this->inline_merge_tag_invoke(); //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </label>
                <input required type="text" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][coupon_name]" value="{{entered_coupon_name}}"/>
                <div class="clearfix bwfan_field_desc">
					<?php esc_html_e( 'Use merge tags to generate personalized coupon', 'wp-marketing-automations' ); ?>
                </div>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Coupon Expiry (days)', 'wp-marketing-automations' ); ?></label>
                <div class="bwfan-col-sm-4 bwfan-pl-0">
                    <input min="0" type="number" placeholder="xx" class="bwfan-input-wrapper" name="bwfan[{{data.action_id}}][data][expiry]" value="{{entered_expiry}}"/>
                </div>
                <div class="clearfix bwfan_field_desc">
					<?php esc_html_e( 'Leave blank for no coupon expiry', 'wp-marketing-automations' ); ?>
                </div>
            </div>
            <div class="bwfan-input-form clearfix">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Email Restriction', 'wp-marketing-automations' ); ?></label>
                <#
                if(_.has(data.actionFieldsOptions, 'restrict_options') && _.isObject(data.actionFieldsOptions.restrict_options) ) {
                _.each( data.actionFieldsOptions.restrict_options, function( value, key ){
                checked = '';
                if(entered_restrict!='' && _.contains(entered_restrict, key)){
                checked = 'checked';
                }
                #>
                <input type="checkbox" name="bwfan[{{data.action_id}}][data][restrict][]" id="bwfan-{{key}}" value="{{key}}" class="<?php esc_attr_e( $this->get_slug() ); ?>-restrict" {{checked}}/>
                <label for="bwfan-{{key}}" class="bwfan-checkbox-label">{{value}}</label>
                <# })
                }
                #>
            </div>
        </script>
		<?php
	}

	/**
	 * Make all the data which is required by the current action.
	 * This data will be used while executing the task of this action.
	 *
	 * @param $integration_object
	 * @param $task_meta
	 *
	 * @return array
	 */
	public function make_data( $integration_object, $task_meta ) {
		$this->set_data_for_merge_tags( $task_meta );
		$data_to_set                = array();
		$data_to_set['email']       = $task_meta['global']['email'];
		$data_to_set['coupon']      = $task_meta['data']['coupon'];
		$data_to_set['coupon_name'] = BWFAN_Common::decode_merge_tags( $task_meta['data']['coupon_name'] );
		$data_to_set['expiry']      = ( isset( $task_meta['data']['expiry'] ) && 0 < intval( $task_meta['data']['expiry'] ) ) ? $task_meta['data']['expiry'] : '';
		$data_to_set['restrict']    = '';

		if ( isset( $task_meta['data']['restrict'] ) ) {
			if ( is_array( $task_meta['data']['restrict'] ) && count( $task_meta['data']['restrict'] ) > 0 ) {
				$data_to_set['restrict'] = 1;
			}
		}

		return $data_to_set;
	}

	/**
	 * Execute the current action.
	 * Return 3 for successful execution , 4 for permanent failure.
	 *
	 * @param $action_data
	 *
	 * @return array
	 */
	public function execute_action( $action_data ) {
		$this->set_data( $action_data['processed_data'] );
		$result = $this->process();
		if ( $this->fields_missing ) {
			return array(
				'status'  => 4,
				'message' => $result['body'][0],
			);
		}

		if ( is_array( $result ) && count( $result ) > 0 ) { // Error in coupon creation
			return array(
				'status'  => 4,
				'message' => $result['err_msg'],
			);
		}

		if ( ! is_integer( $result ) ) {
			return array(
				'status'  => 4,
				'message' => __( 'Coupon does not exist', 'wp-marketing-automations' ),
			);
		}
		$get_type = get_post_field( 'post_type', $result );

		if ( 'shop_coupon' !== $get_type ) {
			return array(
				'status'  => 4,
				'message' => __( 'Coupon does not exist', 'wp-marketing-automations' ),
			);
		}

		return array(
			'status'  => 3,
			'message' => "Coupon {$this->data['coupon_name']} created."
		);
	}

	/**
	 * Process and do the actual processing for the current action.
	 * This function is present in every action class.
	 */
	public function process() {
		$is_required_fields_present = $this->check_fields( $this->data, $this->required_fields );
		if ( false === $is_required_fields_present ) {
			$this->fields_missing = true;

			return $this->show_fields_error();
		}

		$data            = $this->data;
		$coupon_name     = $data['coupon_name'];
		$coupon_meta     = $this->get_coupon_data( $data['coupon'] );
		$get_wc_coupon   = new WC_Coupon( $coupon_name );
		$new_coupon_meta = $coupon_meta;

		if ( $get_wc_coupon->get_id() === 0 ) {
			/** Create a new coupon */
			$new_coupon_meta['_is_bwfan_coupon']     = 1;
			$new_coupon_meta['_bwfan_automation_id'] = $data['automation_id'];
			$new_coupon_meta['expiry_date']          = '';
			$new_coupon_meta['date_expires']         = '';
			$new_coupon_meta['usage_count']          = '0';
			$new_coupon_meta['customer_email']       = [];

			if ( ! empty( $data['expiry'] ) && 0 < absint( $data['expiry'] ) ) {
				$expiry                          = $this->get_expiry_dates( $data['expiry'] );
				$new_coupon_meta['expiry_date']  = $expiry['expire_on'];
				$new_coupon_meta['date_expires'] = $expiry['expiry_timestamped'];
			}

			$coupon_id = $this->create_coupon( $coupon_name, $new_coupon_meta );
			if ( is_array( $coupon_id ) && count( $coupon_id ) > 0 ) {
				/** Some error occurred while making coupon post */
				return $coupon_id;
			}

			if ( 1 === absint( $data['restrict'] ) ) {
				$this->handle_coupon_restriction( $coupon_id, $data['email'] );
			}

			do_action( 'bwfan_coupon_created', $coupon_id );

			return $coupon_id;
		} else {
			/** Update an existing coupon */
			$coupon_id   = $get_wc_coupon->get_id();
			$coupon_meta = [];
			if ( ! empty( $data['expiry'] ) && 0 < absint( $data['expiry'] ) ) {
				$expiry                      = $this->get_expiry_dates( $data['expiry'] );
				$coupon_meta['expiry_date']  = $expiry['expire_on'];
				$coupon_meta['date_expires'] = $expiry['expiry_timestamped'];

				foreach ( $coupon_meta as $key => $val ) {
					update_post_meta( $coupon_id, $key, $val );
				}
			}

			if ( 1 === absint( $data['restrict'] ) ) {
				$this->handle_coupon_restriction( $coupon_id, $data['email'] );
			} else {
				$get_wc_coupon->set_email_restrictions( [] );
				$get_wc_coupon->save();
			}

			do_action( 'bwfan_coupon_created', $coupon_id );

			return $coupon_id;
		}
	}

	public function get_coupon_data( $coupon_id ) {
		$coupon_meta = array();
		$meta        = get_post_meta( $coupon_id );
		if ( is_array( $meta ) && count( $meta ) > 0 ) {
			foreach ( $meta as $key => $val ) {
				if ( '_edit_lock' !== $key && '_edit_last' !== $key ) {
					$coupon_meta[ $key ] = maybe_serialize( $val[0] ) ? maybe_unserialize( $val[0] ) : $val[0];
				}
			}
		}

		return $coupon_meta;
	}

	public function get_expiry_dates( $no_of_days ) {

		$dbj        = new DateTime();
		$no_of_days += 1;

		$exptime = strtotime( "+{$no_of_days} days" );
		$dbj->setTimestamp( $exptime );
		$exp_date         = $dbj->format( 'Y-m-d' );
		$exp_date_email   = date( 'Y-m-d', $exptime );
		$expiry_timestamp = $exptime;

		$date = array(
			'expiry'             => $exp_date,
			'expire_on'          => $exp_date_email,
			'expiry_timestamped' => $expiry_timestamp,
		);

		return $date;
	}

	public function create_coupon( $coupon_name, $meta_data ) {
		$args = array(
			'post_type'   => 'shop_coupon',
			'post_status' => 'publish',
			'post_title'  => $coupon_name,
		);

		$coupon_id = wp_insert_post( $args );
		if ( ! is_wp_error( $coupon_id ) ) {
			$meta_data['usage_count'] = 0;
			if ( is_array( $meta_data ) && count( $meta_data ) > 0 ) {
				foreach ( $meta_data as $key => $val ) {
					update_post_meta( $coupon_id, $key, $val );
				}
			}
			$result = $coupon_id;

			return $result;
		}

		$errormsg = $coupon_id->get_error_message();
		$result   = array(
			'err_msg' => $errormsg,
		);

		return $result;
	}

	/**
	 * @param $coupon_id
	 * @param $email
	 *
	 * @return void|WC_Coupon
	 */
	public function handle_coupon_restriction( $coupon_id, $email ) {
		if ( ! is_email( $email ) ) {
			return;
		}

		$coupon = new WC_Coupon( $coupon_id );

		if ( 0 === $coupon->get_id() ) {
			return;
		}

		$coupon->set_email_restrictions( [ $email ] );
		$coupon->save();

		return $coupon;
	}

	public function handle_delete_expired_coupons() {
		global $wpdb;
		$coupons = $wpdb->get_results( $wpdb->prepare( "
                                                SELECT m1.post_id as id
                                                FROM {$wpdb->prefix}postmeta as m1
                                                LEFT JOIN {$wpdb->prefix}postmeta as m2
                                                ON m1.post_id = m2.post_id
                                                WHERE m1.meta_key = %s
                                                AND m1.meta_value = %d
                                                AND m2.meta_key = %s
                                                AND TIMESTAMPDIFF(MINUTE,m2.meta_value,UTC_TIMESTAMP) > %d
                                                LIMIT %d
                                                ", '_is_bwfan_coupon', 1, 'expiry_date', 0, 20 ) );

		if ( empty( $coupons ) ) {
			bwf_unschedule_actions( 'bwfan_delete_expired_coupons' );

			return;
		}

		foreach ( $coupons as $coupon ) {
			wp_delete_post( $coupon->id, true );
		}
	}

}

/**
 * Register this action. Registering the action will make it eligible to see it on single automation screen in select actions dropdown.
 */
return 'BWFAN_WC_Create_Coupon';
