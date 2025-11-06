<?php
/**
 * Header Verification Helper
 * Temporary include for testing header functionality
 * 
 * Usage: Add to functions.php temporarily during verification:
 * require_once get_stylesheet_directory() . '/header-verification-helper.php';
 * 
 * @package HelloElementorChild
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue verification script for testing (local only)
 */
function hello_elementor_child_enqueue_verification_script(): void {
    // Only load in local development
    if ( defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local' ) {
        wp_enqueue_script(
            'hello-elementor-child-verification',
            get_stylesheet_directory_uri() . '/assets/js/header-verification.js',
            array(),
            wp_get_theme()->get('Version'),
            true
        );
    }
}

/**
 * Add verification info to admin bar (local only)
 */
function hello_elementor_child_add_verification_admin_bar( $wp_admin_bar ): void {
    // Only show in local development
    if ( ! defined('WP_ENVIRONMENT_TYPE') || WP_ENVIRONMENT_TYPE !== 'local' ) {
        return;
    }

    $wp_admin_bar->add_node( array(
        'id'    => 'header-verification',
        'title' => '🔍 Header Test',
        'href'  => '#',
        'meta'  => array(
            'onclick' => 'console.log("Open DevTools Console for verification results"); return false;'
        )
    ) );
}

/**
 * Display verification instructions (local only)
 */
function hello_elementor_child_verification_notice(): void {
    // Only show in local development and to admins
    if ( ! defined('WP_ENVIRONMENT_TYPE') || WP_ENVIRONMENT_TYPE !== 'local' || ! current_user_can('manage_options') ) {
        return;
    }

    echo '<div class="notice notice-info" style="background: #F8FAFC; border-left: 4px solid #1E293B; padding: 12px;">
        <h3>🔍 Header Verification Active</h3>
        <p><strong>Testing Mode:</strong> Header verification script loaded.</p>
        <p><strong>Instructions:</strong></p>
        <ol>
            <li>Open <strong>DevTools Console</strong> (F12)</li>
            <li>Check automatic verification results</li>
            <li>Run <code>testHeaderInteraction()</code> for mobile testing</li>
            <li>Test at viewports: 640px, 1023px, 1024px, 1508px</li>
        </ol>
        <p><strong>Checklist:</strong> See <code>HEADER-VERIFICATION-CHECKLIST.md</code> for complete testing guide.</p>
    </div>';
}

// Hook the functions (only in local)
if ( defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local' ) {
    add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_verification_script', 999 );
    add_action( 'admin_bar_menu', 'hello_elementor_child_add_verification_admin_bar', 999 );
    add_action( 'admin_notices', 'hello_elementor_child_verification_notice' );
}