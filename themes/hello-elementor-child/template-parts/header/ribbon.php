<?php
/**
 * Ribbon partial - Phase 2
 *
 * Outputs a straight 22.09px decorative band with 5 pastel colors
 * (mint, coral, lavender, sage, cream) left to right.
 * 
 * Hidden on mobile (<=640px) via CSS.
 * Purely decorative, no textual content.
 *
 * @package Hello_Elementor_Child
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div id="cas-ribbon" class="cas-ribbon" aria-hidden="true" role="presentation">
    <div class="cas-ribbon-inner"></div>
</div>
