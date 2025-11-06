<?php
/**
 * Asset Scanner - Find Unreferenced Theme Assets
 * 
 * Scans theme directory for CSS/JS/image files and checks if they're
 * referenced in enqueues, imports, or code. Generates a report of
 * potentially unused files.
 * 
 * Usage (WP-CLI):
 * wp asset-scan
 * wp asset-scan --type=css
 * wp asset-scan --type=js
 * wp asset-scan --type=images
 * wp asset-scan --verbose
 * 
 * Usage (Admin Page):
 * Navigate to Tools > Asset Scanner
 * 
 * @package HelloElementorChild
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Scanner Class
 */
class CAS_Asset_Scanner {

	/**
	 * Theme directory path
	 * @var string
	 */
	private $theme_dir;

	/**
	 * Theme directory URI
	 * @var string
	 */
	private $theme_uri;

	/**
	 * All theme files (PHP, CSS, JS)
	 * @var array
	 */
	private $source_files = array();

	/**
	 * Asset files found
	 * @var array
	 */
	private $assets = array(
		'css'    => array(),
		'js'     => array(),
		'images' => array(),
	);

	/**
	 * Referenced assets
	 * @var array
	 */
	private $referenced = array(
		'css'    => array(),
		'js'     => array(),
		'images' => array(),
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->theme_dir = get_stylesheet_directory();
		$this->theme_uri = get_stylesheet_directory_uri();
	}

	/**
	 * Run the scan
	 * 
	 * @param array $args Scan arguments (type, verbose)
	 * @return array Scan results
	 */
	public function scan( $args = array() ) {
		$defaults = array(
			'type'    => 'all', // all, css, js, images
			'verbose' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		// Step 1: Find all asset files
		$this->find_assets( $args['type'] );

		// Step 2: Find all source files (PHP, CSS, JS)
		$this->find_source_files();

		// Step 3: Scan source files for references
		$this->scan_references( $args['verbose'] );

		// Step 4: Generate report
		return $this->generate_report( $args['type'] );
	}

	/**
	 * Find all asset files in theme
	 * 
	 * @param string $type Asset type filter
	 */
	private function find_assets( $type = 'all' ) {
		// CSS files
		if ( $type === 'all' || $type === 'css' ) {
			$css_files = $this->glob_recursive( $this->theme_dir . '/assets/**/*.css' );
			foreach ( $css_files as $file ) {
				$this->assets['css'][] = $this->normalize_path( $file );
			}
		}

		// JS files
		if ( $type === 'all' || $type === 'js' ) {
			$js_files = $this->glob_recursive( $this->theme_dir . '/assets/**/*.js' );
			foreach ( $js_files as $file ) {
				$this->assets['js'][] = $this->normalize_path( $file );
			}
		}

		// Image files
		if ( $type === 'all' || $type === 'images' ) {
			$image_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'avif', 'ico' );
			foreach ( $image_extensions as $ext ) {
				$image_files = $this->glob_recursive( $this->theme_dir . '/assets/**/*.' . $ext );
				foreach ( $image_files as $file ) {
					$this->assets['images'][] = $this->normalize_path( $file );
				}
			}
		}
	}

	/**
	 * Find all source files (PHP, CSS, JS) to scan for references
	 */
	private function find_source_files() {
		// PHP files
		$php_files = $this->glob_recursive( $this->theme_dir . '/**/*.php' );
		foreach ( $php_files as $file ) {
			$this->source_files[] = $this->normalize_path( $file );
		}

		// CSS files (for @import statements)
		foreach ( $this->assets['css'] as $file ) {
			$this->source_files[] = $file;
		}

		// JS files (for import statements)
		foreach ( $this->assets['js'] as $file ) {
			$this->source_files[] = $file;
		}
	}

