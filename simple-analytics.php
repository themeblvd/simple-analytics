<?php
/*
Plugin Name: Simple Analytics
Description: A simple plugin to include your Google Analtyics tracking.
Version: 1.0.2
Author: Theme Blvd
Author URI: http://themeblvd.com
License: GPL2

    Copyright 2015  Theme Blvd

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

define( 'TB_SIMPLE_ANALYTICS_PLUGIN_VERSION', '1.0.2' );
define( 'TB_SIMPLE_ANALYTICS_TWEEPLE_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_SIMPLE_ANALYTICS_PLUGIN_URI', plugins_url( '' , __FILE__ ) );
define( 'TB_SIMPLE_ANALYTICS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Setup plugin.
 */
class Theme_Blvd_Simple_Analytics {

    /**
     * Only instance of object.
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
        if( self::$instance == null ) {
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

        // Output Analytics
        if ( ! is_admin() && ! current_user_can( 'edit_theme_options' ) ) {
            add_action( 'after_setup_theme', array( $this, 'add_output' ) );
        }

        // Settings page
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

            if ( defined('TB_FRAMEWORK_VERSION') && $analytics['placement'] == 'body' ) {
                add_action( 'themeblvd_before', array( $this, 'output' ), 2 );
            } else if ( $analytics['placement'] == 'foot' ) {
                add_action( 'wp_footer', array( $this, 'output' ), 1000 );
            } else {
                add_action( 'wp_head', array( $this, 'output' ), 2 );
            }

        }
    }

    /**
     * Output analytics
     *
     * @since 1.0.0
     */
    public function output() {

        $analytics = get_option( 'themeblvd_analytics' );

        if ( ! empty( $analytics['google_id'] ) ) :
?>
<!-- Simple Analytics by Theme Blvd -->
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php echo esc_attr( $analytics['google_id'] ); ?>', 'auto');
    ga('send', 'pageview');

</script>
<?php
        endif; // end if $analytics['google_id'] )

    }

    /**
     * Add settings page
     *
     * @since 1.0.0
     */
    public function admin_menu() {
        add_options_page( __('Analytics', 'simple-analytics'), __('Analytics', 'simple-analytics'), 'edit_theme_options', 'simple-analytics', array( $this, 'settings_page' ) );
    }

    /**
     * Register settings
     *
     * @since 1.0.0
     */
    public function admin_init() {
        register_setting( 'themeblvd_analytics', 'themeblvd_analytics', array( $this, 'sanitize' ) );
    }

    /**
     * Sanitization callback for saving settings
     *
     * @since 1.0.0
     */
    public function sanitize( $input ) {

        global $allowedposttags;
        $allowed_tags = array_merge( $allowedposttags, array( 'script' => array( 'type' => true, 'src' => true ) ) );

        $output = array();

        foreach ( $input as $key => $value ) {

            switch ( $key ) {

                case 'google_id' :
                    $output[$key] = esc_attr( $value );
                    break;

                case 'placement' :
                    $choices = array( 'head', 'body', 'foot' );
                    if ( in_array( $value, $choices ) ) {
                        $output[$key] = $value;
                    } else {
                        $output[$key] = $choices[0];
                    }
                    break;

            }

        }

        return $output;

    }

    /**
     * Display settings page
     *
     * @since 1.0.0
     */
    public function settings_page() {

        // Setup current settings
        $settings = get_option( 'themeblvd_analytics' );

        $code = '';
        if ( isset( $settings['google_id'] ) ) {
            $code = $settings['google_id'];
        }

        $placement = 'body';

        if ( isset( $settings['placement'] ) ) {
            $placement = $settings['placement'];
        }

        if ( $placement == 'body' && ! defined('TB_FRAMEWORK_VERSION') ) {
            $placement = 'head';
        }

        ?>
        <div class="wrap">

            <?php settings_errors( 'themeblvd_analytics' ); ?>

            <div id="icon-options-general" class="icon32"><br></div>
            <h2><?php _e('Analytics', 'simple-analytics'); ?></h2>

            <form method="POST" action="options.php">

                <?php settings_fields( 'themeblvd_analytics' ); ?>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="themeblvd_analytics[code]"><?php _e('Google Tracking ID', 'simple-analytics'); ?></label>
                            </th>
                            <td>
                                <input name="themeblvd_analytics[google_id]" type="text" class="regular-text" value="<?php echo $code; ?>" />
                                <p class="description"><?php _e('Input your Google Analytics "Tracking ID"<br />Example: UA-12345678-9', 'simple-analytics'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="themeblvd_analytics[placement]"><?php _e('Analytics Placement', 'simple-analytics'); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input type="radio" name="themeblvd_analytics[placement]" value="head" <?php checked( 'head', $placement ); ?>> <span><?php _e('Include within <code>&lt;head&gt;</code> tag.', 'simple-analytics'); ?></span>
                                    </label><br>
                                    <?php if ( defined('TB_FRAMEWORK_VERSION') ) : // Only Theme Blvd theme will have an action hook for this ?>
                                        <label>
                                            <input type="radio" name="themeblvd_analytics[placement]" value="body" <?php checked( 'body', $placement ); ?>> <span><?php _e('Immediately after the opening <code>&lt;body&gt;</code> tag.', 'simple-analytics'); ?></span>
                                        </label><br>
                                    <?php endif; ?>
                                    <label>
                                        <input type="radio" name="themeblvd_analytics[placement]" value="foot" <?php checked( 'foot', $placement ); ?>> <span><?php _e('Include before closing <code>&lt;/body&gt;</code> tag.', 'simple-analytics'); ?></span>
                                    </label><br>
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
    load_plugin_textdomain('simple-analytics');
}
add_action( 'init', 'themeblvd_simple_analytics_textdomain' );
