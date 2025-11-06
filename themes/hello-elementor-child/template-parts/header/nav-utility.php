<?php
/**
 * Utility navigation partial
 *
 * Renders the utility navigation menu if assigned. If not assigned, the
 * output is intentionally empty (optional utility menu).
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! has_nav_menu( 'utility' ) ) {
    // Optional: no output when utility menu is not set.
    return;
}

?>
<nav id="cas-nav-utility" class="nav-utility" role="navigation" aria-label="Utilidad">
    <?php
    wp_nav_menu( array(
        'theme_location' => 'utility',
        'container'      => false,
        'menu_class'     => 'menu menu-utility',
        'depth'          => 1,
    ) );
    ?>
</nav>
