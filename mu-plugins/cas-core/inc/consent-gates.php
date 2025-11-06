<?php
/**
 * Consent gates API (Phase 0)
 *
 * Provides cas_consent_allowed( $category ) to check consent for categories.
 * Phase 0 returns false for everything; later versions will integrate with
 * the site's consent manager.
 *
 * PHP version 8.2
 *
 * @package cas-core
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'cas_consent_allowed' ) ) {
    /**
     * Return whether consent is allowed for a category.
     *
     * @param string $category Consent category slug.
     * @return bool False for all categories in Phase 0.
     */
    function cas_consent_allowed( string $category ): bool {
        // Phase 0: deny by default. Replace this with real checks later.
        return false;
    }
}
