<?php
/**
 * Theme setup helpers
 *
 * Registers theme supports, menus, and other setup only.
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
 * Setup theme supports and register menus.
 */
function hello_elementor_child_setup(): void {
    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Support featured images
    add_theme_support( 'post-thumbnails' );

    // Register a small utility menu (Ribbon)
    register_nav_menus( array(
        'utility' => __( 'Utility Menu (Ribbon)', 'hello-elementor-child' ),
    ) );

    // Ensure WordPress outputs HTML5 markup where applicable
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

    // Prefer theme fonts; do not print Google Fonts by default (Elementor handled in functions.php)
}
add_action( 'after_setup_theme', 'hello_elementor_child_setup', 20 );
