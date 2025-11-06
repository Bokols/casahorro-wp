<?php
/**
 * Filters Drawer
 *
 * Accessible filter drawer (ARIA dialog) with subcategories, price, brands,
 * dynamic attributes, and stock toggle. Includes removable summary chips.
 *
 * @package HelloElementorChild
 * @since 0.2.0
 */

declare(strict_types=1);

use function CasAhorro\PLP\get_child_categories;
use function CasAhorro\PLP\get_product_brands;
use function CasAhorro\PLP\get_dynamic_attributes;
use function CasAhorro\PLP\get_price_range;
use function CasAhorro\PLP\get_active_filters;

$current_category = is_product_category() ? get_queried_object() : null;
$parent_id = $current_category && $current_category->parent !== 0 ? $current_category->parent : ( $current_category ? $current_category->term_id : 0 );
$subcategories = $parent_id ? get_child_categories( $parent_id ) : [];
$brands = get_product_brands();
$dynamic_attributes = get_dynamic_attributes();
$price_range = get_price_range();
$active_filters = get_active_filters();

// Build filter summary chips
$summary_chips = [];

// Subcategory chips
if ( ! empty( $active_filters['subcategory'] ) ) {
	foreach ( $active_filters['subcategory'] as $term_id ) {
		$term = get_term( $term_id, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$summary_chips[] = [
				'label'  => $term->name,
				'param'  => 'subcategory',
				'value'  => $term_id,
				'remove_url' => add_query_arg( 'remove_filter', 'subcategory_' . $term_id ),
			];
		}
	}
}

// Brand chips
if ( ! empty( $active_filters['marca'] ) ) {
	foreach ( $active_filters['marca'] as $slug ) {
		$term = get_term_by( 'slug', $slug, 'pa_marca' ) ?: get_term_by( 'slug', $slug, 'pa_brand' );
		if ( $term && ! is_wp_error( $term ) ) {
			$summary_chips[] = [
				'label'  => $term->name,
				'param'  => 'marca',
				'value'  => $slug,
				'remove_url' => add_query_arg( 'remove_filter', 'marca_' . $slug ),
			];
		}
	}
}

// Price chip
if ( ! empty( $active_filters['min_price'] ) || ! empty( $active_filters['max_price'] ) ) {
	$price_label = '';
	if ( ! empty( $active_filters['min_price'] ) && ! empty( $active_filters['max_price'] ) ) {
		$price_label = sprintf(
			'%s - %s',
			hello_elementor_child_format_clp( $active_filters['min_price'] ),
			hello_elementor_child_format_clp( $active_filters['max_price'] )
		);
	} elseif ( ! empty( $active_filters['min_price'] ) ) {
		$price_label = sprintf( 'Desde %s', hello_elementor_child_format_clp( $active_filters['min_price'] ) );
	} else {
		$price_label = sprintf( 'Hasta %s', hello_elementor_child_format_clp( $active_filters['max_price'] ) );
	}

	$summary_chips[] = [
		'label'  => $price_label,
		'param'  => 'price',
		'value'  => 'range',
		'remove_url' => add_query_arg( 'remove_filter', 'price' ),
	];
}

// Stock chip
if ( ! empty( $active_filters['in_stock'] ) ) {
	$summary_chips[] = [
		'label'  => 'Disponible en stock',
		'param'  => 'in_stock',
		'value'  => '1',
		'remove_url' => add_query_arg( 'remove_filter', 'in_stock' ),
	];
}

// Dynamic attribute chips
foreach ( $dynamic_attributes as $attribute ) {
	$taxonomy = $attribute['taxonomy'];
	if ( ! empty( $active_filters[ $taxonomy ] ) ) {
		foreach ( $active_filters[ $taxonomy ] as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
			if ( $term && ! is_wp_error( $term ) ) {
				$summary_chips[] = [
					'label'  => $attribute['label'] . ': ' . $term->name,
					'param'  => $taxonomy,
					'value'  => $slug,
					'remove_url' => add_query_arg( 'remove_filter', $taxonomy . '_' . $slug ),
				];
			}
		}
	}
}

?>

