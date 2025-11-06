<?php
/**
 * Block Patterns for casAhorro
 *
 * Registers custom block pattern categories and reusable patterns for
 * common content elements like affiliate disclosures.
 *
 * PHP version 8.2
 *
 * @package cas-core
 * @since 1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access.
}

/**
 * Register custom block pattern category
 *
 * Creates a "casahorro" category in the block inserter to organize
 * our custom patterns.
 */
add_action( 'init', function (): void {
    register_block_pattern_category(
        'casahorro',
        array(
            'label'       => __( 'casAhorro', 'cas-core' ),
            'description' => __( 'Patrones reutilizables para casAhorro', 'cas-core' ),
        )
    );
} );

/**
 * Register Affiliate Disclosure Pattern
 *
 * Provides a ready-to-insert affiliate disclosure paragraph in Spanish (Chile)
 * that explains potential commission earnings and links to the affiliate policy.
 */
add_action( 'init', function (): void {
    // Build the disclosure URL (internal link)
    $disclosure_url = esc_url( home_url( '/como-trabajamos-afiliados' ) );
    
    // Pattern content: single paragraph with strong label and link
    $content = sprintf(
        '<!-- wp:paragraph -->
<p><strong>Enlaces de afiliado:</strong> podríamos recibir una comisión, sin costo para ti. Más info en <a href="%s" rel="nofollow sponsored" target="_self">Cómo trabajamos con afiliados</a>.</p>
<!-- /wp:paragraph -->',
        $disclosure_url
    );
    
    register_block_pattern(
        'casahorro/disclosure-afiliados',
        array(
            'title'       => __( 'Disclosure Afiliados', 'cas-core' ),
            'description' => __( 'Aviso de enlaces de afiliado con enlace a política', 'cas-core' ),
            'content'     => $content,
            'categories'  => array( 'casahorro' ),
            'keywords'    => array( 'afiliado', 'disclosure', 'comisión', 'transparencia' ),
            'viewportWidth' => 800,
        )
    );
} );
