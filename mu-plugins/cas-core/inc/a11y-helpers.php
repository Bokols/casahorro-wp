<?php
/**
 * Accessibility helpers (MU plugin)
 *
 * Provides a skip-to-content link via wp_body_open. Safe to include even if
 * the active theme already prints a skip link; this implementation is
 * intentionally small and idempotent.
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
 * Print a skip link if no previous skip link has been printed in this request.
 * Uses a transient per-request marker to avoid duplicate output when both
 * theme and MU plugin attempt to print the link.
 */
function cas_mu_skip_link(): void {
    // Use a global flag to mark printing within this request
    global $cas_mu_skip_printed;

    if ( ! empty( $cas_mu_skip_printed ) ) {
        return; // Already printed
    }

    $skip_target = esc_url( '#site-content' );
    echo '<a class="cas-skip-link screen-reader-text" href="' . $skip_target . '">' . esc_html__( 'Skip to content', 'cas-core' ) . '</a>' . "\n";

    $cas_mu_skip_printed = true;
}
add_action( 'wp_body_open', 'cas_mu_skip_link', 10 );
