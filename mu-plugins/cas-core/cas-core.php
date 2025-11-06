<?php
/**
 * cas-core MU plugin loader
 *
 * Loads lightweight cross-cutting concerns for the casAhorro site. This
 * file and its includes are intended to be always-on via mu-plugins.
 *
 * PHP version 8.2
 *
 * @package cas-core
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access.
}

/**
 * Directory for includes relative to this file.
 * @var string
 */
define( 'CAS_CORE_DIR', __DIR__ . '/inc' );

// Load local environment hardening (only applies when WP_ENVIRONMENT_TYPE === 'local')
if ( file_exists( dirname( __DIR__ ) . '/inc/hardening-local.php' ) ) {
    require_once dirname( __DIR__ ) . '/inc/hardening-local.php';
}

// Load all includes if directory exists
if ( is_dir( CAS_CORE_DIR ) ) {
    foreach ( glob( CAS_CORE_DIR . '/*.php' ) as $file ) {
        // Use include_once to avoid redeclaration if MU plugin reloaded
        include_once $file;
    }
}
