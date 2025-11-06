<?php
/**
 * Image Optimization Scanner - Find Large PNG/JPG Files
 * 
 * Scans theme directory for PNG and JPG files larger than 150KB
 * and suggests converting to WebP/AVIF for better performance.
 * 
 * Usage (WP-CLI):
 * wp image-scan
 * wp image-scan --threshold=200
 * wp image-scan --format=table
 * 
 * Usage (Admin Page):
 * Navigate to Tools > Image Optimizer
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimization Scanner Class
 */
class CAS_Image_Optimizer_Scanner {

	/**
	 * Theme directory path
	 * @var string
	 */
	private $theme_dir;

	/**
	 * File size threshold (bytes)
	 * @var int
	 */
	private $threshold = 153600; // 150KB default

	/**
	 * Constructor
	 * 
	 * @param int $threshold_kb Size threshold in KB (default: 150)
	 */
	public function __construct( $threshold_kb = 150 ) {
		$this->theme_dir = get_stylesheet_directory();
		$this->threshold = $threshold_kb * 1024;
	}

	/**
	 * Scan for large PNG/JPG files
	 * 
	 * @return array Scan results
	 */
	public function scan() {
		$results = array(
			'total_files'      => 0,
			'large_files'      => 0,
			'total_size'       => 0,
			'potential_savings' => 0,
			'files'            => array(),
		);

		// Find PNG files
		$png_files = $this->find_images( 'png' );
		foreach ( $png_files as $file ) {
			$size = filesize( $file );
			$results['total_files']++;
			$results['total_size'] += $size;

			if ( $size > $this->threshold ) {
				$results['large_files']++;
				$results['files'][] = $this->analyze_file( $file, $size );
			}
		}

		// Find JPG files
		$jpg_extensions = array( 'jpg', 'jpeg' );
		foreach ( $jpg_extensions as $ext ) {
			$jpg_files = $this->find_images( $ext );
			foreach ( $jpg_files as $file ) {
				$size = filesize( $file );
				$results['total_files']++;
				$results['total_size'] += $size;

				if ( $size > $this->threshold ) {
					$results['large_files']++;
					$results['files'][] = $this->analyze_file( $file, $size );
				}
			}
		}

		// Calculate potential savings (WebP: ~25-35% smaller, AVIF: ~50% smaller)
		foreach ( $results['files'] as $file ) {
			$results['potential_savings'] += $file['estimated_webp_savings'] + $file['estimated_avif_savings'];
		}

		// Sort by size (largest first)
		usort( $results['files'], function( $a, $b ) {
			return $b['size'] - $a['size'];
		});

		return $results;
	}

