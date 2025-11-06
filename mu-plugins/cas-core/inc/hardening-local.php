<?php
/**
 * Local hardening rules (optional)
 *
 * Disables xml-rpc and file editors when WP_ENVIRONMENT_TYPE === 'local'.
 * This file is included but only active when the environment is local.
 *
 * PHP version 8.2
 *
 * @package cas-core
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Apply local-only hardening if environment is 'local'.
 */
function cas_local_hardening(): void {
    $env = defined( 'WP_ENVIRONMENT_TYPE' ) ? wp_get_environment_type() : 'production';
    if ( 'local' !== $env ) {
        return;
    }

    // Disable XML-RPC
    add_filter( 'xmlrpc_enabled', '__return_false' );

    // Disable file editors in the admin
    if ( is_admin() ) {
        if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
            define( 'DISALLOW_FILE_EDIT', true );
        }
        if ( ! defined( 'DISALLOW_FILE_MODS' ) ) {
            define( 'DISALLOW_FILE_MODS', true );
        }
    }
}
add_action( 'init', 'cas_local_hardening', 1 );