	/**
	 * Scan source files for asset references
	 * 
	 * @param bool $verbose Show progress
	 */
	private function scan_references( $verbose = false ) {
		foreach ( $this->source_files as $source_file ) {
			if ( ! file_exists( $source_file ) ) {
				continue;
			}

			$content = file_get_contents( $source_file );

			// Scan for CSS references
			foreach ( $this->assets['css'] as $css_file ) {
				$basename = basename( $css_file );
				$relative_path = str_replace( $this->theme_dir, '', $css_file );
				
				// Check for wp_enqueue_style, @import, or file path
				if ( 
					strpos( $content, $basename ) !== false ||
					strpos( $content, $relative_path ) !== false
				) {
					if ( ! in_array( $css_file, $this->referenced['css'], true ) ) {
						$this->referenced['css'][] = $css_file;
						if ( $verbose ) {
							echo "✓ Found CSS reference: {$basename} in " . basename( $source_file ) . "\n";
						}
					}
				}
			}

			// Scan for JS references
			foreach ( $this->assets['js'] as $js_file ) {
				$basename = basename( $js_file );
				$relative_path = str_replace( $this->theme_dir, '', $js_file );
				
				// Check for wp_enqueue_script, import, or file path
				if ( 
					strpos( $content, $basename ) !== false ||
					strpos( $content, $relative_path ) !== false
				) {
					if ( ! in_array( $js_file, $this->referenced['js'], true ) ) {
						$this->referenced['js'][] = $js_file;
						if ( $verbose ) {
							echo "✓ Found JS reference: {$basename} in " . basename( $source_file ) . "\n";
						}
					}
				}
			}

			// Scan for image references
			foreach ( $this->assets['images'] as $image_file ) {
				$basename = basename( $image_file );
				$relative_path = str_replace( $this->theme_dir, '', $image_file );
				
				// Check for background-image, <img src, or file path
				if ( 
					strpos( $content, $basename ) !== false ||
					strpos( $content, $relative_path ) !== false
				) {
					if ( ! in_array( $image_file, $this->referenced['images'], true ) ) {
						$this->referenced['images'][] = $image_file;
						if ( $verbose ) {
							echo "✓ Found image reference: {$basename} in " . basename( $source_file ) . "\n";
						}
					}
				}
			}
		}
	}

	/**
	 * Generate report of unreferenced assets
	 * 
	 * @param string $type Asset type filter
	 * @return array Report data
	 */
	private function generate_report( $type = 'all' ) {
		$report = array(
			'total'        => 0,
			'referenced'   => 0,
			'unreferenced' => 0,
			'files'        => array(),
		);

		$types_to_check = $type === 'all' ? array( 'css', 'js', 'images' ) : array( $type );

		foreach ( $types_to_check as $asset_type ) {
			foreach ( $this->assets[ $asset_type ] as $asset_file ) {
				$report['total']++;
				
				$is_referenced = in_array( $asset_file, $this->referenced[ $asset_type ], true );
				
				if ( $is_referenced ) {
					$report['referenced']++;
				} else {
					$report['unreferenced']++;
					$report['files'][] = array(
						'type' => $asset_type,
						'path' => $asset_file,
						'size' => file_exists( $asset_file ) ? filesize( $asset_file ) : 0,
					);
				}
			}
		}

		return $report;
	}

