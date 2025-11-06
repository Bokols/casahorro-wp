<?php
/**
 * Environment banner for admin/preview UIs
 *
 * Prints a small badge in the footer when the environment is not 'production'.
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
 * Echo a small environment badge in the footer when WP_ENVIRONMENT_TYPE is not 'production'.
 */
function cas_env_banner_footer(): void {
    $env = defined( 'WP_ENVIRONMENT_TYPE' ) ? wp_get_environment_type() : 'production';

    if ( 'production' === $env ) {
        return; // Don't show anything in production.
    }

    $label = strtoupper( esc_html( $env ) );
    // Minimal inline style to avoid dependency on theme CSS
    echo '<div class="cas-env-banner" aria-hidden="true" style="position:fixed;right:1rem;bottom:1rem;background:var(--color-text-primary);color:#fff;padding:0.25rem 0.5rem;border-radius:3px;font-size:12px;z-index:99999;opacity:0.9;">' . $label . '</div>' . "\n";
}
add_action( 'wp_footer', 'cas_env_banner_footer', 999 );
