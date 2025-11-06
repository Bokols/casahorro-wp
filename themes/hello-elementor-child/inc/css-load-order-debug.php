<?php
/**
 * CSS Load Order Debugging Utility
 * 
 * Displays the order of all enqueued stylesheets in an admin notice.
 * Helps verify that interaction-patterns.css loads last.
 * 
 * Usage: Add ?debug_css_order=1 to any frontend URL (requires admin login)
 * 
 * @package HelloElementorChild
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display CSS load order when ?debug_css_order=1 is present
 */
function hello_elementor_child_debug_css_order(): void {
    // Only show to administrators with the debug query parameter
    if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['debug_css_order'] ) ) {
        return;
    }
    
    global $wp_styles;
    
    // Get the actual order of enqueued styles (after dependencies are resolved)
    $styles_output = '';
    $position = 1;
    
    // WordPress resolves dependencies and creates the final queue order
    $to_do = $wp_styles->queue;
    $done = array();
    
    // Process the queue to get final order
    while ( ! empty( $to_do ) ) {
        $handle = array_shift( $to_do );
        
        if ( in_array( $handle, $done ) || ! isset( $wp_styles->registered[ $handle ] ) ) {
            continue;
        }
        
        $style = $wp_styles->registered[ $handle ];
        
        // Check if dependencies are met
        $deps = (array) $style->deps;
        $deps_met = true;
        
        foreach ( $deps as $dep ) {
            if ( ! in_array( $dep, $done ) ) {
                $deps_met = false;
                break;
            }
        }
        
        if ( ! $deps_met ) {
            // Re-add to end of queue if dependencies not met
            $to_do[] = $handle;
            continue;
        }
        
        // Add to done list
        $done[] = $handle;
        
        // Get the source URL
        $src = $style->src;
        if ( ! preg_match( '|^https?://|', $src ) && ! empty( $wp_styles->base_url ) ) {
            $src = $wp_styles->base_url . $src;
        }
        
        // Highlight our theme styles
        $highlight = '';
        if ( strpos( $handle, 'hello-elementor-child-interaction-patterns' ) !== false ) {
            $highlight = 'background: #c9e5ea; font-weight: bold; padding: 2px 4px;';
        } elseif ( strpos( $handle, 'hello-elementor-child-color-guard' ) !== false ) {
            $highlight = 'background: #d9e8db; font-weight: bold; padding: 2px 4px;';
        } elseif ( strpos( $handle, 'hello-elementor-child' ) !== false ) {
            $highlight = 'background: #f8fafc; padding: 2px 4px;';
        } elseif ( strpos( $handle, 'elementor' ) !== false ) {
            $highlight = 'background: #f2ddda; padding: 2px 4px;';
        } elseif ( strpos( $handle, 'woocommerce' ) !== false ) {
            $highlight = 'background: #cec7ee; padding: 2px 4px;';
        }
        
        // Extract filename from URL for cleaner display
        $filename = basename( parse_url( $src, PHP_URL_PATH ) );
        
        // Show dependencies
        $deps_display = ! empty( $deps ) ? ' → deps: ' . implode( ', ', $deps ) : '';
        
        $styles_output .= sprintf(
            '<div style="%s">%d. <strong>%s</strong> (%s)%s</div>',
            $highlight,
            $position,
            esc_html( $handle ),
            esc_html( $filename ),
            esc_html( $deps_display )
        );
        
        $position++;
    }
    
    // Find position of our key styles
    $interaction_patterns_pos = array_search( 'hello-elementor-child-interaction-patterns', $done );
    $color_guard_pos = array_search( 'hello-elementor-child-color-guard', $done );
    
    // Check for Elementor kit CSS after our styles
    $elementor_after_ours = array();
    foreach ( $done as $pos => $handle ) {
        if ( $pos > $color_guard_pos && 
             ( strpos( $handle, 'elementor-post-' ) !== false || 
               strpos( $handle, 'elementor-kit-' ) !== false ) ) {
            $elementor_after_ours[] = $handle;
        }
    }
    
    // Determine status
    $status = '✅ CORRECT';
    $status_color = '#d9e8db';
    $message = 'CSS load order is correct. interaction-patterns.css and color-guard.css load last.';
    
    if ( ! empty( $elementor_after_ours ) ) {
        $status = '⚠️ ISSUE DETECTED';
        $status_color = '#f2ddda';
        $message = 'Elementor kit CSS (' . implode( ', ', $elementor_after_ours ) . ') loads AFTER our override styles. This may cause specificity issues.';
    }
    
    // Output admin notice
    echo '<div class="notice notice-info" style="padding: 20px; background: #fff; border-left: 4px solid #1E293B; margin: 20px 0;">';
    echo '<h2 style="margin-top: 0;">🎨 CSS Load Order Debug</h2>';
    echo '<div style="background: ' . esc_attr( $status_color ) . '; padding: 10px; margin-bottom: 15px; border-radius: 4px;">';
    echo '<strong>' . esc_html( $status ) . ':</strong> ' . esc_html( $message );
    echo '</div>';
    echo '<div style="margin-bottom: 15px;">';
    echo '<strong>Legend:</strong> ';
    echo '<span style="background: #c9e5ea; padding: 2px 4px; margin-right: 8px;">Interaction Patterns</span>';
    echo '<span style="background: #d9e8db; padding: 2px 4px; margin-right: 8px;">Color Guard</span>';
    echo '<span style="background: #f8fafc; padding: 2px 4px; margin-right: 8px;">Theme CSS</span>';
    echo '<span style="background: #f2ddda; padding: 2px 4px; margin-right: 8px;">Elementor</span>';
    echo '<span style="background: #cec7ee; padding: 2px 4px;">WooCommerce</span>';
    echo '</div>';
    echo '<div style="font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto; border: 1px solid #e2e8f0; padding: 10px; background: #f8fafc;">';
    echo $styles_output;
    echo '</div>';
    echo '<p style="margin-bottom: 0;"><strong>Position:</strong> interaction-patterns.css = #' . ( $interaction_patterns_pos + 1 ) . ', color-guard.css = #' . ( $color_guard_pos + 1 ) . ' (out of ' . count( $done ) . ' total stylesheets)</p>';
    echo '</div>';
}
add_action( 'wp_footer', 'hello_elementor_child_debug_css_order', 9999 );
