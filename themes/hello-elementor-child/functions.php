<?php
/**
 * Hello Elementor Child functions and definitions
 *
 * Enqueues parent and child styles, tokens CSS, and JS. Adds Elementor
 * compatibility flags and loads theme setup and accessibility helpers.
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Load CSS load order debugging utility (accessible via ?debug_css_order=1)
require_once get_stylesheet_directory() . '/inc/css-load-order-debug.php';

// Load asset scanner (WP-CLI + Admin page)
require_once get_stylesheet_directory() . '/inc/asset-scanner.php';

// Load image optimization scanner (WP-CLI + Admin page)
require_once get_stylesheet_directory() . '/inc/image-optimizer-scanner.php';

// Load assistant WP-CLI command
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once get_stylesheet_directory() . '/cli/cas-assistant-command.php';
}

// Load assistant health dashboard widget
require_once get_stylesheet_directory() . '/inc/assistant-health-widget.php';

// Load assistant admin bar toggle
require_once get_stylesheet_directory() . '/inc/assistant-admin-bar.php';

/**
 * Enqueue parent & child theme styles, tokens, components, and color guard.
 *
 * Load order (CSS):
 * 1. Parent theme stylesheet (Hello Elementor)
 * 2. Child theme base stylesheet (style.css)
 * 3. Design tokens (tokens.css)
 * 4. Component styles (header.css, plp.css, chips.css, etc.)
 * 5. Color guard (color-guard.css) - LAST, overrides Elementor/Woo defaults
 *
 * Priority 20 ensures we load after most plugins (default priority 10).
 * Color guard depends on Elementor/Woo handles when available to print last.
 */
