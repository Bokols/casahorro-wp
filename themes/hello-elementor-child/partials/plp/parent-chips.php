<?php
/**
 * Parent Category Chips
 *
 * Displays top-level product categories as navigational chips outside the filter drawer.
 * Excludes "Uncategorized". Uses <button> elements with aria-pressed for selected state.
 *
 * @package HelloElementorChild
 * @since 0.2.0
 */

declare(strict_types=1);

use function CasAhorro\PLP\get_parent_categories;

$parent_categories = get_parent_categories();
$current_category = is_product_category() ? get_queried_object() : null;
$shop_url = get_permalink( wc_get_page_id( 'shop' ) );

?>

<nav class="plp-parent-chips" aria-label="Categorías principales">
	<div class="plp-parent-chips__container">
		<!-- "Todos" chip - always present, links to /productos -->
		<button
			type="button"
			class="plp-parent-chip <?php echo ! is_product_category() ? 'plp-parent-chip--active' : ''; ?>"
			aria-pressed="<?php echo ! is_product_category() ? 'true' : 'false'; ?>"
			onclick="window.location.href='<?php echo esc_url( $shop_url ); ?>'"
		>
			<span class="plp-parent-chip__label">Todos</span>
		</button>

		<?php foreach ( $parent_categories as $category ) : ?>
			<?php
			$is_active = $current_category && (
				$current_category->term_id === $category->term_id ||
				( $current_category->parent !== 0 && $current_category->parent === $category->term_id )
			);
			$category_url = get_term_link( $category );
			?>

			<button
				type="button"
				class="plp-parent-chip <?php echo $is_active ? 'plp-parent-chip--active' : ''; ?>"
				aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>"
				onclick="window.location.href='<?php echo esc_url( $category_url ); ?>'"
			>
				<span class="plp-parent-chip__label"><?php echo esc_html( $category->name ); ?></span>
				<?php if ( $category->count > 0 ) : ?>
					<span class="plp-parent-chip__count" aria-label="<?php echo esc_attr( sprintf( '%d productos', $category->count ) ); ?>">
						<?php echo esc_html( $category->count ); ?>
					</span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>
</nav>
