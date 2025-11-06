<?php
/**
 * PLP Query Modifications
 *
 * Handles WooCommerce archive customizations, attribute taxonomy registration,
 * sorting options, and products per page enforcement.
 *
 * @package HelloElementorChild
 * @since 0.2.0
 */

declare(strict_types=1);

namespace CasAhorro\PLP;

/**
 * Register product attribute taxonomies for filtering
 */
function register_attribute_taxonomies(): void {
	// Marca (preferred brand taxonomy)
	if ( ! taxonomy_exists( 'pa_marca' ) ) {
		register_taxonomy(
			'pa_marca',
			'product',
			[
				'label'        => 'Marca',
				'hierarchical' => false,
				'public'       => true,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => [ 'slug' => 'marca' ],
			]
		);
	}

	// Brand (fallback taxonomy)
	if ( ! taxonomy_exists( 'pa_brand' ) ) {
		register_taxonomy(
			'pa_brand',
			'product',
			[
				'label'        => 'Brand',
				'hierarchical' => false,
				'public'       => true,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => [ 'slug' => 'brand' ],
			]
		);
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_attribute_taxonomies', 0 );

/**
 * Enforce 24 products per page on archives
 *
 * @param WP_Query $query The WordPress query object.
 */
function enforce_products_per_page( $query ): void {
	if ( ! is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_product_taxonomy() ) ) {
		$query->set( 'posts_per_page', 24 );
	}
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\enforce_products_per_page' );

/**
 * Apply custom sorting to product archives
 *
 * @param WP_Query $query The WordPress query object.
 */
function apply_custom_sorting( $query ): void {
	if ( ! is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_product_taxonomy() ) ) {
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'relevance';

		switch ( $orderby ) {
			case 'price_asc':
				$query->set( 'meta_key', '_price' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'ASC' );
				break;

			case 'price_desc':
				$query->set( 'meta_key', '_price' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );
				break;

			case 'date_desc':
				$query->set( 'orderby', 'date' );
				$query->set( 'order', 'DESC' );
				break;

			case 'relevance':
			default:
				// Default WooCommerce relevance (menu_order + title)
				$query->set( 'orderby', 'menu_order title' );
				$query->set( 'order', 'ASC' );
				break;
		}
	}
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\apply_custom_sorting', 20 );

/**
 * Apply filters from query parameters
 *
 * @param WP_Query $query The WordPress query object.
 */
function apply_filters_from_params( $query ): void {
	if ( ! is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_product_taxonomy() ) ) {
		$tax_query = $query->get( 'tax_query' ) ?: [];
		$meta_query = $query->get( 'meta_query' ) ?: [];

		// Subcategory filter
		if ( ! empty( $_GET['subcategory'] ) ) {
			$subcategories = array_map( 'intval', (array) $_GET['subcategory'] );
			$tax_query[] = [
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $subcategories,
				'operator' => 'IN',
			];
		}

		// Brand filter (prefer pa_marca, fallback to pa_brand)
		if ( ! empty( $_GET['marca'] ) ) {
			$brands = array_map( 'sanitize_text_field', (array) $_GET['marca'] );
			$tax_query[] = [
				'relation' => 'OR',
				[
					'taxonomy' => 'pa_marca',
					'field'    => 'slug',
					'terms'    => $brands,
				],
				[
					'taxonomy' => 'pa_brand',
					'field'    => 'slug',
					'terms'    => $brands,
				],
			];
		}

		// Dynamic attribute filters (pa_*)
		foreach ( $_GET as $key => $value ) {
			if ( strpos( $key, 'pa_' ) === 0 && $key !== 'pa_marca' && $key !== 'pa_brand' && ! empty( $value ) ) {
				$terms = array_map( 'sanitize_text_field', (array) $value );
				$tax_query[] = [
					'taxonomy' => sanitize_key( $key ),
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => 'IN',
				];
			}
		}

		// Price range filter
		if ( ! empty( $_GET['min_price'] ) || ! empty( $_GET['max_price'] ) ) {
			$price_filter = [ 'key' => '_price', 'type' => 'NUMERIC' ];

			if ( ! empty( $_GET['min_price'] ) ) {
				$price_filter['value'] = [];
				$price_filter['value'][] = (int) $_GET['min_price'];
				$price_filter['compare'] = '>=';
			}

			if ( ! empty( $_GET['max_price'] ) ) {
				if ( isset( $price_filter['value'] ) ) {
					$price_filter['value'][] = (int) $_GET['max_price'];
					$price_filter['compare'] = 'BETWEEN';
				} else {
					$price_filter['value'] = (int) $_GET['max_price'];
					$price_filter['compare'] = '<=';
				}
			}

			$meta_query[] = $price_filter;
		}

		// Stock filter
		if ( isset( $_GET['in_stock'] ) && $_GET['in_stock'] === '1' ) {
			$meta_query[] = [
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			];
		}

		// Apply queries
		if ( ! empty( $tax_query ) ) {
			$tax_query['relation'] = 'AND';
			$query->set( 'tax_query', $tax_query );
		}

		if ( ! empty( $meta_query ) ) {
			$meta_query['relation'] = 'AND';
			$query->set( 'meta_query', $meta_query );
		}
	}
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\apply_filters_from_params', 30 );

/**
 * Get parent product categories
 *
 * Excludes "Uncategorized" category from chips.
 *
 * @return array Array of parent category objects.
 */
function get_parent_categories(): array {
	$categories = get_terms( [
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	if ( is_wp_error( $categories ) || empty( $categories ) ) {
		return [];
	}

	// Exclude "Uncategorized" by slug or name
	return array_filter( $categories, function( $category ) {
		return ! in_array( $category->slug, [ 'uncategorized', 'sin-categoria' ], true )
			&& ! in_array( strtolower( $category->name ), [ 'uncategorized', 'sin categoría' ], true );
	} );
}

/**
 * Get child categories for a parent category
 *
 * @param int $parent_id Parent category ID.
 * @return array Array of child category objects.
 */
function get_child_categories( int $parent_id ): array {
	return get_terms( [
		'taxonomy'   => 'product_cat',
		'parent'     => $parent_id,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );
}

/**
 * Get all product brands (prefer pa_marca, fallback pa_brand)
 *
 * @return array Array of brand term objects.
 */
function get_product_brands(): array {
	$marcas = get_terms( [
		'taxonomy'   => 'pa_marca',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	if ( ! empty( $marcas ) && ! is_wp_error( $marcas ) ) {
		return $marcas;
	}

	$brands = get_terms( [
		'taxonomy'   => 'pa_brand',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	] );

	return ! is_wp_error( $brands ) ? $brands : [];
}

/**
 * Get dynamic product attributes (excluding brand taxonomies)
 *
 * @return array Array of attribute taxonomy objects.
 */
function get_dynamic_attributes(): array {
	global $wpdb;

	$attributes = $wpdb->get_results(
		"SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies ORDER BY attribute_name ASC"
	);

	if ( empty( $attributes ) ) {
		return [];
	}

	$dynamic_attrs = [];

	foreach ( $attributes as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );

		// Skip brand taxonomies
		if ( $taxonomy === 'pa_marca' || $taxonomy === 'pa_brand' ) {
			continue;
		}

		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$dynamic_attrs[] = [
				'label'    => $attribute->attribute_label,
				'taxonomy' => $taxonomy,
				'terms'    => $terms,
			];
		}
	}

	return $dynamic_attrs;
}

/**
 * Get price range for current query
 *
 * @return array Array with 'min' and 'max' keys.
 */
function get_price_range(): array {
	global $wpdb;

	$sql = "SELECT MIN(CAST(meta_value AS UNSIGNED)) as min, MAX(CAST(meta_value AS UNSIGNED)) as max 
	        FROM {$wpdb->postmeta} 
	        WHERE meta_key = '_price' 
	        AND meta_value != ''";

	$results = $wpdb->get_row( $sql );

	return [
		'min' => $results->min ? (int) $results->min : 0,
		'max' => $results->max ? (int) $results->max : 1000000,
	];
}

/**
 * Get current filter state from URL parameters
 *
 * @return array Associative array of active filters.
 */
function get_active_filters(): array {
	$filters = [];

	// Subcategories
	if ( ! empty( $_GET['subcategory'] ) ) {
		$filters['subcategory'] = array_map( 'intval', (array) $_GET['subcategory'] );
	}

	// Brands
	if ( ! empty( $_GET['marca'] ) ) {
		$filters['marca'] = array_map( 'sanitize_text_field', (array) $_GET['marca'] );
	}

	// Price
	if ( ! empty( $_GET['min_price'] ) ) {
		$filters['min_price'] = (int) $_GET['min_price'];
	}
	if ( ! empty( $_GET['max_price'] ) ) {
		$filters['max_price'] = (int) $_GET['max_price'];
	}

	// Stock
	if ( isset( $_GET['in_stock'] ) && $_GET['in_stock'] === '1' ) {
		$filters['in_stock'] = true;
	}

	// Dynamic attributes
	foreach ( $_GET as $key => $value ) {
		if ( strpos( $key, 'pa_' ) === 0 && $key !== 'pa_marca' && $key !== 'pa_brand' && ! empty( $value ) ) {
			$filters[ $key ] = array_map( 'sanitize_text_field', (array) $value );
		}
	}

	return $filters;
}
