<?php
/**
 * Template Part: Product Card (Search Results)
 *
 * Renders a WooCommerce product card for search results.
 * Mirrors PLP card anatomy with Chilean peso formatting.
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$product = wc_get_product( get_the_ID() );

if ( ! $product ) {
    return;
}

?>

<article <?php wc_product_class( 'product-card search-result-product', $product ); ?> data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
    <div class="product-card-inner">
        
        <!-- Product Image -->
        <div class="product-card-image">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="product-image-link">
                <?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); ?>
            </a>
            
            <?php if ( $product->is_on_sale() ) : ?>
                <span class="product-badge sale-badge">
                    <?php esc_html_e( 'Oferta', 'hello-elementor-child' ); ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-card-content">
            
            <!-- Title -->
            <h3 class="product-card-title">
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                    <?php echo wp_kses_post( $product->get_name() ); ?>
                </a>
            </h3>

            <!-- Price (Chilean Peso Format) -->
            <div class="product-card-price">
                <?php
                if ( $product->is_on_sale() ) {
                    ?>
                    <span class="price-regular">
                        <?php echo wp_kses_post( hello_elementor_child_format_clp( $product->get_regular_price() ) ); ?>
                    </span>
                    <span class="price-sale">
                        <?php echo wp_kses_post( hello_elementor_child_format_clp( $product->get_sale_price() ) ); ?>
                    </span>
                    <?php
                } else {
                    ?>
                    <span class="price-current">
                        <?php echo wp_kses_post( hello_elementor_child_format_clp( $product->get_price() ) ); ?>
                    </span>
                    <?php
                }
                ?>
            </div>

            <!-- Short Description (if available) -->
            <?php if ( $product->get_short_description() ) : ?>
                <div class="product-card-excerpt">
                    <?php echo wp_kses_post( wp_trim_words( $product->get_short_description(), 15 ) ); ?>
                </div>
            <?php endif; ?>

            <!-- Add to Cart / View Product -->
            <div class="product-card-actions">
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="button button-primary product-view-link">
                    <?php esc_html_e( 'Ver producto', 'hello-elementor-child' ); ?>
                </a>
            </div>
        </div>
    </div>
</article>