<!-- Filter Summary Chips (outside drawer, below parent chips) -->
<?php if ( ! empty( $summary_chips ) ) : ?>
<div class="plp-filter-summary" aria-live="polite" aria-atomic="false">
	<div class="plp-filter-summary__container">
		<span class="plp-filter-summary__label">Filtros aplicados:</span>
		<div class="plp-filter-summary__chips">
			<?php foreach ( $summary_chips as $chip ) : ?>
				<button
					type="button"
					class="plp-summary-chip"
					data-filter-param="<?php echo esc_attr( $chip['param'] ); ?>"
					data-filter-value="<?php echo esc_attr( $chip['value'] ); ?>"
					aria-label="Eliminar filtro: <?php echo esc_attr( $chip['label'] ); ?>"
				>
					<span class="plp-summary-chip__label"><?php echo esc_html( $chip['label'] ); ?></span>
					<svg class="plp-summary-chip__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
						<path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>
			<?php endforeach; ?>

			<button type="button" class="plp-summary-chip plp-summary-chip--clear-all" data-filter-clear-all aria-label="Limpiar todos los filtros">
				<span class="plp-summary-chip__label">Limpiar todos</span>
			</button>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Filter Drawer (ARIA Dialog) -->
<aside
	id="plp-filters-drawer"
	class="plp-filters-drawer"
	role="dialog"
	aria-modal="true"
	aria-labelledby="plp-filters-title"
	aria-hidden="true"
	hidden