	/**
	 * Find image files by extension
	 * 
	 * @param string $extension File extension (png, jpg, jpeg)
	 * @return array File paths
	 */
	private function find_images( $extension ) {
		$files = array();
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $this->theme_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && strtolower( $file->getExtension() ) === strtolower( $extension ) ) {
				$files[] = $file->getPathname();
			}
		}

		return $files;
	}

	/**
	 * Analyze image file and calculate potential savings
	 * 
	 * @param string $file File path
	 * @param int    $size File size in bytes
	 * @return array File analysis
	 */
	private function analyze_file( $file, $size ) {
		$relative_path = str_replace( $this->theme_dir, '', wp_normalize_path( $file ) );
		$extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );

		// Get image dimensions
		$dimensions = @getimagesize( $file );
		$width = $dimensions ? $dimensions[0] : 0;
		$height = $dimensions ? $dimensions[1] : 0;

		// Estimate savings (conservative estimates)
		// WebP: 25-35% smaller than PNG, 20-30% smaller than JPG
		// AVIF: 40-50% smaller than PNG, 30-40% smaller than JPG
		$webp_reduction = $extension === 'png' ? 0.30 : 0.25; // 30% for PNG, 25% for JPG
		$avif_reduction = $extension === 'png' ? 0.45 : 0.35; // 45% for PNG, 35% for JPG

		$webp_size = $size * ( 1 - $webp_reduction );
		$avif_size = $size * ( 1 - $avif_reduction );

		return array(
			'path'                    => $relative_path,
			'absolute_path'           => wp_normalize_path( $file ),
			'extension'               => strtoupper( $extension ),
			'size'                    => $size,
			'size_formatted'          => $this->format_bytes( $size ),
			'width'                   => $width,
			'height'                  => $height,
			'dimensions'              => $width && $height ? "{$width}×{$height}" : 'Unknown',
			'estimated_webp_size'     => $webp_size,
			'estimated_webp_formatted' => $this->format_bytes( $webp_size ),
			'estimated_webp_savings'  => $size - $webp_size,
			'webp_savings_formatted'  => $this->format_bytes( $size - $webp_size ),
			'webp_savings_percent'    => round( $webp_reduction * 100 ),
			'estimated_avif_size'     => $avif_size,
			'estimated_avif_formatted' => $this->format_bytes( $avif_size ),
			'estimated_avif_savings'  => $size - $avif_size,
			'avif_savings_formatted'  => $this->format_bytes( $size - $avif_size ),
			'avif_savings_percent'    => round( $avif_reduction * 100 ),
		);
	}

	/**
	 * Format bytes to human-readable size
	 * 
	 * @param int $bytes File size in bytes
	 * @return string Formatted size
	 */
	private function format_bytes( $bytes ) {
		if ( $bytes >= 1048576 ) {
			return number_format( $bytes / 1048576, 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			return number_format( $bytes / 1024, 2 ) . ' KB';
		} else {
			return $bytes . ' bytes';
		}
	}
}

