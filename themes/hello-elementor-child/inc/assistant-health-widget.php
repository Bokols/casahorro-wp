<?php
/**
 * Assistant Health Dashboard Widget
 * 
 * Shows module status: CSS/JS enqueued, REST endpoint, consent detection
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

/**
 * Register dashboard widget
 */
function cas_assistant_dashboard_widget() {
	wp_add_dashboard_widget(
		'cas_assistant_health',
		'Assistant Health',
		'cas_assistant_dashboard_widget_render'
	);
}
add_action( 'wp_dashboard_setup', 'cas_assistant_dashboard_widget' );

/**
 * Render widget content
 */
function cas_assistant_dashboard_widget_render() {
	// Check CSS enqueued
	do_action( 'wp_enqueue_scripts' );
	$css_enqueued = wp_style_is( 'hello-elementor-child-assistant', 'registered' );
	
	// Check JS modules enqueued
	$js_handles = array(
		'hello-elementor-child-assistant-helpers',
		'hello-elementor-child-assistant-core',
		'hello-elementor-child-assistant-ui',
		'hello-elementor-child-assistant-api',
		'hello-elementor-child-assistant-boot',
	);
	
	$js_enqueued = true;
	foreach ( $js_handles as $handle ) {
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			$js_enqueued = false;
			break;
		}
	}
	
	// Check REST endpoint
	$rest_url = rest_url( 'cas/v1/assistant' );
	$response = wp_remote_post( $rest_url, array(
		'headers' => array( 'Content-Type' => 'application/json' ),
		'body'    => json_encode( array( 'message' => 'health check' ) ),
		'timeout' => 3,
	) );
	
	$rest_ok = false;
	if ( ! is_wp_error( $response ) ) {
		$status = wp_remote_retrieve_response_code( $response );
		// 403 = nonce required (endpoint exists), 200 = OK
		$rest_ok = in_array( $status, array( 200, 403 ), true );
	}
	
	// Check consent detection
	$consent_working = file_exists( get_stylesheet_directory() . '/assets/js/consent-events.js' );
	
	?>
	<style>
		.cas-health-item { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
		.cas-health-status { font-weight: 600; }
		.cas-health-ok { color: #00a32a; }
		.cas-health-fail { color: #d63638; }
	</style>
	
	<div class="cas-assistant-health">
		<div class="cas-health-item">
			<span class="cas-health-status <?php echo $css_enqueued ? 'cas-health-ok' : 'cas-health-fail'; ?>">
				<?php echo $css_enqueued ? '✅' : '❌'; ?>
			</span>
			<span>CSS enqueued</span>
		</div>
		
		<div class="cas-health-item">
			<span class="cas-health-status <?php echo $js_enqueued ? 'cas-health-ok' : 'cas-health-fail'; ?>">
				<?php echo $js_enqueued ? '✅' : '❌'; ?>
			</span>
			<span>JS modules (<?php echo count( $js_handles ); ?>) enqueued</span>
		</div>
		
		<div class="cas-health-item">
			<span class="cas-health-status <?php echo $rest_ok ? 'cas-health-ok' : 'cas-health-fail'; ?>">
				<?php echo $rest_ok ? '✅' : '❌'; ?>
			</span>
			<span>REST endpoint reachable</span>
		</div>
		
		<div class="cas-health-item">
			<span class="cas-health-status <?php echo $consent_working ? 'cas-health-ok' : 'cas-health-fail'; ?>">
				<?php echo $consent_working ? '✅' : '❌'; ?>
			</span>
			<span>Consent detection available</span>
		</div>
		
		<p style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd; font-size: 12px; color: #646970;">
			<strong>REST URL:</strong> <code><?php echo esc_html( $rest_url ); ?></code>
		</p>
	</div>
	<?php
}
