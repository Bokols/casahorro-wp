<?php
/**
 * Assistant Admin Bar Toggle
 * 
 * Adds a toggle to admin bar for quick access (admins only)
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

/**
 * Add Assistant toggle to admin bar
 */
function cas_assistant_admin_bar_toggle( $wp_admin_bar ) {
	// Only for admins
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$wp_admin_bar->add_node( array(
		'id'    => 'cas-assistant-toggle',
		'title' => '🤖 Assistant',
		'href'  => '#',
		'meta'  => array(
			'class' => 'cas-assistant-admin-toggle',
		),
	) );
}
add_action( 'admin_bar_menu', 'cas_assistant_admin_bar_toggle', 999 );

/**
 * Add inline script to trigger assistant on admin bar click
 */
function cas_assistant_admin_bar_script() {
	// Only for admins
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		var toggle = document.querySelector('.cas-assistant-admin-toggle');
		if (toggle) {
			toggle.addEventListener('click', function(e) {
				e.preventDefault();
				if (window.casAssistant && typeof window.casAssistant.open === 'function') {
					window.casAssistant.open('admin-bar');
				} else {
					console.warn('Assistant not loaded yet. Wait a moment and try again.');
				}
			});
		}
	});
	</script>
	<?php
}
add_action( 'wp_footer', 'cas_assistant_admin_bar_script' );
add_action( 'admin_footer', 'cas_assistant_admin_bar_script' );