/**
 * WP-CLI Command
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	
	class CAS_Image_Optimizer_CLI extends WP_CLI_Command {

		/**
		 * Scan theme for large PNG/JPG files
		 * 
		 * ## OPTIONS
		 * 
		 * [--threshold=<kb>]
		 * : Size threshold in KB (default: 150)
		 * ---
		 * default: 150
		 * ---
		 * 
		 * [--format=<format>]
		 * : Output format
		 * ---
		 * default: list
		 * options:
		 *   - list
		 *   - table
		 *   - json
		 * ---
		 * 
		 * ## EXAMPLES
		 * 
		 *     wp image-scan
		 *     wp image-scan --threshold=200
		 *     wp image-scan --format=table
		 * 
		 * @param array $args Positional arguments
		 * @param array $assoc_args Named arguments
		 */
		public function __invoke( $args, $assoc_args ) {
			$threshold = isset( $assoc_args['threshold'] ) ? intval( $assoc_args['threshold'] ) : 150;
			$format = isset( $assoc_args['format'] ) ? $assoc_args['format'] : 'list';

			WP_CLI::log( "Scanning for PNG/JPG files larger than {$threshold}KB..." );
			WP_CLI::log( '' );

			$scanner = new CAS_Image_Optimizer_Scanner( $threshold );
			$results = $scanner->scan();

			if ( $format === 'json' ) {
				WP_CLI::log( wp_json_encode( $results, JSON_PRETTY_PRINT ) );
				return;
			}

			// Display summary
			WP_CLI::log( '=== IMAGE OPTIMIZATION REPORT ===' );
			WP_CLI::log( '' );
			WP_CLI::log( "Total PNG/JPG files:        {$results['total_files']}" );
			WP_CLI::log( "Large files (>{$threshold}KB):    {$results['large_files']}" );
			WP_CLI::log( 'Total size:                ' . ( new CAS_Image_Optimizer_Scanner() )->format_bytes( $results['total_size'] ) );
			WP_CLI::log( '' );

			if ( empty( $results['files'] ) ) {
				WP_CLI::success( "No PNG/JPG files larger than {$threshold}KB found! ✓" );
				return;
			}

			if ( $format === 'table' ) {
				$table_data = array();
				foreach ( $results['files'] as $file ) {
					$table_data[] = array(
						'Path'       => $file['path'],
						'Type'       => $file['extension'],
						'Size'       => $file['size_formatted'],
						'Dimensions' => $file['dimensions'],
						'WebP Size'  => $file['estimated_webp_formatted'] . ' (-' . $file['webp_savings_percent'] . '%)',
						'AVIF Size'  => $file['estimated_avif_formatted'] . ' (-' . $file['avif_savings_percent'] . '%)',
					);
				}
				WP_CLI\Utils\format_items( 'table', $table_data, array( 'Path', 'Type', 'Size', 'Dimensions', 'WebP Size', 'AVIF Size' ) );
			} else {
				WP_CLI::log( '=== LARGE FILES ===' );
				WP_CLI::log( '' );

				foreach ( $results['files'] as $file ) {
					WP_CLI::log( WP_CLI::colorize( "%C{$file['path']}%n" ) );
					WP_CLI::log( "  Type:       {$file['extension']}" );
					WP_CLI::log( "  Size:       {$file['size_formatted']}" );
					WP_CLI::log( "  Dimensions: {$file['dimensions']}" );
					WP_CLI::log( '' );
					WP_CLI::log( '  Conversion Estimates:' );
					WP_CLI::log( "    WebP: {$file['estimated_webp_formatted']} (saves {$file['webp_savings_formatted']}, -{$file['webp_savings_percent']}%)" );
					WP_CLI::log( "    AVIF: {$file['estimated_avif_formatted']} (saves {$file['avif_savings_formatted']}, -{$file['avif_savings_percent']}%)" );
					WP_CLI::log( '' );
				}
			}

			WP_CLI::log( '=== RECOMMENDATIONS ===' );
			WP_CLI::log( '' );
			WP_CLI::log( '1. Convert to WebP for ~25-35% size reduction (better browser support)' );
			WP_CLI::log( '2. Convert to AVIF for ~40-50% size reduction (best compression, modern browsers)' );
			WP_CLI::log( '3. Use <picture> element with multiple formats for fallback:' );
			WP_CLI::log( '' );
			WP_CLI::log( '   <picture>' );
			WP_CLI::log( '     <source srcset="image.avif" type="image/avif">' );
			WP_CLI::log( '     <source srcset="image.webp" type="image/webp">' );
			WP_CLI::log( '     <img src="image.jpg" alt="Description">' );
			WP_CLI::log( '   </picture>' );
			WP_CLI::log( '' );
			WP_CLI::log( '4. Use image optimization tools:' );
			WP_CLI::log( '   - ImageMagick: convert image.png -quality 85 image.webp' );
			WP_CLI::log( '   - cwebp: cwebp -q 85 image.png -o image.webp' );
			WP_CLI::log( '   - avifenc: avifenc --min 20 --max 30 image.png image.avif' );
		}
	}

	WP_CLI::add_command( 'image-scan', 'CAS_Image_Optimizer_CLI' );
}

/**
 * Admin Page
 */
function cas_image_optimizer_admin_menu() {
	add_management_page(
		'Image Optimizer',
		'Image Optimizer',
		'manage_options',
		'image-optimizer',
		'cas_image_optimizer_page'
	);
}
add_action( 'admin_menu', 'cas_image_optimizer_admin_menu' );

/**
 * Admin page callback
 */
