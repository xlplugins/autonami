<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$max_width = absint( apply_filters( 'bwfan_email_raw_width', 600 ) );
$max_width = ( $max_width > 300 ) ? $max_width : 600;

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
    body {
    padding: 0 15px;
    direction:<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>;
    }

    #body_content {
    background-color: <?php echo esc_attr( $body ); ?>;
    width: 100%;
    max-width: <?php echo $max_width; ?>px;
    direction:<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>
    }

    #body_content table {
    margin: 0 0 16px;
    }

    #body_content table td {
    padding: 10px 18px;
    }

    #body_content table td td {
    padding: 12px;
    }

    #body_content table td th {
    padding: 12px;
    }

    #body_content h1, #body_content h2, #body_content h3, #body_content h4 {
    margin: 0 0 16px;
    line-height: 1.5;
    }

    #body_content h1 {
    font-size: <?php echo( absint( $font_size ) + 9 ); ?>px;
    }

    #body_content h2 {
    font-size: <?php echo( absint( $font_size ) + 6 ); ?>px;
    }

    #body_content h3 {
    font-size: <?php echo( absint( $font_size ) + 3 ); ?>px;
    }

    #body_content h4 {
    font-size: <?php echo $font_size; ?>px;
    font-weight: normal;
    }

    #body_content h1:last-child, #body_content h2:last-child, #body_content h3:last-child, #body_content h4:last-child {
    margin: 0;
    }

    #body_content p {
    margin: 0 0 16px;
    line-height: 1.5;
    font-size: <?php echo $font_size; ?>px;
    }

    #body_content ul {
    display: block;
    list-style-type: disc;
    margin-block-start: 15px;
    margin-block-end: 15px;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    padding-inline-start: 30px;
    }

    #body_content li {
    margin: 0 0 10px;
    display: list-item;
    font-size: <?php echo $font_size; ?>px;
    }

    .td {
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
    vertical-align: middle;
    }

    .address {
    padding: 12px;
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    border: 1px solid <?php echo esc_attr( $body_darker_10 ); ?>;
    }

    .text {
    color: <?php echo esc_attr( $text ); ?>;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    }

    .link {
    color: <?php echo esc_attr( $base ); ?>;
    }

    #header_wrapper {
    padding: 36px 48px;
    display: block;
    }

    a {
    color: <?php echo esc_attr( $link_color ); ?>;
    font-weight: normal;
    text-decoration: underline;
    display: inline-block;
    }

    img {
    border: none;
    display: inline-block;
    height: auto;
    outline: none;
    vertical-align: middle;
    margin: 0;
    max-width: 100%;
    }

    img.aligncenter {
    display: block;
    margin: 0 auto;
    }

    img.alignleft {
    float: left;
    margin: 0.5em 1em 0.5em 0;
    }

    img.alignright {
    float: right;
    margin: 0.5em 0 0.5em 1em;
    }

    .bwfan-product-grid-container img.bwfan-product-image {
    margin-bottom: 15px;
    }

    .autonami-button {
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