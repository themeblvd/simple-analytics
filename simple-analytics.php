<?php
/**
 * Plugin Name: Simple Analytics
 * Description: A simple plugin to include your Google Analytics tracking.
 * Version: 1.1.1
 * Author: Theme Blvd
 * Author URI: http://themeblvd.com
 * License: GPL2
 *
 * Copyright 2017  Theme Blvd
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * You may NOT assume that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * The license for this software can likely be found here:
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Simple Analytics
 */

define( 'TB_SIMPLE_ANALYTICS_PLUGIN_VERSION', '1.1.1' );
define( 'TB_SIMPLE_ANALYTICS_TWEEPLE_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_SIMPLE_ANALYTICS_PLUGIN_URI', plugins_url( '' , __FILE__ ) );
define( 'TB_SIMPLE_ANALYTICS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Setup plugin.
 */
class Theme_Blvd_Simple_Analytics {

	/**
	 * Only instance of object.
	 *
	 * @var Theme_Blvd_Simple_Analytics
	 */
	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return  Theme_Blvd_Simple_Analytics A single instance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

	/**
	 * Initiate plugin.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {

		// Output Analytics.
		if ( ! is_admin() ) {

			add_action( 'after_setup_theme', array( $this, 'add_output' ) );

		}

		// Settings page.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

	}

	/**
	 * Hook in output to cofigured action
	 *
	 * @since 1.0.2
	 */
	public function add_output() {

		$analytics = get_option( 'themeblvd_analytics' );

		if ( $analytics && isset( $analytics['placement'] ) ) {

			if ( defined( 'TB_FRAMEWORK_VERSION' ) && 'body' === $analytics['placement'] ) {

				add_action( 'themeblvd_before', array( $this, 'output' ), 2 );

			} elseif ( 'foot' === $analytics['placement'] ) {

				add_action( 'wp_footer', array( $this, 'output' ), 1000 );

			} else {

				add_action( 'wp_head', array( $this, 'output' ), 2 );

			}
		}
	}

	/**
	 * Output analytics.
	 *
	 * @since 1.0.0
	 */
	public function output() {

		$settings = get_option( 'themeblvd_analytics' );

		if ( ! empty( $settings['google_id'] ) ) {

			$google_id = esc_attr( $settings['google_id'] );

			// Generate ga() JS code.
			$ga = "ga('create', '{$google_id}', 'auto');\n";

			if ( ! empty( $settings['anonymize'] ) ) {

				$ga .= "\tga('set', 'anonymizeIp', true);\n";

			}

			$ga .= "\tga('send', 'pageview');\n";

			// Start output.
			echo "<!-- Simple Analytics by Theme Blvd -->\n";

			if ( current_user_can( 'edit_theme_options' ) ) {

				echo '<!-- ' . esc_html__( 'Analytics code commented out because you are logged in as an admin.', 'simple-analytics' ) . "\n";

			}
?>
<script>

	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	<?php echo wp_kses( $ga, array() ); ?>

</script>
<?php
			if ( current_user_can( 'edit_theme_options' ) ) {

				echo "-->\n";

			}
		}
	}

	/**
	 * Add settings page.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {

		add_options_page(
			__( 'Analytics', 'simple-analytics' ),
			__( 'Analytics', 'simple-analytics' ),
			'edit_theme_options',
			'simple-analytics',
			array( $this, 'settings_page' )
		);

	}

	/**
	 * Register settings.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {

		register_setting(
			'themeblvd_analytics',
			'themeblvd_analytics',
			array( $this, 'sanitize' )
		);

	}

	/**
	 * Sanitization callback for saving settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Data from settings page to sanitized.
	 * @return array $output Data from settings page after sanitization.
	 */
	public function sanitize( $input ) {

		global $allowedposttags;

		$allowed_tags = array_merge( $allowedposttags, array( 'script' => array( 'type' => true, 'src' => true ) ) );

		$output = array(
			'google_id'	=> '',
			'placement'	=> 'body',
			'anonymize' => false,
		);

		foreach ( $input as $key => $value ) {

			switch ( $key ) {

				case 'google_id' :

					$output[ $key ] = esc_attr( $value );

					break;

				case 'placement' :

					$choices = array( 'head', 'body', 'foot' );

					if ( in_array( $value, $choices, true ) ) {

						$output[ $key ] = $value;

					}

					break;

				case 'anonymize' :

					if ( '1' === $value ) {

						$output[ $key ] = true;

					}

					break;

			}
		}

		return $output;

	}

	/**
	 * Display settings page.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {

		$settings = get_option( 'themeblvd_analytics' );

		$code = '';

		if ( isset( $settings['google_id'] ) ) {

			$code = $settings['google_id'];

		}

		$placement = 'body';

		if ( isset( $settings['placement'] ) ) {

			$placement = $settings['placement'];

		}

		if ( 'body' === $placement && ! defined( 'TB_FRAMEWORK_VERSION' ) ) {

			$placement = 'head';

		}

		$anonymize = false;

		if ( isset( $settings['anonymize'] ) ) {

			$anonymize = $settings['anonymize'];

		}
		?>
		<div class="wrap">

			<?php settings_errors( 'themeblvd_analytics' ); ?>

			<div id="icon-options-general" class="icon32"><br></div>

			<h2><?php esc_html_e( 'Analytics', 'simple-analytics' ); ?></h2>

			<form method="POST" action="options.php">

				<?php settings_fields( 'themeblvd_analytics' ); ?>

				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="themeblvd_analytics[code]"><?php esc_html_e( 'Google Tracking ID', 'simple-analytics' ); ?></label>
							</th>
							<td>
								<input name="themeblvd_analytics[google_id]" type="text" class="regular-text" value="<?php echo esc_attr( $code ); ?>" />
								<p class="description"><?php echo esc_html__( 'Input your Google Analytics "Tracking ID"', 'simple-analytics' ) . '<br>' . esc_html__( 'Example: UA-12345678-9', 'simple-analytics' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="themeblvd_analytics[placement]"><?php esc_html_e( 'Analytics Placement', 'simple-analytics' ); ?></label>
							</th>
							<td>
								<fieldset>
									<label>
										<input type="radio" name="themeblvd_analytics[placement]" value="head" <?php checked( 'head', $placement ); ?>> <span><?php printf( esc_html__( 'Include within %s tag.', 'simple-analytics' ), '<code>&lt;head&gt;</code>' ); ?></span>
									</label><br>
									<?php if ( defined( 'TB_FRAMEWORK_VERSION' ) ) : // Only Theme Blvd theme will have an action hook for this. ?>
										<label>
											<input type="radio" name="themeblvd_analytics[placement]" value="body" <?php checked( 'body', $placement ); ?>> <span><?php printf( esc_html__( 'Include immediately after the opening %s tag.', 'simple-analytics' ), '<code>&lt;body&gt;</code>' ); ?></span>
										</label><br>
									<?php endif; ?>
									<label>
										<input type="radio" name="themeblvd_analytics[placement]" value="foot" <?php checked( 'foot', $placement ); ?>> <span><?php printf( esc_html__( 'Include before closing %s tag.', 'simple-analytics' ), '<code>&lt;/body&gt;</code>' ); ?></span>
									</label><br>
								</fieldset>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="themeblvd_analytics[code]"><?php esc_html_e( 'IP Anonymization', 'simple-analytics' ); ?></label>
							</th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>IP Anonymization</span>
									</legend>
									<label>
										<input name="themeblvd_analytics[anonymize]" type="checkbox" value="1" <?php checked( true, $anonymize ); ?>>
										<?php esc_html_e( 'Anonymize IP addresses in tracking code.', 'simple-analytics' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button(); ?>

			</form>

		</div><!-- .wrap (end) -->
		<?php

	}
}

/**
 * Initiate plugin.
 *
 * @since 1.0.0
 */
function themeblvd_simple_analytics_init() {

	Theme_Blvd_Simple_Analytics::get_instance();

}
add_action( 'plugins_loaded', 'themeblvd_simple_analytics_init' );

/**
 * Register text domain for localization.
 *
 * @since 1.0.1
 */
function themeblvd_simple_analytics_textdomain() {

	load_plugin_textdomain( 'simple-analytics' );

}
add_action( 'init', 'themeblvd_simple_analytics_textdomain' );