function hello_elementor_child_enqueue_assets(): void {
    $theme_version = wp_get_theme()->get( 'Version' ) ?: '1.0.0';
    $base_uri = get_stylesheet_directory_uri();
    $base_dir = get_stylesheet_directory();

    // Helper function to get file version
    $get_version = function( $relative_path ) use ( $base_dir, $theme_version ) {
        $full_path = $base_dir . $relative_path;
        return file_exists( $full_path ) ? filemtime( $full_path ) : $theme_version;
    };

    // 1) Parent style (Hello Elementor)
    wp_enqueue_style( 
        'hello-elementor-style', 
        get_template_directory_uri() . '/style.css', 
        array(), 
        $theme_version 
    );

    // 2) Child base style (this theme)
    wp_enqueue_style( 
        'hello-elementor-child-style', 
        get_stylesheet_uri(), 
        array( 'hello-elementor-style' ), 
        $get_version( '/style.css' )
    );

    // 3) Design tokens (foundational CSS custom properties)
    wp_enqueue_style(
        'hello-elementor-child-tokens',
        $base_uri . '/assets/tokens.css',
        array( 'hello-elementor-child-style' ),
        $get_version( '/assets/tokens.css' )
    );

    // Home Hero - Device-specific backgrounds (conditional on homepage)
    // Desktop/tablet/mobile responsive backgrounds with performance budgets
    // Load after tokens, before Elementor page CSS
    if ( is_front_page() || is_page( 'inicio' ) ) {
        wp_enqueue_style(
            'hello-elementor-child-home-hero',
            $base_uri . '/assets/css/home-hero.css',
            array( 'hello-elementor-child-tokens' ),
            $get_version( '/assets/css/home-hero.css' )
        );
    }

    // 4) Component styles
    // Header styles (navigation, ribbon, z-index)
    wp_enqueue_style(
        'hello-elementor-child-header',
        $base_uri . '/assets/css/header.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/header.css' )
    );

    // Header Sticky - Opaque background enforcement (AFTER base header styles)
    // Prevents transparency flicker on hover/focus/scroll
    wp_enqueue_style(
        'hello-elementor-child-header-sticky',
        $base_uri . '/assets/css/header-sticky.css',
        array( 'hello-elementor-child-header' ),
        $get_version( '/assets/css/header-sticky.css' )
    );

    // Nav Drawer - Mobile/Tablet Opaque Background (AFTER header styles)
    // Locks mobile menu drawer to solid background when open
    // Prevents transparency flicker on hover/focus, ensures overlay dims page
    wp_enqueue_style(
        'hello-elementor-child-nav-drawer',
        $base_uri . '/assets/css/nav-drawer.css',
        array( 'hello-elementor-child-header-sticky' ),
        $get_version( '/assets/css/nav-drawer.css' )
    );

    // Component: Buttons
    wp_enqueue_style(
        'hello-elementor-child-buttons',
        $base_uri . '/assets/css/components/buttons.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/components/buttons.css' )
    );

    // Component: Flow Buttons (hero CTAs, assistant triggers)
    // High-specificity styles to resist Elementor inline overrides
    wp_enqueue_style(
        'hello-elementor-child-buttons-flow',
        $base_uri . '/assets/css/components/buttons-flow.css',
        array( 'hello-elementor-child-buttons' ),
        $get_version( '/assets/css/components/buttons-flow.css' )
    );

    // Component: Chips (category filters, tags)
    wp_enqueue_style(
        'hello-elementor-child-chips',
        $base_uri . '/assets/css/components/chips.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/components/chips.css' )
    );

    // Component: Product Cards (PLP)
    wp_enqueue_style(
        'hello-elementor-child-product-card',
        $base_uri . '/assets/css/components/product-card.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/components/product-card.css' )
    );

    // Component: Toast notifications
    wp_enqueue_style(
        'hello-elementor-child-toast',
        $base_uri . '/assets/css/components/toast.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/components/toast.css' )
    );

    // Component: Drawer/modals
    wp_enqueue_style(
        'hello-elementor-child-drawer',
        $base_uri . '/assets/css/components/drawer.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/components/drawer.css' )
    );

    // Component: Section utilities (pastel bands)
    wp_enqueue_style(
        'hello-elementor-child-sections',
        $base_uri . '/assets/css/components/sections.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/components/sections.css' )
    );

    // Component: Breadcrumbs
    wp_enqueue_style(
        'hello-elementor-child-breadcrumbs',
        $base_uri . '/assets/css/components/breadcrumbs.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/components/breadcrumbs.css' )
    );

    // Component: Assistant drawer
    wp_enqueue_style(
        'hello-elementor-child-assistant',
        $base_uri . '/assets/css/assistant.css',
        array( 'hello-elementor-child-tokens' ),
        $get_version( '/assets/css/assistant.css' )
    );

    // PLP styles (product listing page - conditional)
    if ( is_shop() || is_product_category() || is_product_taxonomy() ) {
        wp_enqueue_style(
            'hello-elementor-child-plp',
            $base_uri . '/assets/css/plp.css',
            array( 'hello-elementor-child-tokens' ),
            $get_version( '/assets/css/plp.css' )
        );
    }

    // 5) Color guard - MUST BE LAST
    // Dependencies: all component styles + Elementor/Woo when available
    $color_guard_deps = array(
        'hello-elementor-child-tokens',
        'hello-elementor-child-header',
        'hello-elementor-child-buttons',
        'hello-elementor-child-chips',
        'hello-elementor-child-product-card',
        'hello-elementor-child-toast',
        'hello-elementor-child-drawer',
        'hello-elementor-child-sections',
    );

    // Add Elementor frontend styles as dependency if registered
    if ( wp_style_is( 'elementor-frontend', 'registered' ) ) {
        $color_guard_deps[] = 'elementor-frontend';
    }

    // Add WooCommerce general styles as dependency if registered
    if ( wp_style_is( 'woocommerce-general', 'registered' ) ) {
        $color_guard_deps[] = 'woocommerce-general';
    }

    // Add WooCommerce layout styles as dependency if registered
    if ( wp_style_is( 'woocommerce-layout', 'registered' ) ) {
        $color_guard_deps[] = 'woocommerce-layout';
    }

    // Interaction patterns - enforce unified behaviors
    // Must load AFTER all component styles but BEFORE color-guard
    wp_enqueue_style(
        'hello-elementor-child-interaction-patterns',
        $base_uri . '/assets/css/interaction-patterns.css',
        $color_guard_deps,
        $get_version( '/assets/css/interaction-patterns.css' )
    );

    // Color guard - final override layer
    // Must be absolutely last - depends on interaction-patterns
    wp_enqueue_style(
        'hello-elementor-child-color-guard',
        $base_uri . '/assets/css/color-guard.css',
        array( 'hello-elementor-child-interaction-patterns' ),
        $get_version( '/assets/css/color-guard.css' )
    );

    // Shop Grid - WooCommerce product grid override (PLP archives only)
    // Must load AFTER color-guard and Elementor to override default grid styles
    // Highest priority to win over Autoptimize bundles
    if ( is_shop() || is_product_category() || is_product_taxonomy() ) {
        wp_enqueue_style(
            'hello-elementor-child-shop-grid',
            $base_uri . '/assets/css/shop-grid.css',
            array( 'hello-elementor-child-color-guard' ),
            $get_version( '/assets/css/shop-grid.css' )
        );
    }

    // JavaScript files (deferred)
    // Main JS (global utilities)
    wp_enqueue_script( 
        'hello-elementor-child-main', 
        $base_uri . '/assets/js/main.js', 
        array(), 
        $get_version( '/assets/js/main.js' ), 
        true 
    );

    // Header JS (menu interactions)
    wp_enqueue_script( 
        'hello-elementor-child-header', 
        $base_uri . '/assets/js/header.js', 
        array(), 
        $get_version( '/assets/js/header.js' ), 
        true 
    );

    // Consent events handler (CookieYes integration)
    wp_enqueue_script( 
        'hello-elementor-child-consent', 
        $base_uri . '/assets/js/consent-events.js', 
        array(), 
        $get_version( '/assets/js/consent-events.js' ), 
        true 
    );

    // Assistant bundle (homepage only)
    if ( is_front_page() || is_page( 'inicio' ) ) {
        $rel = '/assets/js/assistant/assistant.bundle.js';
        wp_enqueue_script(
            'cas-assistant-bundle',
            get_stylesheet_directory_uri() . $rel,
            array(),
            filemtime( get_stylesheet_directory() . $rel ),
            true
        );
        wp_add_inline_script(
            'cas-assistant-bundle',
            'window.casAssistantConfig=' . wp_json_encode( array(
                'restUrl' => esc_url_raw( rest_url( 'cas/v1/assistant' ) ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
            ) ) . ';',
            'before'
        );
    }

    // Home Hero - Ribbon detection (conditional on homepage)
    // Adds .has-ribbon class to body when ribbon visible (>640px)
    if ( is_front_page() || is_page( 'inicio' ) ) {
        wp_enqueue_script(
            'hello-elementor-child-home-hero-ribbon',
            $base_uri . '/assets/js/home-hero-ribbon-detect.js',
            array(),
            $get_version( '/assets/js/home-hero-ribbon-detect.js' ),
            true
        );
    }

    // PLP filters script (conditional)
    if ( is_shop() || is_product_category() || is_product_taxonomy() ) {
        wp_enqueue_script(
            'hello-elementor-child-plp-filters',
            $base_uri . '/assets/js/plp/filters.js',
            array( 'hello-elementor-child-consent' ),
            $get_version( '/assets/js/plp/filters.js' ),
            true
        );
        
        // PLP analytics (consent-gated, local events only)
        wp_enqueue_script(
            'hello-elementor-child-plp-analytics',
            $base_uri . '/assets/js/plp/analytics.js',
            array( 'hello-elementor-child-consent' ),
            $get_version( '/assets/js/plp/analytics.js' ),
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_assets', 20 );

/**
 * Ensure interaction-patterns.css and color-guard.css load AFTER Elementor kit CSS.
 * 
 * Elementor generates dynamic kit CSS (post-*.css) at various priorities.
 * This hook runs at priority 999 to guarantee our override styles are absolutely last.
 * 
 * @since 1.0.0
 */
function hello_elementor_child_enqueue_final_overrides(): void {
    global $wp_styles;
    
    // Get theme base URI
    $base_uri = get_stylesheet_directory_uri();
    $theme_version = wp_get_theme()->get( 'Version' );
    
    // Check if Elementor kit CSS is registered (pattern: elementor-post-* or elementor-kit-*)
    $has_elementor_kit = false;
    foreach ( $wp_styles->registered as $handle => $style ) {
        if ( strpos( $handle, 'elementor-post-' ) !== false || 
             strpos( $handle, 'elementor-kit-' ) !== false ) {
            $has_elementor_kit = true;
            break;
        }
    }
    
    // If Elementor kit CSS exists, ensure our styles depend on it
    if ( $has_elementor_kit ) {
        // Find all Elementor kit handles
        $elementor_kit_handles = array();
        foreach ( $wp_styles->registered as $handle => $style ) {
            if ( strpos( $handle, 'elementor-post-' ) !== false || 
                 strpos( $handle, 'elementor-kit-' ) !== false ||
                 strpos( $handle, 'elementor-frontend' ) !== false ) {
                $elementor_kit_handles[] = $handle;
            }
        }
        
        // Add Elementor kit handles as dependencies to interaction-patterns
        if ( isset( $wp_styles->registered['hello-elementor-child-interaction-patterns'] ) ) {
            $current_deps = (array) $wp_styles->registered['hello-elementor-child-interaction-patterns']->deps;
            $wp_styles->registered['hello-elementor-child-interaction-patterns']->deps = array_unique( 
                array_merge( $current_deps, $elementor_kit_handles ) 
            );
        }
        
        // Add Elementor kit handles as dependencies to header-sticky
        if ( isset( $wp_styles->registered['hello-elementor-child-header-sticky'] ) ) {
            $current_deps = (array) $wp_styles->registered['hello-elementor-child-header-sticky']->deps;
            $wp_styles->registered['hello-elementor-child-header-sticky']->deps = array_unique( 
                array_merge( $current_deps, $elementor_kit_handles ) 
            );
        }
        
        // Add Elementor kit handles as dependencies to nav-drawer
        if ( isset( $wp_styles->registered['hello-elementor-child-nav-drawer'] ) ) {
            $current_deps = (array) $wp_styles->registered['hello-elementor-child-nav-drawer']->deps;
            $wp_styles->registered['hello-elementor-child-nav-drawer']->deps = array_unique( 
                array_merge( $current_deps, $elementor_kit_handles ) 
            );
        }
        
        // Add Elementor kit handles as dependencies to shop-grid
        if ( isset( $wp_styles->registered['hello-elementor-child-shop-grid'] ) ) {
            $current_deps = (array) $wp_styles->registered['hello-elementor-child-shop-grid']->deps;
            $wp_styles->registered['hello-elementor-child-shop-grid']->deps = array_unique( 
                array_merge( $current_deps, $elementor_kit_handles ) 
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_final_overrides', 999 );

/**
 * Exclude assistant ES6 modules from Autoptimize concatenation.
 * 
 * ES6 modules use import/export syntax and must not be bundled.
 * 
 * @since 1.0.0
 */
function hello_elementor_child_autoptimize_exclude_assistant( $exclude ) {
    $modules = array(
        'assets/js/assistant/core.js',
        'assets/js/assistant/ui.js',
        'assets/js/assistant/api.js',
        'assets/js/assistant/boot.js',
    );
    
    // Parse existing exclusions
    $existing = array_map( 'trim', explode( ',', $exclude ) );
    
    // Merge and deduplicate
    $merged = array_unique( array_merge( $existing, $modules ) );
    
    // Remove empty strings
    $merged = array_filter( $merged );
    
    return implode( ', ', $merged );
}
add_filter( 'autoptimize_filter_js_exclude', 'hello_elementor_child_autoptimize_exclude_assistant', 10, 1 );


/**
 * Register theme menu locations: primary and utility.
 *
 * - 'primary' => Menú principal
 * - 'utility' => Menú de utilidad
 */
function hello_elementor_child_register_menus(): void {
    register_nav_menus( array(
        'primary' => __( 'Menú principal', 'hello-elementor-child' ),
        'utility' => __( 'Menú de utilidad', 'hello-elementor-child' ),
    ) );
}
add_action( 'after_setup_theme', 'hello_elementor_child_register_menus', 11 );

/**
 * Elementor compatibility flags: prefer theme fonts and disable FA4 shim.
 *
 * These filters tell Elementor to use theme fonts and not to inject
 * Google Fonts automatically. They also disable automatic FA4 shim.
 */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );
add_filter( 'elementor/frontend/should_load_font_awesome', '__return_false' );
add_filter( 'elementor/preview/enqueue_styles', '__return_true' );


/**
 * Load theme includes for setup and accessibility.
 */
require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/accessibility.php';
require_once __DIR__ . '/inc/customizer.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/plp-query.php';

/**
 * Add rewrite rule for /styleguide/colors styleguide page.
 * Accessible at: https://yourdomain.com/styleguide/colors
 */
function cas_add_styleguide_rewrite_rules(): void {
    add_rewrite_rule(
        '^styleguide/colors/?$',
        'index.php?styleguide_page=colors',
        'top'
    );
}
add_action( 'init', 'cas_add_styleguide_rewrite_rules' );

/**
 * Register custom query var for styleguide page.
 */
function cas_add_styleguide_query_vars( $vars ) {
    $vars[] = 'styleguide_page';
    return $vars;
}
add_filter( 'query_vars', 'cas_add_styleguide_query_vars' );

/**
 * Template redirect for styleguide page.
 * Serves the styleguide/index.php template when accessing /styleguide/colors
 */
function cas_styleguide_template_redirect(): void {
    $styleguide_page = get_query_var( 'styleguide_page' );
    
    if ( 'colors' === $styleguide_page ) {
        $template_path = get_stylesheet_directory() . '/styleguide/index.php';
        
        if ( file_exists( $template_path ) ) {
            include $template_path;
            exit;
        }
    }
}
add_action( 'template_redirect', 'cas_styleguide_template_redirect' );

/**
 * Add defer attribute to selected script handles (non-blocking).
 * Registered once to avoid duplicate anonymous closures.
 *
 * @param string $tag    The HTML script tag.
 * @param string $handle The registered script handle.
 * @return string Filtered tag.
 */
function hello_elementor_child_add_script_defer( $tag, $handle ) {
    $defer_handles = array( 'hello-elementor-child-main', 'hello-elementor-child-header', 'hello-elementor-child-consent', 'hello-elementor-child-plp-filters' );
    if ( in_array( $handle, $defer_handles, true ) ) {
        if ( false === stripos( $tag, ' defer' ) ) {
            $tag = str_replace( ' src', ' defer src', $tag );
        }
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'hello_elementor_child_add_script_defer', 10, 2 );

/**
 * Disable Elementor Theme Builder locations (header/footer/single/archive).
 *
 * This prevents Elementor from rendering a separate Header template that could
 * duplicate our custom header in `header.php`. If you later want Elementor to
 * control the header/footer again, remove this filter or return true instead.
 */
// Ensure Elementor Theme Builder locations are not registered by Hello theme.
add_filter( 'hello_elementor_register_elementor_locations', '__return_false', 0 );

// Belt-and-suspenders: remove the parent theme's registration action if it was added.
function cas_disable_hello_elementor_locations(): void {
    if ( function_exists( 'remove_action' ) ) {
        remove_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations', 10 );
    }
}
add_action( 'after_setup_theme', 'cas_disable_hello_elementor_locations', 20 );

/**
 * Optional: If Elementor managed to register locations before our filter ran
 * (edge cases), avoid rendering Elementor's header by short-circuiting
 * elementor_theme_do_location('header') inside our child theme header.
 *
 * We do this by hooking a no-op for 'elementor/theme/register_locations' at an
 * earlier priority, but the above filter should be sufficient in Hello.
 */

/**
 * ========================================
 * WOOCOMMERCE PRICE FORMATTING (CLP)
 * ========================================
 * 
 * Chile uses Chilean Peso (CLP) with format: $X.XXX.XXX
 * - No decimals (prices are whole numbers)
 * - Dot as thousands separator
 * - $ prefix (currency symbol)
 * 
 * Examples:
 *   15000    => $15.000
 *   1250000  => $1.250.000
 *   499990   => $499.990
 * 
 * This hooks into WooCommerce's price display filters to ensure
 * all prices (product grids, single products, cart, checkout) use
 * Chilean formatting with no decimals or trailing ",00".
 */

/**
 * Short alias for Chilean peso formatting
 *
 * @param string|int|float|null $price Price to format.
 * @return string Formatted price with CLP symbol.
 */
function cas_clp( $price ): string {
	return hello_elementor_child_format_clp( $price );
}

/**
 * Override WooCommerce price format arguments
 *
 * Forces Chilean peso format:
 * - 0 decimal places
 * - Dot as thousands separator
 * - No decimal separator (not displayed)
 * - $ currency symbol
 *
 * @param array $args Price format arguments.
 * @return array Modified arguments.
 */
function cas_wc_price_args( array $args ): array {
	$args['decimals']           = 0;           // No decimals for CLP
	$args['decimal_separator']  = ',';         // Not displayed (0 decimals)
	$args['thousand_separator'] = '.';         // Dot separator (1.250.000)
	$args['currency']           = 'CLP';       // Chilean Peso
	$args['currency_pos']       = 'left';      // $ before number
	
	return $args;
}
add_filter( 'wc_price_args', 'cas_wc_price_args', 10, 1 );

/**
 * Set WooCommerce currency to CLP
 *
 * @param string $currency Current currency code.
 * @return string Currency code.
 */
function cas_wc_currency( string $currency ): string {
	return 'CLP';
}
add_filter( 'woocommerce_currency', 'cas_wc_currency', 10, 1 );

/**
 * Override WooCommerce currency symbol
 *
 * @param string $symbol Current currency symbol.
 * @param string $currency Currency code.
 * @return string Currency symbol.
 */
function cas_wc_currency_symbol( string $symbol, string $currency ): string {
	if ( $currency === 'CLP' ) {
		return '$';
	}
	return $symbol;
}
add_filter( 'woocommerce_currency_symbol', 'cas_wc_currency_symbol', 10, 2 );

/**
 * Format price with Chilean peso formatting
 *
 * Routes through our cas_clp() helper to ensure consistent formatting
 * across all WooCommerce contexts (PLP, PDP, cart, checkout).
 *
 * @param string $formatted_price Formatted price HTML from WooCommerce.
 * @param float  $price Raw price value.
 * @param array  $args Price format arguments.
 * @param mixed  $unformatted_price Unformatted price.
 * @return string Formatted price HTML.
 */
function cas_wc_price_html( string $formatted_price, float $price, array $args, $unformatted_price ): string {
	// Extract just the numeric value (remove any HTML tags)
	$clean_price = strip_tags( $formatted_price );
	
	// Remove currency symbol and whitespace
	$clean_price = preg_replace( '/[^0-9.,]/', '', $clean_price );
	
	// Convert to float (handle both dot and comma decimals)
	$clean_price = str_replace( '.', '', $clean_price ); // Remove thousands separators
	$clean_price = str_replace( ',', '.', $clean_price ); // Normalize decimal separator
	$numeric_value = (float) $clean_price;
	
	// Use our CLP formatter
	$clp_formatted = cas_clp( $numeric_value );
	
	// Preserve original HTML structure (span classes, etc.)
	if ( preg_match( '/<span[^>]*class="[^"]*woocommerce-Price-amount[^"]*"[^>]*>/', $formatted_price ) ) {
		// Replace content inside .woocommerce-Price-amount span
		$formatted_price = preg_replace(
			'/(<span[^>]*class="[^"]*woocommerce-Price-amount[^"]*"[^>]*>).*?(<\/span>)/s',
			'$1' . $clp_formatted . '$2',
			$formatted_price
		);
	} else {
		// No span found, return plain formatted price
		$formatted_price = $clp_formatted;
	}
	
	return $formatted_price;
}
add_filter( 'wc_price', 'cas_wc_price_html', 10, 4 );

/**
 * Remove decimals from formatted price
 *
 * This filter catches any remaining decimal display and removes it.
 * Ensures no ",00" or ".00" appears in prices.
 *
 * @param string $formatted_price Formatted price.
 * @return string Price without decimals.
 */
function cas_remove_price_decimals( string $formatted_price ): string {
	// Remove ,00 or .00 (trailing decimal zeros)
	$formatted_price = preg_replace( '/[,\.]\d{2}(?=\D|$)/', '', $formatted_price );
	
	return $formatted_price;
}
add_filter( 'formatted_woocommerce_price', 'cas_remove_price_decimals', 10, 1 );
add_filter( 'woocommerce_price_trim_zeros', '__return_true', 10, 1 );

/**
 * Ensure WooCommerce number format uses CLP settings
 *
 * @param array $args Number format arguments.
 * @return array Modified arguments.
 */
function cas_wc_number_format_args( array $args ): array {
	$args['decimals']           = 0;
	$args['decimal_separator']  = ',';
	$args['thousand_separator'] = '.';
	
	return $args;
}
add_filter( 'woocommerce_price_format_num_decimals', function() { return 0; }, 10, 1 );

/**
 * Force WooCommerce to use 0 decimals globally
 */
add_filter( 'woocommerce_get_price_decimals', function() { return 0; }, 10, 1 );

/**
 * ========================================
 * SPANISH (CHILE) TRANSLATIONS
 * ========================================
 * 
 * Translates WooCommerce and theme strings to Spanish (Chile).
 * 
 * Common WooCommerce strings:
 * - "Add to cart" => "Agregar al carro"
 * - "Select options" => "Seleccionar opciones"
 * - "Sale!" => "¡Oferta!"
 * - "Read more" => "Leer más"
 * - "Out of stock" => "Agotado"
 * - "In stock" => "Disponible"
 * 
 * Locale: es_CL (Spanish - Chile)
 * Text domain: woocommerce, hello-elementor-child
 */

/**
 * Set theme text domain for translations
 */
function cas_load_theme_textdomain(): void {
	load_theme_textdomain( 'hello-elementor-child', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'cas_load_theme_textdomain' );

/**
 * Translate WooCommerce strings to Spanish (Chile)
 *
 * @param string $translated Translated text.
 * @param string $text Original text.
 * @param string $domain Text domain.
 * @return string Translated text.
 */
function cas_translate_woocommerce_strings( string $translated, string $text, string $domain ): string {
	// Only translate WooCommerce strings
	if ( $domain !== 'woocommerce' ) {
		return $translated;
	}
	
	// Spanish (Chile) translations
	$translations = [
		// Product buttons
		'Add to cart'                => 'Agregar al carro',
		'Select options'             => 'Seleccionar opciones',
		'Read more'                  => 'Leer más',
		'View products'              => 'Ver productos',
		'Quick view'                 => 'Vista rápida',
		
		// Product status
		'Sale!'                      => '¡Oferta!',
		'Out of stock'               => 'Agotado',
		'In stock'                   => 'Disponible',
		'Available on backorder'     => 'Disponible en pedido',
		'Only %s left in stock'      => 'Solo quedan %s en stock',
		
		// Product details
		'Description'                => 'Descripción',
		'Additional information'     => 'Información adicional',
		'Reviews'                    => 'Reseñas',
		'Related products'           => 'Productos relacionados',
		'You may also like'          => 'También te puede gustar',
		
		// Cart
		'Cart'                       => 'Carro',
		'Your cart is empty'         => 'Tu carro está vacío',
		'Continue shopping'          => 'Seguir comprando',
		'Update cart'                => 'Actualizar carro',
		'Proceed to checkout'        => 'Proceder al pago',
		'Remove'                     => 'Eliminar',
		'Quantity'                   => 'Cantidad',
		'Subtotal'                   => 'Subtotal',
		'Total'                      => 'Total',
		
		// Checkout
		'Checkout'                   => 'Pago',
		'Billing details'            => 'Datos de facturación',
		'Shipping details'           => 'Datos de envío',
		'Your order'                 => 'Tu pedido',
		'Product'                    => 'Producto',
		'Price'                      => 'Precio',
		'Place order'                => 'Realizar pedido',
		'Order notes'                => 'Notas del pedido',
		'Payment method'             => 'Método de pago',
		'Shipping method'            => 'Método de envío',
		
		// Account
		'My account'                 => 'Mi cuenta',
		'Dashboard'                  => 'Panel',
		'Orders'                     => 'Pedidos',
		'Downloads'                  => 'Descargas',
		'Addresses'                  => 'Direcciones',
		'Account details'            => 'Detalles de cuenta',
		'Logout'                     => 'Cerrar sesión',
		'Login'                      => 'Iniciar sesión',
		'Register'                   => 'Registrarse',
		
		// Search & filters
		'Search'                     => 'Buscar',
		'Search results for "%s"'    => 'Resultados de búsqueda para "%s"',
		'No products found'          => 'No se encontraron productos',
		'Showing all %d results'     => 'Mostrando los %d resultados',
		'Showing %1$d-%2$d of %3$d results' => 'Mostrando %1$d-%2$d de %3$d resultados',
		'Sort by'                    => 'Ordenar por',
		'Default sorting'            => 'Orden predeterminado',
		'Sort by popularity'         => 'Ordenar por popularidad',
		'Sort by average rating'     => 'Ordenar por calificación',
		'Sort by latest'             => 'Ordenar por más recientes',
		'Sort by price: low to high' => 'Ordenar por precio: menor a mayor',
		'Sort by price: high to low' => 'Ordenar por precio: mayor a menor',
		
		// Filters
		'Filter'                     => 'Filtrar',
		'Filters'                    => 'Filtros',
		'Apply filters'              => 'Aplicar filtros',
		'Clear filters'              => 'Limpiar filtros',
		'Price'                      => 'Precio',
		'Category'                   => 'Categoría',
		'Categories'                 => 'Categorías',
		'Brands'                     => 'Marcas',
		'Color'                      => 'Color',
		'Size'                       => 'Tamaño',
		
		// Compare
		'Compare'                    => 'Comparar',
		'Compare products'           => 'Comparar productos',
		'Add to compare'             => 'Agregar a comparar',
		'Remove from compare'        => 'Quitar de comparar',
		'Clear all'                  => 'Limpiar todo',
		
		// Wishlist
		'Wishlist'                   => 'Lista de deseos',
		'Add to wishlist'            => 'Agregar a favoritos',
		'Remove from wishlist'       => 'Quitar de favoritos',
		
		// Ratings
		'Rated %s out of 5'          => 'Calificado %s de 5',
		'customer review'            => 'reseña de cliente',
		'customer reviews'           => 'reseñas de clientes',
		'No reviews yet'             => 'Sin reseñas aún',
		'Be the first to review'     => 'Sé el primero en opinar',
		
		// Misc
		'Free shipping'              => 'Envío gratis',
		'Ships in 24h'               => 'Envío en 24h',
		'View details'               => 'Ver detalles',
		'Learn more'                 => 'Saber más',
		'Contact us'                 => 'Contáctanos',
		'Need help?'                 => '¿Necesitas ayuda?',
	];
	
	// Return translation if exists, otherwise return original
	return $translations[ $text ] ?? $translated;
}
add_filter( 'gettext', 'cas_translate_woocommerce_strings', 20, 3 );

/**
 * Translate WooCommerce strings with context
 *
 * @param string $translated Translated text.
 * @param string $text Original text.
 * @param string $context Translation context.
 * @param string $domain Text domain.
 * @return string Translated text.
 */
function cas_translate_woocommerce_strings_with_context( string $translated, string $text, string $context, string $domain ): string {
	// Only translate WooCommerce strings
	if ( $domain !== 'woocommerce' ) {
		return $translated;
	}
	
	// Context-specific translations
	$context_translations = [
		'product' => [
			'Sale!' => '¡Oferta!',
		],
		'checkout' => [
			'Place order' => 'Realizar pedido',
		],
	];
	
	if ( isset( $context_translations[ $context ][ $text ] ) ) {
		return $context_translations[ $context ][ $text ];
	}
	
	return $translated;
}
add_filter( 'gettext_with_context', 'cas_translate_woocommerce_strings_with_context', 20, 4 );

/**
 * Translate "Add to cart" button text based on product type
 *
 * @param string $text Button text.
 * @param \WC_Product $product Product object.
 * @return string Translated button text.
 */
function cas_translate_add_to_cart_text( string $text, $product ): string {
	$translations = [
		'Add to cart'     => 'Agregar al carro',
		'Select options'  => 'Seleccionar opciones',
		'Read more'       => 'Leer más',
		'View products'   => 'Ver productos',
	];
	
	return $translations[ $text ] ?? $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'cas_translate_add_to_cart_text', 20, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'cas_translate_add_to_cart_text', 20, 2 );

/**
 * Translate "Sale!" badge text
 *
 * @param string $text Badge text.
 * @return string Translated badge text.
 */
function cas_translate_sale_badge( string $text ): string {
	return str_replace( 'Sale!', '¡Oferta!', $text );
}
add_filter( 'woocommerce_sale_flash', 'cas_translate_sale_badge', 20, 1 );

/**
 * Set locale to Spanish (Chile)
 *
 * @param string $locale Current locale.
 * @return string Locale code.
 */
function cas_set_locale( string $locale ): string {
	return 'es_CL';
}
add_filter( 'locale', 'cas_set_locale', 10, 1 );

/* ========================================
   JSON-LD STRUCTURED DATA (SEO)
   ======================================== */

/**
 * Output JSON-LD structured data for breadcrumbs, collections, and products
 * Supports BreadcrumbList and CollectionPage/ItemList schemas
 *
 * @return void
 */
function cas_output_json_ld_structured_data(): void {
	$schema_data = [];
	
	// 1. BreadcrumbList (on all pages except home)
	if ( ! is_front_page() ) {
		$breadcrumb_schema = cas_get_breadcrumb_schema();
		if ( $breadcrumb_schema ) {
			$schema_data[] = $breadcrumb_schema;
		}
	}
	
	// 2. CollectionPage + ItemList (on product archive pages)
	if ( is_shop() || is_product_category() ) {
		$collection_schema = cas_get_collection_page_schema();
		if ( $collection_schema ) {
			$schema_data[] = $collection_schema;
		}
	}
	
	// Output all schemas in single script tag
	if ( ! empty( $schema_data ) ) {
		echo '<script type="application/ld+json">';
		echo wp_json_encode( $schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo '</script>' . "\n";
	}
}
add_action( 'wp_head', 'cas_output_json_ld_structured_data', 5 );

/**
 * Get BreadcrumbList schema
 *
 * @return array|null Schema data or null if not applicable
 */
function cas_get_breadcrumb_schema(): ?array {
	if ( is_front_page() ) {
		return null;
	}
	
	$breadcrumbs = [];
	$position = 1;
	
	// Home (always first)
	$breadcrumbs[] = [
		'@type' => 'ListItem',
		'position' => $position++,
		'name' => 'Inicio',
		'item' => home_url( '/' ),
	];
	
	// Shop page (if on WooCommerce pages)
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() ) ) {
		if ( ! is_shop() ) {
			$breadcrumbs[] = [
				'@type' => 'ListItem',
				'position' => $position++,
				'name' => 'Productos',
				'item' => get_permalink( wc_get_page_id( 'shop' ) ),
			];
		}
	}
	
	// Category hierarchy
	if ( is_product_category() ) {
		$current_cat = get_queried_object();
		
		// Parent categories
		if ( $current_cat->parent ) {
			$parent_cats = [];
			$parent_id = $current_cat->parent;
			
			while ( $parent_id ) {
				$parent = get_term( $parent_id, 'product_cat' );
				if ( ! $parent || is_wp_error( $parent ) ) {
					break;
				}
				
				array_unshift( $parent_cats, [
					'@type' => 'ListItem',
					'position' => 0, // Will be updated
					'name' => $parent->name,
					'item' => get_term_link( $parent ),
				] );
				
				$parent_id = $parent->parent;
			}
			
			// Update positions
			foreach ( $parent_cats as &$cat ) {
				$cat['position'] = $position++;
			}
			
			$breadcrumbs = array_merge( $breadcrumbs, $parent_cats );
		}
		
		// Current category
		$breadcrumbs[] = [
			'@type' => 'ListItem',
			'position' => $position++,
			'name' => $current_cat->name,
			'item' => get_term_link( $current_cat ),
		];
		
	} elseif ( is_product() ) {
		// Single product
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
						'@type' => 'ListItem',
						'position' => 0,
						'name' => $parent->name,
						'item' => get_term_link( $parent ),
					] );
					
					$parent_id = $parent->parent;
				}
				
				foreach ( $parent_cats as &$cat ) {
					$cat['position'] = $position++;
				}
				
				$breadcrumbs = array_merge( $breadcrumbs, $parent_cats );
			}
			
			// Category
			$breadcrumbs[] = [
				'@type' => 'ListItem',
				'position' => $position++,
				'name' => $main_cat->name,
				'item' => get_term_link( $main_cat ),
			];
		}
		
		// Current product
		$breadcrumbs[] = [
			'@type' => 'ListItem',
			'position' => $position++,
			'name' => get_the_title(),
			'item' => get_permalink(),
		];
		
	} elseif ( is_singular() ) {
		// Other singular content
		$breadcrumbs[] = [
			'@type' => 'ListItem',
			'position' => $position++,
			'name' => get_the_title(),
			'item' => get_permalink(),
		];
	}
	
	// Don't output if only home exists
	if ( count( $breadcrumbs ) <= 1 ) {
		return null;
	}
	
	return [
		'@context' => 'https://schema.org',
		'@type' => 'BreadcrumbList',
		'itemListElement' => $breadcrumbs,
	];
}

/**
 * Get CollectionPage schema with ItemList for product archives
 *
 * @return array|null Schema data or null if not applicable
 */
function cas_get_collection_page_schema(): ?array {
	if ( ! ( is_shop() || is_product_category() ) ) {
		return null;
	}
	
	// Get current page info
	$page_title = is_shop() ? 'Productos' : single_term_title( '', false );
	$page_url = is_shop() ? get_permalink( wc_get_page_id( 'shop' ) ) : get_term_link( get_queried_object() );
	
	// Get products from current query
	global $wp_query;
	$products = [];
	
	if ( $wp_query->have_posts() ) {
		$position = 1;
		
		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();
			$product = wc_get_product( get_the_ID() );
			
			if ( ! $product ) {
				continue;
			}
			
			$products[] = [
				'@type' => 'ListItem',
				'position' => $position++,
				'url' => get_permalink(),
				'name' => get_the_title(),
			];
		}
		
		wp_reset_postdata();
	}
	
	// Don't output if no products
	if ( empty( $products ) ) {
		return null;
	}
	
	return [
		'@context' => 'https://schema.org',
		'@type' => 'CollectionPage',
		'name' => $page_title,
		'url' => $page_url,
		'mainEntity' => [
			'@type' => 'ItemList',
			'numberOfItems' => count( $products ),
			'itemListElement' => $products,
		],
	];
}

/**
 * Add WebSite schema with search action
 * Helps Google show site search box in search results
 *
 * @return void
 */
function cas_output_website_schema(): void {
	if ( ! is_front_page() ) {
		return;
	}
	
	$schema = [
		'@context' => 'https://schema.org',
		'@type' => 'WebSite',
		'name' => get_bloginfo( 'name' ),
		'url' => home_url( '/' ),
		'potentialAction' => [
			'@type' => 'SearchAction',
			'target' => [
				'@type' => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			],
			'query-input' => 'required name=search_term_string',
		],
	];
	
	echo '<script type="application/ld+json">';
	echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	echo '</script>' . "\n";
}
add_action( 'wp_head', 'cas_output_website_schema', 5 );

/**
 * ============================================================================
 * HOME HERO - CUSTOMIZER PANEL
 * ============================================================================
 * 
 * Admin-managed background images for home hero section
 * Outputs CSS variables to <head> for use in home-hero.css
 * 
 * Performance budgets (post-compression):
 * - Desktop: ≤120KB (AVIF/WebP)
 * - Tablet: ≤90KB (AVIF/WebP)
 * - Mobile: ≤60KB (AVIF/WebP)
 * 
 * @since 1.0.0
 */

/**
 * Register Customizer section and image controls
 * 
 * @param WP_Customize_Manager $wp_customize Customizer manager instance
 */
function cas_register_home_hero_customizer( $wp_customize ): void {
	
	// Add panel: Home Hero
	$wp_customize->add_section(
		'cas_home_hero',
		array(
			'title'       => __( 'Home Hero', 'hello-elementor-child' ),
			'description' => __( 'Imágenes de fondo decorativas para el hero de la página de inicio. Mantén los tamaños ≤120KB (desktop), ≤90KB (tablet), ≤60KB (mobile). Formatos recomendados: AVIF o WebP.', 'hello-elementor-child' ),
			'priority'    => 130,
		)
	);

	// Setting: Desktop background
	$wp_customize->add_setting(
		'cas_home_hero_bg_desktop',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	// Control: Desktop background
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'cas_home_hero_bg_desktop',
			array(
				'label'       => __( 'Desktop Background (≥1024px)', 'hello-elementor-child' ),
				'description' => __( 'Imagen decorativa anclada a la derecha. Tamaño recomendado: 1920×600px, ≤120KB (AVIF/WebP).', 'hello-elementor-child' ),
				'section'     => 'cas_home_hero',
				'mime_type'   => 'image',
				'button_labels' => array(
					'select'       => __( 'Seleccionar imagen', 'hello-elementor-child' ),
					'change'       => __( 'Cambiar imagen', 'hello-elementor-child' ),
					'remove'       => __( 'Quitar', 'hello-elementor-child' ),
				),
			)
		)
	);

	// Setting: Tablet background
	$wp_customize->add_setting(
		'cas_home_hero_bg_tablet',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	// Control: Tablet background
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'cas_home_hero_bg_tablet',
			array(
				'label'       => __( 'Tablet Background (640-1023px)', 'hello-elementor-child' ),
				'description' => __( 'Versión ligera/recortada para tablets. Tamaño recomendado: 1200×500px, ≤90KB (AVIF/WebP).', 'hello-elementor-child' ),
				'section'     => 'cas_home_hero',
				'mime_type'   => 'image',
				'button_labels' => array(
					'select'       => __( 'Seleccionar imagen', 'hello-elementor-child' ),
					'change'       => __( 'Cambiar imagen', 'hello-elementor-child' ),
					'remove'       => __( 'Quitar', 'hello-elementor-child' ),
				),
			)
		)
	);

	// Setting: Mobile background
	$wp_customize->add_setting(
		'cas_home_hero_bg_mobile',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);

	// Control: Mobile background
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'cas_home_hero_bg_mobile',
			array(
				'label'       => __( 'Mobile Background (<640px)', 'hello-elementor-child' ),
				'description' => __( 'Patrón pequeño o ilustración mínima para móviles. Tamaño recomendado: 800×400px, ≤60KB (WebP). Opcional: dejar vacío para usar gradiente CSS.', 'hello-elementor-child' ),
				'section'     => 'cas_home_hero',
				'mime_type'   => 'image',
				'button_labels' => array(
					'select'       => __( 'Seleccionar imagen', 'hello-elementor-child' ),
					'change'       => __( 'Cambiar imagen', 'hello-elementor-child' ),
					'remove'       => __( 'Quitar', 'hello-elementor-child' ),
				),
			)
		)
	);
}
add_action( 'customize_register', 'cas_register_home_hero_customizer' );

