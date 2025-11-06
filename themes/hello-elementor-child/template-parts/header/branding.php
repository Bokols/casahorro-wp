<?php
/**
 * Header branding partial
 *
 * Outputs the custom logo if present; otherwise the site name as a link.
 * Wrapped in a minimal wrapper for layout: <div class="header-branding">.
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="header-branding">
    <?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
        <?php echo get_custom_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php else : ?>
        <a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>
        </a>
    <?php endif; ?>
</div>