	/**
	 * Recursive glob (supports ** wildcard)
	 * 
	 * @param string $pattern Glob pattern
	 * @return array Matching files
	 */
	private function glob_recursive( $pattern ) {
		$files = array();
		
		// Convert ** to * for simple recursion
		$pattern = str_replace( '**', '*', $pattern );
		
		// Get base directory
		$dir = dirname( $pattern );
		$filename_pattern = basename( $pattern );
		
		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && fnmatch( $filename_pattern, $file->getFilename() ) ) {
				$files[] = $file->getPathname();
			}
		}

		return $files;
	}

	/**
	 * Normalize file path (convert to absolute)
	 * 
	 * @param string $path File path
	 * @return string Normalized path
	 */
	private function normalize_path( $path ) {
		return wp_normalize_path( realpath( $path ) );
	}

	/**
	 * Format bytes to human-readable size
	 * 
	 * @param int $bytes File size in bytes
	 * @return string Formatted size
	 */
	public static function format_bytes( $bytes ) {
		if ( $bytes >= 1073741824 ) {
			return number_format( $bytes / 1073741824, 2 ) . ' GB';
		} elseif ( $bytes >= 1048576 ) {
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
	
	class CAS_Asset_Scanner_CLI extends WP_CLI_Command {

		/**
		 * Scan theme for unreferenced assets
		 * 
		 * ## OPTIONS
		 * 
		 * [--type=<type>]
		 * : Asset type to scan (all, css, js, images)
		 * ---
		 * default: all
		 * options:
		 *   - all
		 *   - css
		 *   - js
		 *   - images
		 * ---
		 * 
		 * [--verbose]
		 * : Show detailed progress
		 * 
		 * ## EXAMPLES
		 * 
		 *     wp asset-scan
		 *     wp asset-scan --type=css
		 *     wp asset-scan --type=js --verbose
		 * 
		 * @param array $args Positional arguments
		 * @param array $assoc_args Named arguments
		 */
		public function __invoke( $args, $assoc_args ) {
			$type = isset( $assoc_args['type'] ) ? $assoc_args['type'] : 'all';
			$verbose = isset( $assoc_args['verbose'] );

			WP_CLI::log( 'Scanning theme assets...' );
			WP_CLI::log( '' );

			$scanner = new CAS_Asset_Scanner();
			$report = $scanner->scan( array(
				'type'    => $type,
				'verbose' => $verbose,
			) );

			// Display summary
			WP_CLI::log( '=== ASSET SCAN REPORT ===' );
			WP_CLI::log( '' );
			WP_CLI::log( "Total assets found:     {$report['total']}" );
			WP_CLI::log( "Referenced assets:      {$report['referenced']}" );
			WP_CLI::log( "Unreferenced assets:    {$report['unreferenced']}" );
			WP_CLI::log( '' );

			// Display unreferenced files
			if ( ! empty( $report['files'] ) ) {
				WP_CLI::log( '=== UNREFERENCED FILES ===' );
				WP_CLI::log( '' );

				$total_size = 0;
				foreach ( $report['files'] as $file ) {
					$relative_path = str_replace( get_stylesheet_directory(), '', $file['path'] );
					$size = CAS_Asset_Scanner::format_bytes( $file['size'] );
					$total_size += $file['size'];
					
					WP_CLI::log( sprintf(
						'[%s] %s (%s)',
						strtoupper( $file['type'] ),
						$relative_path,
						$size
					) );
				}

				WP_CLI::log( '' );
				WP_CLI::log( 'Total size: ' . CAS_Asset_Scanner::format_bytes( $total_size ) );
				WP_CLI::log( '' );
				WP_CLI::warning( 'Review these files before deleting. Some may be referenced dynamically.' );
			} else {
				WP_CLI::success( 'All assets are referenced! ✓' );
			}
		}
	}

	WP_CLI::add_command( 'asset-scan', 'CAS_Asset_Scanner_CLI' );
}

/**
 * Admin Page
 */
function cas_asset_scanner_admin_menu() {
	add_management_page(
		'Asset Scanner',
		'Asset Scanner',
		'manage_options',
		'asset-scanner',
		'cas_asset_scanner_page'
	);
}
add_action( 'admin_menu', 'cas_asset_scanner_admin_menu' );

/**
 * Admin page callback
 */
function cas_asset_scanner_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}

	$report = null;
	if ( isset( $_POST['scan'] ) && check_admin_referer( 'asset_scanner_scan' ) ) {
		$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'all';
		$scanner = new CAS_Asset_Scanner();
		$report = $scanner->scan( array( 'type' => $type ) );
	}

	?>
	<div class="wrap">
		<h1>Asset Scanner</h1>
		<p>Scan theme directory for unreferenced CSS, JS, and image files.</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'asset_scanner_scan' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Asset Type</th>
					<td>
						<select name="type">
							<option value="all">All Assets</option>
							<option value="css">CSS Only</option>
							<option value="js">JavaScript Only</option>
							<option value="images">Images Only</option>
						</select>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Scan Assets', 'primary', 'scan' ); ?>
		</form>

		<?php if ( $report ) : ?>
			<hr>
			<h2>Scan Results</h2>
			<table class="widefat">
				<thead>
					<tr>
						<th>Metric</th>
						<th>Count</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Total Assets</td>
						<td><?php echo esc_html( $report['total'] ); ?></td>
					</tr>
					<tr>
						<td>Referenced Assets</td>
						<td style="color: green;"><?php echo esc_html( $report['referenced'] ); ?></td>
					</tr>
					<tr>
						<td>Unreferenced Assets</td>
						<td style="color: orange;"><?php echo esc_html( $report['unreferenced'] ); ?></td>
					</tr>
				</tbody>
			</table>

			<?php if ( ! empty( $report['files'] ) ) : ?>
				<h3>Unreferenced Files</h3>
				<p><strong>⚠️ Warning:</strong> Review these files carefully before deleting. Some may be referenced dynamically or used in ways the scanner cannot detect.</p>
				<table class="widefat">
					<thead>
						<tr>
							<th>Type</th>
							<th>File Path</th>
							<th>Size</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$total_size = 0;
						foreach ( $report['files'] as $file ) : 
							$relative_path = str_replace( get_stylesheet_directory(), '', $file['path'] );
							$total_size += $file['size'];
						?>
							<tr>
								<td><code><?php echo esc_html( strtoupper( $file['type'] ) ); ?></code></td>
								<td><code><?php echo esc_html( $relative_path ); ?></code></td>
								<td><?php echo esc_html( CAS_Asset_Scanner::format_bytes( $file['size'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
						<tr style="background: #f0f0f0; font-weight: bold;">
							<td colspan="2">Total Size</td>
							<td><?php echo esc_html( CAS_Asset_Scanner::format_bytes( $total_size ) ); ?></td>
						</tr>
					</tbody>
				</table>
			<?php else : ?>
				<p style="color: green; font-weight: bold;">✓ All assets are referenced!</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
