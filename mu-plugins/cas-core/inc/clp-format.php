<?php
/**
 * Chilean peso formatting helpers
 *
 * Provides cas_clp() to format numbers as $X.XXX.XXX (no decimals).
 * Logs a debug notice when a float is passed to help discover callers.
 *
 * PHP version 8.2
 *
 * @package cas-core
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'cas_clp' ) ) {
    /**
     * Format a numeric amount as Chilean peso currency with thousands separators.
     *
     * - Rounds floats and decimal strings to nearest integer.
     * - Returns a string like "$1.234.567" (no decimals).
     * - If WP_DEBUG and original value was float (not an integer-like string), logs a notice.
     *
     * @param int|float|string $amount Amount to format.
     * @return string Formatted CLP with leading $.
     */
    function cas_clp( $amount ): string {
        // Preserve original for debug detection
        $original = $amount;

        // Normalize: if string, try to remove thousands separators and commas
        if ( is_string( $amount ) ) {
            // Remove non-numeric characters except dot and minus
            $clean = preg_replace( '/[^0-9\.-]/', '', $amount );
            if ( null !== $clean ) {
                $amount = $clean;
            }
        }

        // Cast to float for rounding when needed
        if ( is_numeric( $amount ) ) {
            // If the value appears decimal or float-like, round to nearest integer
            if ( is_float( $amount + 0 ) || false !== strpos( (string) $amount, '.' ) ) {
                $rounded = (int) round( (float) $amount );
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_float( $amount + 0 ) ) {
                    // Use error_log to avoid depending on logger libraries
                    error_log( '[cas_clp] Rounded float to integer: original=' . print_r( $original, true ) . '; rounded=' . $rounded );
                }
                $amount = $rounded;
            }
            // Ensure integer value
            $amount = (int) $amount;
        } else {
            // Non-numeric input -> treat as zero and optionally log
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[cas_clp] Non-numeric amount provided: ' . print_r( $original, true ) );
            }
            $amount = 0;
        }

        // Format with thousands separator '.' and no decimals
        $formatted = number_format( $amount, 0, ',', '.' );

        return '$' . $formatted;
    }
}
