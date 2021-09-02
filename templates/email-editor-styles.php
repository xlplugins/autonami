<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$font_size = absint( apply_filters( 'bwfan_email_text_font_size', 15 ) );
$font_size = ( $font_size > 9 && $font_size < 31 ) ? $font_size : 15;

$bg         = '#ffffff';
$body       = '#ffffff';
$base       = '#ffffff';
$base_text  = '#000000';
$text       = '#000000';
$link_color = '#045fb4';

$bg_darker_10    = '#e5e5e5';
$body_darker_10  = '#e5e5e5';
$base_lighter_20 = '#ffffff';
$base_lighter_40 = '#ffffff';
$text_lighter_20 = '#333333';
$text_lighter_40 = '#666666';

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
// body{padding: 0;} ensures proper scale/positioning of the email in the iOS native email app.

ob_start();
?>
    .bwfan-email-table-wrap table {
    margin: 0 0 16px;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    border: 1px solid <?php echo esc_attr( $text_lighter_40 ); ?> !important;
    }

    .bwfan-email-table-wrap table td {
    padding: 10px 18px;
    vertical-align: middle;
    }

    .bwfan-email-table-wrap table td td {
    padding: 12px;
    }

    .bwfan-email-table-wrap table td th {
    padding: 12px;
    }

    .bwfan-email-table-wrap h1, .bwfan-email-table-wrap h2, .bwfan-email-table-wrap h3, .bwfan-email-table-wrap h4 {
    margin: 0 0 16px;
    line-height: 130%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
    }

    .bwfan-email-table-wrap h1 {
    font-size: <?php echo( absint( $font_size ) + 6 ); ?>px;
    }

    .bwfan-email-table-wrap h2 {
    font-size: <?php echo( absint( $font_size ) + 3 ); ?>px;
    }

    .bwfan-email-table-wrap h3 {
    font-size: <?php echo( absint( $font_size + 1 ) ); ?>px;
    }

    .bwfan-email-table-wrap h4 {
    font-size: <?php echo( absint( $font_size - 1 ) ); ?>px;
    font-weight: normal;
    }

    .bwfan-email-table-wrap h1:last-child, .bwfan-email-table-wrap h2:last-child, .bwfan-email-table-wrap h3:last-child, .bwfan-email-table-wrap h4:last-child {
    margin: 0;
    }

    .bwfan-email-table-wrap p {
    margin: 0 0 16px;
    line-height: 1.5;
    font-size: <?php echo $font_size; ?>px;
    }

    .bwfan-email-table-wrap ul {
    display: block;
    list-style-type: disc;
    margin-block-start: 15px;
    margin-block-end: 15px;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    padding-inline-start: 30px;
    }

    .bwfan-email-table-wrap li {
    margin: 0 0 10px;
    display: list-item;
    font-size: <?php echo $font_size; ?>px;
    }

    .bwfan-email-table-wrap a {
    color: <?php echo esc_attr( $link_color ); ?>;
    font-weight: normal;
    text-decoration: underline;
    display: inline-block;
    }

    .bwfan-email-table-wrap img {
    border: none;
    display: inline-block;
    height: auto;
    outline: none;
    vertical-align: middle;
    margin: 0;
    max-width: 100%;
    }

    .bwfan-email-product-2-col.bwfan-email-table-wrap .bwfan-product-grid-item-2-col img,
    .bwfan-email-product-3-col.bwfan-email-table-wrap .bwfan-product-grid-item-3-col img {
    padding-top: 20px;
    }

    .bwfan-email-table-wrap img.aligncenter {
    display: block;
    margin: 0 auto;
    }

    .bwfan-email-table-wrap img.alignleft {
    float: left;
    margin: 0.5em 1em 0.5em 0;
    }

    .bwfan-email-table-wrap img.alignright {
    float: right;
    margin: 0.5em 0 0.5em 1em;
    }

    .bwfan-email-table-wrap .bwfan-product-grid-container img.bwfan-product-image {
    margin-bottom: 15px;
    }

    .bwfan-email-table-wrap .autonami-button {
    font-weight: bold;
    border-radius: 4px;
    display: inline-block;
    padding: 12px 20px;
    margin: 8px auto;
    font-size: 14px;
    text-align: center;
    text-decoration: none;
    }
<?php
$default_css = ob_get_clean();

if ( true === apply_filters( 'bwfan_display_email_default_css', true ) ) {
	echo $default_css;
}

/** Without style tag */
do_action( 'bwfan_output_email_style' );