/**
 * Output CSS variables for home hero backgrounds
 * Injects inline CSS into <head> with image URLs
 * 
 * Only outputs on front page to minimize HTML bloat
 */
function cas_output_home_hero_css_vars(): void {
	
	// Only output on front page
	if ( ! is_front_page() && ! is_page( 'inicio' ) ) {
		return;
	}

	// Get image attachment IDs from theme mods (using cas_ prefix)
	$desktop_id = get_theme_mod( 'cas_home_hero_bg_desktop', '' );
	$tablet_id  = get_theme_mod( 'cas_home_hero_bg_tablet', '' );
	$mobile_id  = get_theme_mod( 'cas_home_hero_bg_mobile', '' );

	// Resolve to URLs, set to 'none' if empty
	$desktop_value = 'none';
	$tablet_value  = 'none';
	$mobile_value  = 'none';

	if ( ! empty( $desktop_id ) ) {
		$url = wp_get_attachment_image_url( absint( $desktop_id ), 'full' );
		if ( $url ) {
			$desktop_value = "url('" . esc_url( $url ) . "')";
		}
	}

	if ( ! empty( $tablet_id ) ) {
		$url = wp_get_attachment_image_url( absint( $tablet_id ), 'full' );
		if ( $url ) {
			$tablet_value = "url('" . esc_url( $url ) . "')";
		}
	}

	if ( ! empty( $mobile_id ) ) {
		$url = wp_get_attachment_image_url( absint( $mobile_id ), 'full' );
		if ( $url ) {
			$mobile_value = "url('" . esc_url( $url ) . "')";
		}
	}

	// Output CSS variables on body.home
	echo '<style id="cas-home-hero-vars">';
	echo 'body.home{';
	echo '--hero-bg-desktop:' . $desktop_value . ';';
	echo '--hero-bg-tablet:' . $tablet_value . ';';
	echo '--hero-bg-mobile:' . $mobile_value . ';';
	echo '}';
	echo '</style>' . "\n";
}
add_action( 'wp_head', 'cas_output_home_hero_css_vars', 10 );