function cas_image_optimizer_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}

	$results = null;
	if ( isset( $_POST['scan'] ) && check_admin_referer( 'image_optimizer_scan' ) ) {
		$threshold = isset( $_POST['threshold'] ) ? intval( $_POST['threshold'] ) : 150;
		$scanner = new CAS_Image_Optimizer_Scanner( $threshold );
		$results = $scanner->scan();
	}

	?>
	<div class="wrap">
		<h1>🖼️ Image Optimization Scanner</h1>
		<p>Find large PNG and JPG files in the theme directory and get conversion recommendations.</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'image_optimizer_scan' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Size Threshold</th>
					<td>
						<input type="number" name="threshold" value="150" min="1" max="10000" step="1">
						<span class="description">KB (files larger than this will be reported)</span>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Scan Images', 'primary', 'scan' ); ?>
		</form>

		<?php if ( $results ) : ?>
			<hr>
			<h2>Scan Results</h2>
			
			<div class="notice notice-info inline">
				<p>
					<strong>Summary:</strong> 
					Found <?php echo esc_html( $results['large_files'] ); ?> large file(s) 
					out of <?php echo esc_html( $results['total_files'] ); ?> total PNG/JPG images.
				</p>
			</div>

			<?php if ( empty( $results['files'] ) ) : ?>
				<p style="color: green; font-weight: bold;">✓ No large PNG/JPG files found! Your images are already optimized.</p>
			<?php else : ?>
				<h3>Large Files (Candidates for Optimization)</h3>
				
				<table class="widefat striped">
					<thead>
						<tr>
							<th>File Path</th>
							<th>Type</th>
							<th>Current Size</th>
							<th>Dimensions</th>
							<th>WebP Estimate</th>
							<th>AVIF Estimate</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $results['files'] as $file ) : ?>
							<tr>
								<td><code><?php echo esc_html( $file['path'] ); ?></code></td>
								<td><strong><?php echo esc_html( $file['extension'] ); ?></strong></td>
								<td><?php echo esc_html( $file['size_formatted'] ); ?></td>
								<td><?php echo esc_html( $file['dimensions'] ); ?></td>
								<td>
									<?php echo esc_html( $file['estimated_webp_formatted'] ); ?>
									<br>
									<small style="color: green;">
										↓ <?php echo esc_html( $file['webp_savings_formatted'] ); ?> 
										(<?php echo esc_html( $file['webp_savings_percent'] ); ?>%)
									</small>
								</td>
								<td>
									<?php echo esc_html( $file['estimated_avif_formatted'] ); ?>
									<br>
									<small style="color: green;">
										↓ <?php echo esc_html( $file['avif_savings_formatted'] ); ?> 
										(<?php echo esc_html( $file['avif_savings_percent'] ); ?>%)
									</small>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class="notice notice-warning inline" style="margin-top: 20px;">
					<h3>📋 Conversion Recommendations</h3>
					<ol>
						<li><strong>WebP:</strong> Good compression (~25-35% smaller), excellent browser support (95%+ browsers)</li>
						<li><strong>AVIF:</strong> Best compression (~40-50% smaller), modern browsers only (Chrome 85+, Firefox 93+)</li>
						<li><strong>Strategy:</strong> Use both formats with fallback for maximum compatibility and performance</li>
					</ol>

					<h4>Example HTML (Responsive Images with Fallback):</h4>
					<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;"><code>&lt;picture&gt;
  &lt;source srcset="image.avif" type="image/avif"&gt;
  &lt;source srcset="image.webp" type="image/webp"&gt;
  &lt;img src="image.jpg" alt="Description" loading="lazy"&gt;
&lt;/picture&gt;</code></pre>

					<h4>Conversion Tools:</h4>
					<ul>
						<li><strong>ImageMagick:</strong> <code>convert image.png -quality 85 image.webp</code></li>
						<li><strong>cwebp:</strong> <code>cwebp -q 85 image.png -o image.webp</code></li>
						<li><strong>avifenc:</strong> <code>avifenc --min 20 --max 30 image.png image.avif</code></li>
						<li><strong>Squoosh (GUI):</strong> <a href="https://squoosh.app" target="_blank">https://squoosh.app</a></li>
					</ul>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<style>
		.widefat code {
			font-size: 12px;
			background: #f0f0f0;
			padding: 2px 6px;
			border-radius: 3px;
		}
	</style>
	<?php
}
