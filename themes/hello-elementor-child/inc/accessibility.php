<?php
/**
 * Accessibility helpers
 *
 * Provides a skip-to-content link on wp_body_open and minimal CSS to
 * reveal it when focused. Keeps markup small and unobtrusive.
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output a skip-to-content link immediately after body tag via wp_body_open.
 *
 * The link is visually-hidden by default and becomes visible when focused.
 */
function hello_elementor_child_skip_link(): void {
    // Use esc_url to be safe if anchor is filtered later
    $skip_target = esc_url( '#site-content' );
    echo '<a class="hello-skip-link screen-reader-text" href="' . $skip_target . '">' . esc_html__( 'Skip to content', 'hello-elementor-child' ) . '</a>' . "\n";
}
add_action( 'wp_body_open', 'hello_elementor_child_skip_link', 5 );

/**
 * Print minimal CSS for the skip link.
 *
 * This keeps CSS tiny and avoids adding it to the main stylesheet which
 * might be overridden by page builders. The style is low-specificity.
 */
function hello_elementor_child_skip_link_css(): void {
    ?>
    <style id="hello-elementor-child-skip-link" type="text/css">
    .hello-skip-link{
        position: absolute;
        left: -9999px;
        top: auto;
        width: 1px;
        height: 1px;
        overflow: hidden;
    }
    .hello-skip-link:focus, .hello-skip-link:active{
        position: fixed;
        left: 1rem;
        top: 1rem;
        width: auto;
        height: auto;
        padding: 0.5rem 0.75rem;
        background: #000;
        color: #fff;
        z-index: 9999;
        border-radius: 4px;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    </style>
    <?php
}
add_action( 'wp_head', 'hello_elementor_child_skip_link_css', 1 );
