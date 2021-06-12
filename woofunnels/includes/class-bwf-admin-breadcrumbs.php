<?php
/**
 * Class to control breadcrumb and its behaviour accross the buildwoofunnels
 * @author buildwoofunnels
 */
if ( ! class_exists( 'BWF_Admin_Breadcrumbs' ) ) {

	class BWF_Admin_Breadcrumbs {

		private static $ins = null;

		/**
		 * @var array nodes use to contain all the nodes
		 */
		public static $nodes = [];

		/**
		 * @var array ref used to contain refs to pass to the urls
		 */
		public static $ref = [];

		/**
		 * Insert a single node into the property
		 *
		 * @param $config [] of the node getting registered
		 */
		public static function register_node( $config ) {
			self::$nodes[] = wp_parse_args( $config, [ 'class' => '', 'link' => '', 'text' => '' ] );
		}


		/**
		 * Insert a referral property so that we can populate the referral accross all urls.
		 *
		 * @param $key
		 * @param $val
		 */
		public static function register_ref( $key, $val ) {
			self::$ref[ $key ] = $val;
		}

		/**
		 * Render HTML for all the registered nodes
		 */
		public static function render() {
			if ( empty( self::$nodes ) ) {
				return '';
			}
			$last_item = end( self::$nodes );
			?>
			<ul>
				<li class="<?php echo esc_attr( $last_item['class'] ) ?>">
					<?php echo wp_kses_post( $last_item['text'] ); ?>
				</li>
			</ul>
			<?php
		}

		public static function render_top_bar() {
			if ( empty( self::$nodes ) ) {
				return false;
			}

			if ( ! is_array( self::$nodes ) || count( self::$nodes ) == 0 ) {
				return false;
			}

			self::$nodes = array_filter( self::$nodes, function ( $v ) {
				if ( isset( $v['text'] ) && ! empty( $v['text'] ) ) {
					return true;
				}
			} );

			if ( ! is_array( self::$nodes ) || count( self::$nodes ) == 0 ) {
				return false;
			}

			$count = count( self::$nodes );
			$h     = 0;
			foreach ( self::$nodes as $menu ) {
				if ( ! isset( $menu['text'] ) || empty( $menu['text'] ) ) {
					continue;
				}
				$h ++;

				echo '<span>';
				if ( $count !== $h && isset( $menu['link'] ) && ! empty( $menu['link'] ) ) {
					echo '<a href="' . $menu['link'] . '">' . $menu['text'] . '</a>';
				} else {
					echo $menu['text'];
				}
				echo '</span>';
			}

			return self::$nodes;
		}


		/**
		 * Add the registered referral to the url passed
		 * ref should contain the query param as key and value as value
		 *
		 * @param $url URL to add refs to
		 *
		 * @return string modified url
		 */
		public static function maybe_add_refs( $url ) {
			if ( empty( self::$ref ) ) {
				return $url;
			}

			return add_query_arg( self::$ref, $url );
		}


		public static function render_sticky_bar() {
			?>
			<style>
                /* Sticky Bar */
                .bwf-header-bar {
                    background: #fff;
                    box-sizing: border-box;
                    border-bottom: 1px solid #fff;
                    padding: 0 0 0 20px;
                    min-height: 56px;
                    position: fixed;
                    width: 100%;
                    top: 32px;
                    z-index: 1001;
                    display: flex;
                    align-items: center;
                    box-shadow: 0 0px 10px 0 #c8c8c8
                }

                .bwf-header-bar > img {
                    max-width: 24px
                }

                .bwf-bar-navigation {
                    font-size: 16px;
                    padding-left: 15px;
                    display: flex
                }

                .bwf-bar-navigation > span {
                    padding-right: 25px;
                    position: relative
                }

                .bwf-bar-navigation > span a {
                    text-decoration: none;
                    font-weight: normal
                }

                .bwf-bar-navigation > span:after {
                    content: "\f345";
                    font-family: 'dashicons';
                    font-size: 15px;
                    position: absolute;
                    right: 4px;
                    top: 1px
                }

                .bwf-bar-navigation > span:last-child:after {
                    content: ""
                }

                .bwf-bar-quick-links {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    position: fixed;
                    right: 0;
                    top: 32px;
                    height: 56px
                }

                .bwf-bar-quick-links a.bwf-bar-link {
                    display: block;
                    font-size: 13px;
                    height: 56px;
                    text-decoration: none;
                    text-align: center;
                    padding: 0 10px;
                    min-width: 70px;
                    transition: all 0.4s ease;
                    -webkit-transition: all 0.4s ease;
                    box-sizing: border-box
                }

                .bwf-bar-quick-links a.bwf-bar-link:hover {
                    background: #f0f0f0
                }

                .bwf-bar-quick-links a * {
                    display: block;
                    margin: 0 auto;
                    padding: 0;
                    float: none;
                    color: #757575
                }

                .bwf-bar-quick-links a i {
                    font-size: 20px;
                    color: #757575;
                    margin-top: 8px
                }

                .wrap.bwf-funnel-common {
                    padding: 60px 0 0 20px;
                    margin: 0 20px 0 0
                }
                .bwf-header-bar .bwf-breadcrub-svg-icon {
                    max-width: 35px;
                }
			</style>
			<div class="bwf-header-bar">
				<img class="bwf-breadcrub-svg-icon" src="<?php echo esc_url( plugin_dir_url( WooFunnel_Loader::$ultimate_path ) . 'woofunnels/assets/img/bwf-icon-white-bg.svg'); ?>"/>
				<div class="bwf-bar-navigation">
					<?php
					global $submenu;
					if ( array_key_exists( 'bwf_dashboard', $submenu ) ) {
						echo '<span><a href="' . admin_url( 'admin.php?page=bwf_dashboard' ) . '">WooFunnels</a></span> ';
					}
					if ( method_exists( 'BWF_Admin_Breadcrumbs', 'render_top_bar' ) ) {
						BWF_Admin_Breadcrumbs::render_top_bar();
					}
					?>
				</div>
				<div class="bwf-bar-quick-links">
					<a class="bwf-bar-link" href="https://buildwoofunnels.com/documentation/" target="_blank">
						<i class="dashicons dashicons-format-chat"></i>
						<span>Docs</span>
					</a>
					<a class="bwf-bar-link" href="https://buildwoofunnels.com/support/" target="_blank">
						<i class="dashicons dashicons-businessman"></i>
						<span>Support</span>
					</a>
				</div>
			</div>
			<?php
		}


	}
}
