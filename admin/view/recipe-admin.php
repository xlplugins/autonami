<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $recipe_data;
$autonami_notifications = BWFAN_Common::get_autonami_notifications();
$page_class             = array( 'bwfan_clearfix' );
if ( 0 < count( $autonami_notifications ) ) {
	$page_class[] = 'bwfan_page_col2_wrap';
}
$register_recipe_entities = BWFAN_Recipe_Loader::get_registered_recipes();
include_once( BWFAN_PLUGIN_DIR . '/includes/recipes/view/recipe-list-view.php' );

if ( class_exists( 'BWFAN_Header' ) ) {
	$header_ins = new BWFAN_Header();
	$header_ins->set_level_1_navigation_active( 'automations' );
	$header_ins->set_level_2_side_navigation( BWFAN_Header::level_2_navigation_automations() );
	$header_ins->set_level_2_side_navigation_active( 'recipes' );
	echo $header_ins->render();
}
?>
<script type="text/html" id="tmpl-bwfan-pagination-ui">
    <#
    var show_items = parseInt(data.show_items);
    var page = parseInt(data.current_page);
    var total = parseInt(data.total);
    var total_pages = parseInt(total / show_items);
    total_pages = (0 === total_pages) ? 1 : (total_pages + (total % show_items));
    #>
    <div class="bwfan-pagination" data-page="{{page}}">
        <# if(0 < total) { #>
        <div class="displaying-num">{{total}} {{total > 1 ? 'items' : 'item'}}</div>
        <div class="pagination-links">
            <# if(1 === page) { #>
            <span class="tablenav-pages-navspan button disabled" data-path="first">&laquo;</span>
            <span class="tablenav-pages-navspan button disabled" data-path="previous">&lsaquo;</span>
            <# } else if(2 === page) { #>
            <span class="tablenav-pages-navspan button disabled" data-path="first">&laquo;</span>
            <a class="previous-page button bwfan_pagination_click" href="javascript:void(0)" data-page="1">
                <span data-path="previous">&lsaquo;</span>
            </a>
            <# } else { #>
            <a class="previous-page button bwfan_pagination_click" href="javascript:void(0)" data-page="1">
                <span data-path="first">&laquo;</span>
            </a>
            <a class="previous-page button bwfan_pagination_click" href="javascript:void(0)" data-page="{{page - 1}}">
                <span data-path="previous">&lsaquo;</span>
            </a>
            <# } #>
        </div>
        <div class="tablenav-paging-text">{{page}} of <span class="total-pages">{{total_pages}} {{total_pages > 1 ? 'pages' : 'page'}}</span></div>
        <div class="pagination-links">
            <# if((total_pages - 1) === page) { #>
            <a class="next-page button bwfan_pagination_click" href="javascript:void(0)" data-page="{{page + 1}}">
                <span data-path="next">&rsaquo;</span>
            </a>
            <span class="tablenav-pages-navspan button disabled" data-path="last">&raquo;</span>
            <# } else if(total_pages === page) { #>
            <span class="tablenav-pages-navspan button disabled" data-path="next">&rsaquo;</span>
            <span class="tablenav-pages-navspan button disabled" data-path="last">&raquo;</span>
            <# } else { #>
            <a class="previous-page button bwfan_pagination_click" href="javascript:void(0)" data-page="{{page + 1}}">
                <span data-path="next">&rsaquo;</span>
            </a>
            <a class="previous-page button bwfan_pagination_click" href="javascript:void(0)" data-page="{{total_pages}}">
                <span data-path="last">&raquo;</span>
            </a>
            <# } #>
        </div>
        <# } #>
    </div>
</script>
<div class="wrap bwfan_page_automations bwfan_global">
    <div id="poststuff">
        <div class="bwfan_recipe">
            <div class="bwfan_r_tabs">
                <div class="bwfan_r_t"><?php esc_html_e( 'Automation Recipes', 'wp-marketing-automations' ); ?></div>
                <ul>
                    <li><a class="bwfan_r_t_active" data-slug="-1" href="javascript:void(0)">All Recipes</a></li>
					<?php
					if ( is_array( $recipe_data['connectors'] ) || count( $recipe_data['connectors'] ) > 0 ) {
						foreach ( $recipe_data['connectors'] as $c_slug => $c_data ) {
							echo "<li><a href='javascript:void(0)' data-slug='" . esc_attr( $c_slug ) . "'>" . esc_html( $c_data['name'] ) . "</a></li>";
						}
					}
					?>
                </ul>
            </div>
            <div class="bwfan_r_wrap">
                <div class="bwfan_r_t">
                    <div class="bwfan_r_t_l">
                        <select name="action" id="bulk-action-selector-top">
                            <option value="-1">Filter By All Plugins</option>
							<?php
							if ( is_array( $recipe_data['plugins'] ) || count( $recipe_data['plugins'] ) > 0 ) {
								foreach ( $recipe_data['plugins'] as $c_slug => $c_data ) {
									echo "<option value='" . esc_attr( $c_slug ) . "'>" . esc_html( $c_data['name'] ) . "</option>";
								}
							}
							?>
                        </select>
                    </div>
                </div>
                <div class="bwfan_r_list">
                </div>
                <div class="bwfan-pagination-wrap">
                    <div class="bwfan-pagination" data-page="1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bwfan_izimodal_default" style="display: none" id="modal-show-recipe-import">
    <div class="sections" id="bwfan-recipe-import">
    </div>
</div>

