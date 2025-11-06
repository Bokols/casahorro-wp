<?php
/**
 * Primary navigation partial
 *
 * Renders the primary navigation menu if assigned. Provides a subtle
 * fallback message when the menu location is not configured.
 *
 * PHP version 8.2
 *
 * @package Hello_Elementor_Child
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Get product categories for category picker
$product_categories = get_terms( array(
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'parent'     => 0, // Only top-level categories
) );
?>
<nav id="cas-nav-primary" class="nav-primary" role="navigation" aria-label="Principal">
    <div class="nav-primary__menu">
        <?php if ( has_nav_menu( 'primary' ) ) :
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'menu menu-primary',
                'depth'          => 2,
            ) );
        else : ?>
            <!-- 
                DESKTOP & MOBILE NAVIGATION - Unified menu structure:
                Items: Productos (/productos) · Comparar (/comparar) · Sobre (/sobre) · Contacto (/contacto)
                Order: Specific order maintained, "Inicio" removed as requested
                
                Visual States (same for desktop & mobile):
                - Default: C1 ink #1E293B (WCAG AA >7:1 contrast)
                - Hover: underline only (no background)
                - Focus: 3px outline + visible focus ring (currentColor)
                - Active: underline + C1 ink-700 #475569 (WCAG AA 4.5:1+ contrast)
            -->
            <ul class="menu menu-primary">
                <li class="menu-item">
                    <a href="/productos" <?php echo ( is_shop() || is_product_category() || is_product_taxonomy() ) ? 'aria-current="page"' : ''; ?>>
                        <?php echo esc_html__( 'Productos', 'hello-elementor-child' ); ?>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="/comparar" <?php echo ( is_page( 'comparar' ) ) ? 'aria-current="page"' : ''; ?>>
                        <?php echo esc_html__( 'Comparar', 'hello-elementor-child' ); ?>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="/sobre" <?php echo ( is_page( 'sobre' ) ) ? 'aria-current="page"' : ''; ?>>
                        <?php echo esc_html__( 'Sobre', 'hello-elementor-child' ); ?>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="/contacto" <?php echo ( is_page( 'contacto' ) ) ? 'aria-current="page"' : ''; ?>>
                        <?php echo esc_html__( 'Contacto', 'hello-elementor-child' ); ?>
                    </a>
                </li>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Utility Navigation for Mobile (inside overlay) -->
    <div class="nav-primary__utility">
        <?php if ( has_nav_menu( 'utility' ) ) : ?>
            <nav class="nav-utility nav-utility--mobile" role="navigation" aria-label="Utilidad">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'utility',
                    'container'      => false,
                    'menu_class'     => 'menu menu-utility',
                    'depth'          => 1,
                ) );
                ?>
            </nav>
        <?php endif; ?>
    </div>

    <div class="nav-primary__tools">
        <?php
        /**
         * ═══════════════════════════════════════════════════════════
         * PHASE-2 SCOPE GUARD: SEARCH UI - FULLY DISABLED
         * ═══════════════════════════════════════════════════════════
         * 
         * Search with category dropdown is OUT OF SCOPE for Phase-2.
         * 
         * Guardrail: `if ( false )` ensures search block is NEVER rendered.
         * CSS backup: `.nav-search` has display:none + visibility:hidden.
         * 
         * Header/ribbon sizing intact: 82px + 22.09px = 104.09px (no CLS).
         * 
         * Re-enable: Change `if ( false )` to `if ( true )` in future phase.
         * Dependencies: header.css scope guards, C1 neutral tokens.
         * 
         * Status: ✅ LOCKED for Phase-2
         */
        if ( false ) : // Phase-2 guard: DO NOT CHANGE without scope approval
        ?>
        <!-- Search with Category -->
        <div class="nav-search">
            <form role="search" method="get" class="nav-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <div class="nav-search__wrapper">
                    <!-- Category selector -->
                    <select 
                        name="product_cat" 
                        class="nav-search__category"
                        aria-label="<?php esc_attr_e( 'Categoría', 'hello-elementor-child' ); ?>"
                    >
                        <option value=""><?php esc_html_e( 'Todas', 'hello-elementor-child' ); ?></option>
                        <?php if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) :
                            foreach ( $product_categories as $category ) : ?>
                                <option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( get_query_var( 'product_cat' ), $category->slug ); ?>>
                                    <?php echo esc_html( $category->name ); ?>
                                </option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                    
                    <!-- Search input -->
                    <input 
                        type="search" 
                        class="nav-search__input" 
                        placeholder="<?php esc_attr_e( 'Buscar productos...', 'hello-elementor-child' ); ?>" 
                        value="<?php echo get_search_query(); ?>" 
                        name="s"
                        aria-label="<?php esc_attr_e( 'Buscar', 'hello-elementor-child' ); ?>"
                    >
                    <input type="hidden" name="post_type" value="product">
                    
                    <!-- Search button -->
                    <button type="submit" class="nav-search__button" aria-label="<?php esc_attr_e( 'Enviar búsqueda', 'hello-elementor-child' ); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                            <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; // End Phase-2 guard: Search UI ?>

        <?php
        /**
         * ═══════════════════════════════════════════════════════════
         * PHASE-2 SCOPE GUARD: INLINE COMPARE - FULLY DISABLED
         * ═══════════════════════════════════════════════════════════
         * 
         * Inline header compare button is OUT OF SCOPE for Phase-2.
         * Only the global FAB (#cas-compare-fab) is visible.
         * 
         * Guardrail: `if ( false )` ensures compare block is NEVER rendered.
         * CSS backup: `.nav-compare` has display:none + visibility:hidden.
         * 
         * Z-stack preserved:
         * - FAB: z-index 1000 (bottom-right, always visible)
         * - Header: z-index 900 (below FAB, no overlap)
         * - Safe areas respected (drawer.css handles padding)
         * 
         * Re-enable: Change `if ( false )` to `if ( true )` if inline mode needed.
         * Dependencies: header.css scope guards, FAB component.
         * 
         * Status: ✅ LOCKED for Phase-2 (FAB-only mode)
         */
        if ( false ) : // Phase-2 guard: DO NOT CHANGE without scope approval
        ?>
        <!-- Compare FAB Icon -->
        <button 
            type="button"
            class="nav-compare"
            aria-label="<?php esc_attr_e( 'Comparar productos', 'hello-elementor-child' ); ?>"
            data-nav-compare
            data-compare-open
        >
            <!-- Left icon -->
            <svg class="nav-compare__arrow nav-compare__arrow--left" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M3.333 4.167h5M3.333 10h5M3.333 15.833h5M11.667 4.167h5M11.667 10h5M11.667 15.833h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            
            <!-- Text -->
            <span class="nav-compare__label"><?php esc_html_e( 'Comparar', 'hello-elementor-child' ); ?></span>
            
            <!-- Circle background -->
            <span class="nav-compare__circle"></span>
            
            <!-- Right icon -->
            <svg class="nav-compare__arrow nav-compare__arrow--right" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M3.333 4.167h5M3.333 10h5M3.333 15.833h5M11.667 4.167h5M11.667 10h5M11.667 15.833h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            
            <!-- Badge -->
            <span class="nav-compare__badge" data-compare-badge aria-live="polite" aria-atomic="true"></span>
        </button>
        <?php endif; // End Phase-2 guard: Inline compare ?>
    </div>
</nav>
