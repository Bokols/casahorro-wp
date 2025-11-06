<?php
/**
 * Search Results Page (SRP)
 *
 * Displays search results with product cards matching PLP anatomy.
 * Consent-gated analytics, Chilean peso formatting, Spanish (Chile) copy.
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

// Get search query
$search_query = get_search_query();
$total_results = (int) $wp_query->found_posts;

?>

<main id="content" class="site-main search-results" role="main">
    <div class="search-header">
        <div class="search-header-inner">
            <h1 class="search-title">
                <?php
                if ( $search_query ) {
                    printf(
                        /* translators: %s: search query */
                        esc_html__( 'Resultados de búsqueda para "%s"', 'hello-elementor-child' ),
                        '<span class="search-query">' . esc_html( $search_query ) . '</span>'
                    );
                } else {
                    esc_html_e( 'Resultados de búsqueda', 'hello-elementor-child' );
                }
                ?>
            </h1>
            
            <?php if ( $total_results > 0 ) : ?>
                <p class="search-count">
                    <?php
                    printf(
                        /* translators: %d: number of results */
                        esc_html( _n( '%d resultado encontrado', '%d resultados encontrados', $total_results, 'hello-elementor-child' ) ),
                        number_format_i18n( $total_results )
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="search-content">
        <?php if ( have_posts() ) : ?>
            <div class="search-results-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    
                    // Render product card for WooCommerce products
                    if ( get_post_type() === 'product' && function_exists( 'wc_get_product' ) ) {
                        get_template_part( 'template-parts/search/card-product' );
                    } else {
                        // Fallback for posts/pages
                        get_template_part( 'template-parts/search/card-post' );
                    }
                endwhile;
                ?>
            </div>

            <?php
            // Pagination
            the_posts_pagination(
                array(
                    'mid_size'           => 2,
                    'prev_text'          => __( '← Anterior', 'hello-elementor-child' ),
                    'next_text'          => __( 'Siguiente →', 'hello-elementor-child' ),
                    'screen_reader_text' => __( 'Navegación de resultados', 'hello-elementor-child' ),
                )
            );
            ?>

        <?php else : ?>
            <!-- Empty State (es-CL) -->
            <div class="search-no-results">
                <div class="no-results-content">
                    <svg class="no-results-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M8 11h6M11 8v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    
                    <h2 class="no-results-title">
                        <?php esc_html_e( 'No encontramos resultados', 'hello-elementor-child' ); ?>
                    </h2>
                    
                    <p class="no-results-message">
                        <?php
                        if ( $search_query ) {
                            printf(
                                /* translators: %s: search query */
                                esc_html__( 'No encontramos productos o páginas que coincidan con "%s".', 'hello-elementor-child' ),
                                '<strong>' . esc_html( $search_query ) . '</strong>'
                            );
                        } else {
                            esc_html_e( 'Por favor, intenta con otros términos de búsqueda.', 'hello-elementor-child' );
                        }
                        ?>
                    </p>
                    
                    <div class="no-results-suggestions">
                        <h3><?php esc_html_e( 'Sugerencias:', 'hello-elementor-child' ); ?></h3>
                        <ul>
                            <li><?php esc_html_e( 'Verifica la ortografía de las palabras', 'hello-elementor-child' ); ?></li>
                            <li><?php esc_html_e( 'Prueba con palabras clave diferentes', 'hello-elementor-child' ); ?></li>
                            <li><?php esc_html_e( 'Usa términos más generales', 'hello-elementor-child' ); ?></li>
                            <li><?php esc_html_e( 'Intenta con sinónimos', 'hello-elementor-child' ); ?></li>
                        </ul>
                    </div>
                    
                    <!-- Search form -->
                    <div class="no-results-search">
                        <?php get_search_form(); ?>
                    </div>
                    
                    <!-- Back to home link -->
                    <p class="no-results-home">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button button-secondary">
                            <?php esc_html_e( 'Volver al inicio', 'hello-elementor-child' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