>
	<div class="plp-filters-drawer__backdrop" data-drawer-close></div>

	<div class="plp-filters-drawer__panel">
		<!-- Header -->
		<header class="plp-filters-drawer__header">
			<h2 id="plp-filters-title" class="plp-filters-drawer__title">Filtros</h2>
			<button
				type="button"
				class="plp-filters-drawer__close"
				data-drawer-close
				aria-label="Cerrar filtros"
			>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
					<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
			</button>
		</header>

		<!-- Filter Form -->
		<form
			id="plp-filters-form"
			class="plp-filters-drawer__content"
			method="get"
			action="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
		>
			<!-- Preserve current category if on category page -->
			<?php if ( $current_category ) : ?>
				<input type="hidden" name="product_cat" value="<?php echo esc_attr( $current_category->slug ); ?>">
			<?php endif; ?>

			<!-- Subcategories Section -->
			<?php if ( ! empty( $subcategories ) ) : ?>
			<fieldset class="plp-filter-section">
				<legend class="plp-filter-section__title">Subcategorías</legend>
				<div class="plp-filter-section__content">
					<?php foreach ( $subcategories as $subcategory ) : ?>
						<?php
						$is_checked = ! empty( $active_filters['subcategory'] ) && in_array( $subcategory->term_id, $active_filters['subcategory'], true );
						?>
						<label class="plp-filter-checkbox">
							<input
								type="checkbox"
								name="subcategory[]"
								value="<?php echo esc_attr( $subcategory->term_id ); ?>"
								<?php checked( $is_checked ); ?>
							>
							<span class="plp-filter-checkbox__label">
								<?php echo esc_html( $subcategory->name ); ?>
								<span class="plp-filter-checkbox__count">(<?php echo esc_html( $subcategory->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<?php endif; ?>

			<!-- Price Section -->
			<fieldset class="plp-filter-section">
				<legend class="plp-filter-section__title">Precio (CLP)</legend>
				<div class="plp-filter-section__content">
					<!-- Price Slider -->
					<div class="plp-price-slider">
						<div class="plp-price-slider__track">
							<div class="plp-price-slider__range" data-slider-range></div>
							<div
								class="plp-price-slider__thumb"
								data-slider-thumb="min"
								role="slider"
								aria-label="Precio mínimo"
								aria-valuemin="<?php echo esc_attr( $price_range['min'] ); ?>"
								aria-valuemax="<?php echo esc_attr( $price_range['max'] ); ?>"
								aria-valuenow="<?php echo esc_attr( $active_filters['min_price'] ?? $price_range['min'] ); ?>"
								tabindex="0"
							></div>
							<div
								class="plp-price-slider__thumb"
								data-slider-thumb="max"
								role="slider"
								aria-label="Precio máximo"
								aria-valuemin="<?php echo esc_attr( $price_range['min'] ); ?>"
								aria-valuemax="<?php echo esc_attr( $price_range['max'] ); ?>"
								aria-valuenow="<?php echo esc_attr( $active_filters['max_price'] ?? $price_range['max'] ); ?>"
								tabindex="0"
							></div>
						</div>
						
						<!-- Price Range Display -->
						<div class="plp-price-range">
							<span class="plp-price-range__value" data-price-display="min">
								<?php echo esc_html( hello_elementor_child_format_clp( $active_filters['min_price'] ?? $price_range['min'] ) ); ?>
							</span>
							<span class="plp-price-range__separator">-</span>
							<span class="plp-price-range__value" data-price-display="max">
								<?php echo esc_html( hello_elementor_child_format_clp( $active_filters['max_price'] ?? $price_range['max'] ) ); ?>
							</span>
						</div>
					</div>
					
					<!-- Hidden inputs for form submission -->
					<input
						type="hidden"
						name="min_price"
						data-price-input="min"
						value="<?php echo esc_attr( $active_filters['min_price'] ?? '' ); ?>"
					>
					<input
						type="hidden"
						name="max_price"
						data-price-input="max"
						value="<?php echo esc_attr( $active_filters['max_price'] ?? '' ); ?>"
					>
				</div>
			</fieldset>

			<!-- Brands Section -->
			<?php if ( ! empty( $brands ) ) : ?>
			<fieldset class="plp-filter-section plp-filter-section--collapsible">
				<legend class="plp-filter-section__title">
					<button
						type="button"
						class="plp-filter-section__toggle"
						aria-expanded="true"
						data-filter-toggle="brands"
					>
						<span>Marcas</span>
						<svg class="plp-filter-section__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</legend>
				<div class="plp-filter-section__content" data-filter-content="brands">
					<?php foreach ( $brands as $brand ) : ?>
						<?php
						$is_checked = ! empty( $active_filters['marca'] ) && in_array( $brand->slug, $active_filters['marca'], true );
						?>
						<label class="plp-filter-checkbox">
							<input
								type="checkbox"
								name="marca[]"
								value="<?php echo esc_attr( $brand->slug ); ?>"
								<?php checked( $is_checked ); ?>
							>
							<span class="plp-filter-checkbox__label">
								<?php echo esc_html( $brand->name ); ?>
								<span class="plp-filter-checkbox__count" data-count-for="marca-<?php echo esc_attr( $brand->slug ); ?>">(<?php echo esc_html( $brand->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<?php endif; ?>

			<!-- Dynamic Attributes -->
			<?php foreach ( $dynamic_attributes as $attribute ) : ?>
			<fieldset class="plp-filter-section plp-filter-section--collapsible">
				<legend class="plp-filter-section__title">
					<button
						type="button"
						class="plp-filter-section__toggle"
						aria-expanded="false"
						data-filter-toggle="<?php echo esc_attr( $attribute['taxonomy'] ); ?>"
					>
						<span><?php echo esc_html( $attribute['label'] ); ?></span>
						<svg class="plp-filter-section__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</legend>
				<div class="plp-filter-section__content" data-filter-content="<?php echo esc_attr( $attribute['taxonomy'] ); ?>" hidden>
					<?php foreach ( $attribute['terms'] as $term ) : ?>
						<?php
						$is_checked = ! empty( $active_filters[ $attribute['taxonomy'] ] ) && in_array( $term->slug, $active_filters[ $attribute['taxonomy'] ], true );
						?>
						<label class="plp-filter-checkbox">
							<input
								type="checkbox"
								name="<?php echo esc_attr( $attribute['taxonomy'] ); ?>[]"
								value="<?php echo esc_attr( $term->slug ); ?>"
								<?php checked( $is_checked ); ?>
							>
							<span class="plp-filter-checkbox__label">
								<?php echo esc_html( $term->name ); ?>
								<span class="plp-filter-checkbox__count" data-count-for="<?php echo esc_attr( $attribute['taxonomy'] . '-' . $term->slug ); ?>">(<?php echo esc_html( $term->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<?php endforeach; ?>

			<!-- Stock Toggle -->
			<fieldset class="plp-filter-section plp-filter-section--collapsible">
				<legend class="plp-filter-section__title">
					<button
						type="button"
						class="plp-filter-section__toggle"
						aria-expanded="false"
						data-filter-toggle="stock"
					>
						<span>Disponibilidad</span>
						<svg class="plp-filter-section__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
							<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</legend>
				<div class="plp-filter-section__content" data-filter-content="stock" hidden>
					<label class="plp-filter-toggle">
						<input
							type="checkbox"
							name="in_stock"
							value="1"
							<?php checked( ! empty( $active_filters['in_stock'] ) ); ?>
						>
						<span class="plp-filter-toggle__slider"></span>
						<span class="plp-filter-toggle__label">Disponible en stock</span>
					</label>
				</div>
			</fieldset>
		</form>

		<!-- Footer Actions -->
		<footer class="plp-filters-drawer__footer">
			<button
				type="button"
				class="plp-filters-drawer__button plp-filters-drawer__button--secondary"
				data-filter-clear-all
				aria-label="Limpiar todos los filtros"
			>
				Limpiar filtros
			</button>
			<button
				type="submit"
				form="plp-filters-form"
				class="plp-filters-drawer__button plp-filters-drawer__button--primary"
			>
				Aplicar filtros
			</button>
		</footer>
	</div>
</aside>
