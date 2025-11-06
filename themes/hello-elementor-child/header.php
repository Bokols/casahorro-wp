<?php
/**
 * Theme header (safe child version without ribbon)
 *
 * Reintroduces the custom child header with guards so it only renders when
 * Elementor isn't handling the header and when the theme hasn't disabled
 * header/footer for the current document. The ribbon is intentionally
 * omitted for now to isolate header alignment.
 *
 * @package Hello_Elementor_Child
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Determine header classes. Add a class when the admin toolbar is showing so
// CSS can compensate for the toolbar height when making the header sticky.
$header_classes = array( 'site-header', 'is-sticky' );
if ( function_exists( 'is_admin_bar_showing' ) && is_admin_bar_showing() ) {
    $header_classes[] = 'has-toolbar';
}

// Decide whether to render the child header block.
$render_child_header = true;
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
    // Elementor Theme Builder is providing the header.
    $render_child_header = false;
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="<?php echo esc_attr( apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' ) ); ?>">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ( apply_filters( 'hello_elementor_enable_skip_link', true ) ) : ?>
    <a class="skip-link screen-reader-text" href="<?php echo esc_url( apply_filters( 'hello_elementor_skip_link_url', '#content' ) ); ?>"><?php echo esc_html__( 'Skip to content', 'hello-elementor-child' ); ?></a>
<?php endif; ?>

<?php if ( $render_child_header ) : ?>
<div class="header-wrapper">
    <header id="cas-header" class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>" role="banner" aria-label="Encabezado">
        <div class="cas-header-inner">
          <div class="cas-container">

            <?php get_template_part( 'template-parts/header/branding' ); ?>

            <button 
                id="cas-menu-toggle" 
                class="menu-toggle" 
                type="button"
                aria-expanded="false" 
                aria-controls="cas-nav-primary"
                aria-label="<?php echo esc_attr__( 'Menú de navegación', 'hello-elementor-child' ); ?>"
            >
                <span><?php echo esc_html__( 'Menú', 'hello-elementor-child' ); ?></span>
            </button>

            <?php get_template_part( 'template-parts/header/nav-primary' ); ?>

            <?php get_template_part( 'template-parts/header/nav-utility' ); ?>

          </div>
        </div>
    </header>
</div>
<?php // Ribbon positioned below the sticky header wrapper to act as visual separator.
    // Hidden on small viewports via CSS (<=640px).
    get_template_part( 'template-parts/header/ribbon' ); ?>

<?php // Breadcrumbs positioned below header for SEO and navigation aid
    get_template_part( 'template-parts/breadcrumbs' ); ?>
<?php endif; ?>
