<?php
/**
 * Breadcrumbs Template Part
 * casAhorro Design System
 * 
 * Displays hierarchical breadcrumb navigation with:
 * - Home > Category > Subcategory > Current Page
 * - ARIA navigation landmark
 * - Structured data support (JSON-LD)
 * - C1 compliant styling
 * 
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't show breadcrumbs on homepage
if ( is_front_page() ) {
	return;
}

// Build breadcrumb trail
$breadcrumbs = [];

// 1. Home (always first)
$breadcrumbs[] = [
	'label' => 'Inicio',
	'url'   => home_url( '/' ),
	'current' => false,
];

// 2. Shop/Products page (if on product archive or single product)
if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
	if ( ! is_shop() ) {
		$breadcrumbs[] = [
			'label' => 'Productos',
			'url'   => get_permalink( wc_get_page_id( 'shop' ) ),
			'current' => false,
		];
	}
}

// 3. Category (if on category page or single product in category)
if ( is_product_category() ) {
	$current_cat = get_queried_object();
	
	// Parent categories (if any)
	if ( $current_cat->parent ) {
		$parent_cats = [];
		$parent_id = $current_cat->parent;
		
		while ( $parent_id ) {
			$parent = get_term( $parent_id, 'product_cat' );
			if ( ! $parent || is_wp_error( $parent ) ) {
				break;
			}
			
			array_unshift( $parent_cats, [
				'label' => $parent->name,
				'url'   => get_term_link( $parent ),
				'current' => false,
			] );
			
			$parent_id = $parent->parent;
		}
		
		$breadcrumbs = array_merge( $breadcrumbs, $parent_cats );
	}
	
	// Current category
	$breadcrumbs[] = [
		'label' => $current_cat->name,
		'url'   => get_term_link( $current_cat ),
		'current' => true,
	];
	
} elseif ( is_product() ) {
	// Single product: get first category
	$product_cats = get_the_terms( get_the_ID(), 'product_cat' );
	
	if ( $product_cats && ! is_wp_error( $product_cats ) ) {
		$main_cat = $product_cats[0];
		
		// Parent categories
		if ( $main_cat->parent ) {
			$parent_cats = [];
			$parent_id = $main_cat->parent;
			
			while ( $parent_id ) {
				$parent = get_term( $parent_id, 'product_cat' );
				if ( ! $parent || is_wp_error( $parent ) ) {
					break;
				}
				
				array_unshift( $parent_cats, [
					'label' => $parent->name,
					'url'   => get_term_link( $parent ),
					'current' => false,
				] );
				
				$parent_id = $parent->parent;
			}
			
			$breadcrumbs = array_merge( $breadcrumbs, $parent_cats );
		}
		
		// Category link
		$breadcrumbs[] = [
			'label' => $main_cat->name,
			'url'   => get_term_link( $main_cat ),
			'current' => false,
		];
	}
	
	// Current product
	$breadcrumbs[] = [
		'label' => get_the_title(),
		'url'   => get_permalink(),
		'current' => true,
	];
	
} elseif ( is_page() ) {
	// Regular page
	$breadcrumbs[] = [
		'label' => get_the_title(),
		'url'   => get_permalink(),
		'current' => true,
	];
	
} elseif ( is_singular() ) {
	// Other singular content
	$breadcrumbs[] = [
		'label' => get_the_title(),
		'url'   => get_permalink(),
		'current' => true,
	];
}

// Don't render if only home breadcrumb exists
if ( count( $breadcrumbs ) <= 1 ) {
	return;
}
?>

<nav class="cas-breadcrumbs" aria-label="Ruta de navegación">
	<div class="cas-breadcrumbs__container">
		<ol class="cas-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
			<?php foreach ( $breadcrumbs as $index => $crumb ) : 
				$position = $index + 1;
			?>
				<li class="cas-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<?php if ( $crumb['current'] ) : ?>
						<span class="cas-breadcrumbs__current" aria-current="page" itemprop="name">
							<?php echo esc_html( $crumb['label'] ); ?>
						</span>
						<meta itemprop="position" content="<?php echo esc_attr( $position ); ?>">
					<?php else : ?>
						<a href="<?php echo esc_url( $crumb['url'] ); ?>" class="cas-breadcrumbs__link" itemprop="item">
							<?php if ( $index === 0 ) : ?>
								<span class="cas-breadcrumbs__home-icon" aria-hidden="true">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
										<path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd" />
									</svg>
								</span>
							<?php endif; ?>
							<span itemprop="name"><?php echo esc_html( $crumb['label'] ); ?></span>
						</a>
						<meta itemprop="position" content="<?php echo esc_attr( $position ); ?>">
						<span class="cas-breadcrumbs__separator" aria-hidden="true">/</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</nav>
