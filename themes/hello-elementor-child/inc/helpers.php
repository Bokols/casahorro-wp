<?php
/**
 * Helper Functions
 *
 * Utility functions for Chilean peso formatting, consent checking, etc.
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Format price as Chilean Peso (CLP)
 *
 * Format: $X.XXX.XXX (no decimals, dot as thousands separator)
 *
 * Examples:
 *   15000    => $15.000
 *   1250000  => $1.250.000
 *   499990   => $499.990
 *
 * @param string|int|float|null $price Price to format.
 * @return string Formatted price with CLP symbol.
 */
function hello_elementor_child_format_clp( $price ): string {
    if ( $price === null || $price === '' ) {
        return '';
    }

    // Convert to integer (remove decimals)
    $amount = (int) round( (float) $price );

    // Format with dot as thousands separator
    $formatted = number_format( $amount, 0, ',', '.' );

    return '$' . $formatted;
}

/**
 * Check if user has granted analytics consent via CookieYes
 *
 * @return bool
 */
function hello_elementor_child_has_analytics_consent(): bool {
    // Check for CookieYes analytics consent cookie
    if ( isset( $_COOKIE['cookieyes-analytics'] ) ) {
        return $_COOKIE['cookieyes-analytics'] === 'yes';
    }

    return false;
}

/**
 * Check if user has granted consent for a specific category
 *
 * @param string $category Category name (e.g., 'analytics', 'marketing', 'functional').
 * @return bool
 */
function hello_elementor_child_has_consent( string $category ): bool {
    $cookie_name = 'cookieyes-' . $category;

    if ( isset( $_COOKIE[ $cookie_name ] ) ) {
        return $_COOKIE[ $cookie_name ] === 'yes';
    }

    return false;
}
