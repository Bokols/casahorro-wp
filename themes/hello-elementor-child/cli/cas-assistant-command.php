<?php
/**
 * WP-CLI command for Assistant module diagnostics
 * 
 * Usage: wp cas-assistant status
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

class CAS_Assistant_CLI_Command {
    
    /**
     * Check assistant module enqueue and REST endpoint status
     * 
     * ## EXAMPLES
     * 
     *     wp cas-assistant status
     * 
     * @when after_wp_load
     */
    public function status( $args, $assoc_args ) {
        WP_CLI::line( 'Checking Assistant module status...' );
        WP_CLI::line( '' );
        
        // Simulate front page enqueue
        WP_CLI::line( '📦 Enqueued Assets:' );
        
        global $wp_styles, $wp_scripts;
        
        // Reset query for front page
        query_posts( array( 'page_id' => get_option( 'page_on_front' ) ) );
        do_action( 'wp_enqueue_scripts' );
        
        // Check CSS
        $css_handle = 'hello-elementor-child-assistant';
        $css_enqueued = wp_style_is( $css_handle, 'enqueued' );
        WP_CLI::line( sprintf(
            '  %s assistant.css (%s)',
            $css_enqueued ? '✅' : '❌',
            $css_handle
        ) );
        
        // Check JS modules
        $js_handles = array(
            'hello-elementor-child-assistant-helpers',
            'hello-elementor-child-assistant-core',
            'hello-elementor-child-assistant-ui',
            'hello-elementor-child-assistant-api',
            'hello-elementor-child-assistant-boot',
        );
        
        foreach ( $js_handles as $handle ) {
            $enqueued = wp_script_is( $handle, 'enqueued' );
            $basename = str_replace( 'hello-elementor-child-assistant-', '', $handle );
            WP_CLI::line( sprintf(
                '  %s %s.js (%s)',
                $enqueued ? '✅' : '❌',
                $basename,
                $handle
            ) );
        }
        
        wp_reset_query();
        
        // Check REST endpoint
        WP_CLI::line( '' );
        WP_CLI::line( '🌐 REST Endpoint:' );
        
        $rest_url = rest_url( 'cas/v1/assistant' );
        WP_CLI::line( sprintf( '  URL: %s', $rest_url ) );
        
        $response = wp_remote_post( $rest_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode( array( 'message' => 'test' ) ),
            'timeout' => 5,
        ) );
        
        if ( is_wp_error( $response ) ) {
            WP_CLI::line( sprintf( '  ❌ Error: %s', $response->get_error_message() ) );
        } else {
            $status_code = wp_remote_retrieve_response_code( $response );
            $success = in_array( $status_code, array( 200, 403 ), true ); // 403 = needs nonce, but route exists
            WP_CLI::line( sprintf(
                '  %s HTTP %d',
                $success ? '✅' : '❌',
                $status_code
            ) );
        }
        
        WP_CLI::line( '' );
        WP_CLI::success( 'Status check complete.' );
    }
}

WP_CLI::add_command( 'cas-assistant', 'CAS_Assistant_CLI_Command' );