/**
 * Add Customizer live preview support
 * Refreshes preview when hero images change
 * 
 * @param WP_Customize_Manager $wp_customize Customizer manager instance
 */
function cas_home_hero_customizer_live_preview( $wp_customize ): void {
	// Enable selective refresh for hero backgrounds
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'home_hero_backgrounds',
			array(
				'selector'            => '.home-hero',
				'container_inclusive' => false,
				'render_callback'     => '__return_false', // Full page refresh needed for CSS vars
			)
		);
	}
}
add_action( 'customize_register', 'cas_home_hero_customizer_live_preview', 20 );

/**
 * ============================================================================
 * PAGE TEMPLATE & TITLE SYSTEM
 * ============================================================================
 * 
 * Hides default page titles and forces Elementor Full Width template on all pages.
 * 
 * ENABLE/DISABLE:
 * - To disable: Add this to wp-config.php or a Must-Use plugin:
 *   define( 'CAS_DISABLE_PAGE_DEFAULTS', true );
 * 
 * ROLLBACK:
 * - Emergency disable via filter:
 *   add_filter( 'cas_enable_page_defaults', '__return_false' );
 */

/**
 * Hide default page titles on all pages
 * 
 * @return string 'hide' to remove the title from the page template
 */
function cas_hide_page_title(): string {
	// Killswitch: Check constant
	if ( defined( 'CAS_DISABLE_PAGE_DEFAULTS' ) && CAS_DISABLE_PAGE_DEFAULTS ) {
		return 'show';
	}

	// Rollback filter
	if ( ! apply_filters( 'cas_enable_page_defaults', true ) ) {
		return 'show';
	}

	// Only hide on pages (not posts, archives, etc.)
	if ( is_page() ) {
		return 'hide';
	}

	return 'show';
}
add_filter( 'hello_elementor_page_title', 'cas_hide_page_title' );

