<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * C1 Design System:
 * - 4:3 aspect ratio image with reserved space (no CLS)
 * - Neutral placeholder (no neon backgrounds)
 * - CLP price formatting with neutral ink color
 * - Title limited to 2-3 lines with ellipsis
 * - Link hover = underline only
 * - Focus-visible outline (3px)
 * - Affiliate disclosure below "Ver oferta" if present
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// Check if this is an affiliate product (has external URL)
$is_affiliate = $product->is_type( 'external' );
$button_text = $is_affiliate && method_exists( $product, 'get_button_text' ) ? $product->get_button_text() : 'Ver oferta';
?>

<li <?php wc_product_class( 'product-card', $product ); ?>>
	<div class="product-card__inner">
		
		<!-- Product Image (4:3 aspect ratio) -->
		<div class="product-card__image">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="product-card__image-link" aria-label="<?php echo esc_attr( sprintf( 'Ver detalles de %s', $product->get_name() ) ); ?>">
				<?php
				/**
				 * Hook: woocommerce_before_shop_loop_item_title.
				 *
				 * @hooked woocommerce_show_product_loop_sale_flash - 10
				 * @hooked woocommerce_template_loop_product_thumbnail - 10
				 */
				if ( has_post_thumbnail() ) {
					// Get current product position in loop (0-indexed)
					global $wp_query;
					$current_position = $wp_query->current_post;
					
					// First 4 products load eagerly (above fold on desktop), rest lazy load
					$loading_attr = $current_position < 4 ? 'eager' : 'lazy';
					
					the_post_thumbnail( 'woocommerce_thumbnail', [
						'alt'     => esc_attr( $product->get_name() ),
						'class'   => 'product-card__thumbnail',
						'loading' => $loading_attr, // Lazy load images below fold
						'decoding' => 'async', // Decode async to avoid blocking
					] );
				} else {
					// Neutral placeholder (no neon backgrounds)
					echo '<div class="product-card__placeholder" role="img" aria-label="' . esc_attr__( 'Imagen no disponible', 'woocommerce' ) . '">';
					echo '<svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">';
					echo '<rect width="80" height="80" fill="#F8FAFC"/>';
					echo '<path d="M35 32.5L25 45L32.5 52.5L42.5 40L52.5 52.5H27.5L35 32.5Z" fill="#CBD5E1"/>';
					echo '<circle cx="47.5" cy="35" r="5" fill="#CBD5E1"/>';
					echo '</svg>';
					echo '</div>';
				}

				// Sale badge
				if ( $product->is_on_sale() ) {
					echo '<span class="product-card__badge product-card__badge--sale">¡Oferta!</span>';
				}
				?>
			</a>
		</div>

		<!-- Product Content -->
		<div class="product-card__content">
			
			<!-- Product Title (2-3 lines max) -->
			<h2 class="product-card__title">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="product-card__title-link">
					<?php echo wp_kses_post( $product->get_name() ); ?>
				</a>
			</h2>

			<!-- Product Price (CLP formatting with neutral ink) -->
			<div class="product-card__price">
				<?php
				/**
				 * Hook: woocommerce_after_shop_loop_item_title.
				 *
				 * @hooked woocommerce_template_loop_rating - 5
				 * @hooked woocommerce_template_loop_price - 10
				 */
				if ( $price_html = $product->get_price_html() ) {
					// Price already formatted via CLP filters in functions.php
					echo wp_kses_post( $price_html );
				}
				?>
			</div>

			<!-- Add to Cart / View Offer Button -->
			<?php
			/**
			 * Hook: woocommerce_after_shop_loop_item.
			 *
			 * @hooked woocommerce_template_loop_product_link_close - 5
			 * @hooked woocommerce_template_loop_add_to_cart - 10
			 */
			if ( $is_affiliate ) {
				// External/Affiliate product
				?>
				<div class="product-card__actions">
					<a 
						href="<?php echo esc_url( $product->get_product_url() ); ?>" 
						rel="nofollow noopener noreferrer" 
						target="_blank"
						class="button product-card__button product-card__button--external"
						aria-label="<?php echo esc_attr( sprintf( '%s - Abre en nueva pestaña', $button_text ) ); ?>"
					>
						<?php echo esc_html( $button_text ); ?>
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M6 3H3V13H13V10M9 3H13M13 3V7M13 3L6 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
					
					<!-- Affiliate Disclosure -->
					<p class="product-card__disclosure">
						<small>
							Enlace de afiliado — 
							<a href="/como-trabajamos" class="product-card__disclosure-link">Cómo trabajamos</a>
						</small>
					</p>
				</div>
				<?php
			} else {
				// Regular product
				do_action( 'woocommerce_after_shop_loop_item' );
			}
			?>
		</div>
	</div>
</li>
