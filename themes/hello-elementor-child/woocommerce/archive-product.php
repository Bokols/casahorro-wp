<?php
/**
 * Archive Product Template (PLP)
 *
 * Overrides WooCommerce default product archive with custom layout including
 * parent chips, filter drawer, sort controls, and product grid.
 *
 * @package HelloElementorChild
 * @since 0.2.0
 */

declare(strict_types=1);

get_header();

$current_category = is_product_category() ? get_queried_object() : null;
$page_title = $current_category ? $current_category->name : 'Productos';
$page_description = $current_category && ! empty( $current_category->description ) ? $current_category->description : '';
$result_count = wc_get_loop_prop( 'total' );

?>

<main id="main" class="site-main plp-main">
	<!-- Hero Section -->
	<section class="plp-hero">
		<div class="plp-hero__container">
			<h1 class="plp-hero__title"><?php echo esc_html( $page_title ); ?></h1>
			<?php if ( $page_description ) : ?>
				<div class="plp-hero__description">
					<?php echo wp_kses_post( wpautop( $page_description ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<!-- Parent Category Chips -->
	<?php get_template_part( 'partials/plp/parent-chips' ); ?>

	<!-- Filter Summary & Drawer -->
	<?php get_template_part( 'partials/plp/filters-drawer' ); ?>

	<!-- Toolbar (Filter Button + Sort + Result Count) -->
	<div class="plp-toolbar">
		<div class="plp-toolbar__container">
			<!-- Filter Button (opens drawer) -->
			<button
				type="button"
				class="plp-toolbar__filter-button"
				data-drawer-open
				aria-label="Abrir filtros"
				aria-expanded="false"
				aria-controls="plp-filters-drawer"
			>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
					<path d="M2 4h16M6 10h8M9 16h2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span>Filtros</span>
			</button>

			<!-- Result Count -->
			<div class="plp-toolbar__count" aria-live="polite">
				<?php if ( $result_count > 0 ) : ?>
					<span><?php echo esc_html( sprintf( _n( '%s producto', '%s productos', $result_count, 'hello-elementor-child' ), number_format_i18n( $result_count ) ) ); ?></span>
				<?php else : ?>
					<span>No se encontraron productos</span>
				<?php endif; ?>
			</div>

			<!-- Sort Dropdown -->
			<div class="plp-toolbar__sort">
				<label for="plp-sort" class="plp-toolbar__sort-label">Ordenar por:</label>
				<select
					id="plp-sort"
					class="plp-toolbar__sort-select"
					data-sort-control
				>
					<?php
					$current_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'relevance';
					$sort_options = [
						'relevance'  => 'Relevancia',
						'price_asc'  => 'Precio: Menor a mayor',
						'price_desc' => 'Precio: Mayor a menor',
						'date_desc'  => 'Novedades',
					];
					?>
					<?php foreach ( $sort_options as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_orderby, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<!-- Product Grid -->
	<?php if ( woocommerce_product_loop() ) : ?>
		<div class="plp-grid">
			<div class="plp-grid__container">
				<?php
				woocommerce_product_loop_start();

				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();

						/**
						 * Hook: woocommerce_shop_loop.
						 */
						do_action( 'woocommerce_shop_loop' );

						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();
				?>
			</div>
		</div>

		<!-- Pagination -->
		<nav class="plp-pagination" aria-label="Paginación de productos">
			<?php
			echo paginate_links( [
				'base'      => esc_url_raw( add_query_arg( 'paged', '%#%', false ) ),
				'format'    => '?paged=%#%',
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'total'     => wc_get_loop_prop( 'total_pages' ),
				'prev_text' => '← Anterior',
				'next_text' => 'Siguiente →',
				'type'      => 'list',
				'end_size'  => 1,
				'mid_size'  => 2,
			] );
			?>
		</nav>

	<?php else : ?>
		<!-- Empty State -->
		<div class="plp-empty">
			<div class="plp-empty__container">
				<svg class="plp-empty__icon" width="64" height="64" viewBox="0 0 64 64" fill="none" aria-hidden="true">
					<circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="2" opacity="0.2"/>
					<path d="M28 28L36 36M36 28L28 36" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<h2 class="plp-empty__title">No se encontraron productos</h2>
				<p class="plp-empty__description">
					Intenta ajustar los filtros o realiza una nueva búsqueda.
				</p>
				<?php if ( ! empty( \CasAhorro\PLP\get_active_filters() ) ) : ?>
					<button
						type="button"
						class="plp-empty__button"
						data-filter-clear-all
					>
						Limpiar todos los filtros
					</button>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