/**
 * Force Elementor Full Width template on all pages
 * 
 * @param string $template The current page template
 * @return string 'elementor_canvas' or original template
 */
function cas_force_elementor_full_width( $template ) {
	// Killswitch: Check constant
	if ( defined( 'CAS_DISABLE_PAGE_DEFAULTS' ) && CAS_DISABLE_PAGE_DEFAULTS ) {
		return $template;
	}

	// Rollback filter
	if ( ! apply_filters( 'cas_enable_page_defaults', true ) ) {
		return $template;
	}

	// Only apply to pages
	if ( ! is_page() ) {
		return $template;
	}

	// Check if already using a template
	$current_template = get_page_template_slug();
	

	// Respect manually set templates
	return $template;
}

/**
 * Enqueue Logger - Debug CSS/JS Load Order (Admin-only, Front Page)
 * 
 * Logs enqueued styles and scripts to debug.log to verify load order.
 * Only runs for logged-in admins on the front page.
 * Helps verify home-hero.css loads after tokens and before Elementor.
 * 
 * Enable WordPress debug logging in wp-config.php:
 * define('WP_DEBUG', true);
 * define('WP_DEBUG_LOG', true);
 * define('WP_DEBUG_DISPLAY', false);
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */
function cas_log_enqueue_order(): void {
	// Only run for admins
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Only run on front page
	if ( ! is_front_page() && ! is_page( 'inicio' ) ) {
		return;
	}

	// Only run if debug log is enabled
	if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
		return;
	}

	global $wp_styles, $wp_scripts;

	// Log CSS load order
	if ( ! empty( $wp_styles->queue ) ) {
		error_log( '=== CSS Load Order (Front Page) ===' );
		foreach ( $wp_styles->queue as $index => $handle ) {
			$src = isset( $wp_styles->registered[ $handle ]->src ) ? $wp_styles->registered[ $handle ]->src : 'N/A';
			$deps = isset( $wp_styles->registered[ $handle ]->deps ) ? implode( ', ', $wp_styles->registered[ $handle ]->deps ) : 'none';
			error_log( sprintf( '[%d] %s | Deps: %s | Src: %s', $index + 1, $handle, $deps, $src ) );
		}
	}

	// Log JS load order
	if ( ! empty( $wp_scripts->queue ) ) {
		error_log( '=== JS Load Order (Front Page) ===' );
		foreach ( $wp_scripts->queue as $index => $handle ) {
			$src = isset( $wp_scripts->registered[ $handle ]->src ) ? $wp_scripts->registered[ $handle ]->src : 'N/A';
			$deps = isset( $wp_scripts->registered[ $handle ]->deps ) ? implode( ', ', $wp_scripts->registered[ $handle ]->deps ) : 'none';
			$in_footer = isset( $wp_scripts->registered[ $handle ]->extra['group'] ) && $wp_scripts->registered[ $handle ]->extra['group'] === 1 ? 'footer' : 'header';
			error_log( sprintf( '[%d] %s | Deps: %s | Location: %s | Src: %s', $index + 1, $handle, $deps, $in_footer, $src ) );
		}
	}

	error_log( '=== End Enqueue Log ===' );
}
add_action( 'wp_print_scripts', 'cas_log_enqueue_order', 9999 );
add_action( 'wp_print_styles', 'cas_log_enqueue_order', 9999 );