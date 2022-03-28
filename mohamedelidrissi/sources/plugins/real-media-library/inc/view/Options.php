<?php

namespace MatthiasWeb\RealMediaLibrary\view;

use MatthiasWeb\RealMediaLibrary\base\UtilsProvider;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * This class handles all hooks for the options.
 *
 * If you want to extend the options for your plugin
 * please use the RML/Options/Register action. There are no
 * parameters. The settings section headline must start with
 * RealMediaLibrary:* (also in translation). The *-value will be
 * added as navigation label.
 */
class Options {
    use UtilsProvider;
    private static $me = null;
    /**
     * C'tor.
     */
    private function __construct() {
        // Silence is golden.
    }
    /**
     * Register RML core fields.
     */
    public function register_fields() {
        if (!wp_rml_active()) {
            return;
        }
        add_settings_section('rml_options_general', __('RealMediaLibrary:General'), [$this, 'empty_callback'], 'media');
        add_option('rml_load_frontend', 1);
        register_setting('media', 'rml_load_frontend', 'esc_attr');
        add_settings_field(
            'rml_load_frontend',
            '<label for="rml_load_frontend">' . __('Load RML functionality in frontend', RML_TD) . '</label>',
            [$this, 'html_rml_load_frontend'],
            'media',
            'rml_options_general'
        );
        /**
         * Allows you to register new options tabs and fields to the Real Media
         * Library options panel (Settings > Media).
         *
         * @example <caption>Create a new tab with a settings field</caption>
         * add_action( 'RML/Options/Register', function() {
         *  // Register tab
         *  add_settings_section(
         *  	'rml_options_custom',
         *  	__('RealMediaLibrary:My Tab'), // The label must begin with RealMediaLibrary:
         *  	[MatthiasWeb\RealMediaLibrary\view\Options::getInstance(), 'empty_callback'),
         *  	'media'
         *  );
         *
         *  add_settings_field(
         *      'rml_button_custom',
         *      '<label for="rml_button_custom">Your custom button</label>' ,
         *      'my_function_to_print_rml_button_custom',
         *      'media',
         *      'rml_options_custom' // The section
         *  );
         * } );
         * @hook RML/Options/Register
         */
        do_action('RML/Options/Register');
        // Reset
        add_settings_section('rml_options_reset', __('RealMediaLibrary:Reset'), [$this, 'empty_callback'], 'media');
        if ($this->isPro()) {
            add_settings_field(
                'rml_button_order_reset',
                '<label for="rml_button_order_reset">' . __('Reset the order of all galleries', RML_TD) . '</label>',
                [$this, 'html_rml_button_order_reset'],
                'media',
                'rml_options_reset'
            );
        }
        add_settings_field(
            'rml_button_wipe',
            '<label for="rml_button_wipe">' .
                __('Wipe all settings (folders, attachment relations)', RML_TD) .
                '</label>',
            [$this, 'html_rml_button_wipe'],
            'media',
            'rml_options_reset'
        );
        add_settings_field(
            'rml_button_cnt_reset',
            '<label for="rml_button_wipe">' . __('Reset folder count cache', RML_TD) . '</label>',
            [$this, 'html_rml_button_cnt_reset'],
            'media',
            'rml_options_reset'
        );
        add_settings_field(
            'rml_button_slug_reset',
            '<label for="rml_button_wipe">' . __('Reset names, slugs and absolute pathes', RML_TD) . '</label>',
            [$this, 'html_rml_button_slug_reset'],
            'media',
            'rml_options_reset'
        );
    }
    // Noop
    public function empty_callback($arg) {
        // Silence is golden.
    }
    /**
     * Output wipe button.
     */
    public function html_rml_button_wipe() {
        // Check if reinstall the database tables
        if (isset($_GET['rml_install'])) {
            echo 'DB Update was executed<br /><br />';
            $this->getCore()
                ->getActivator()
                ->install(\true);
            echo '<br /><br />';
        }
        echo '<a class="rml-rest-button button" data-url="reset/relations" data-method="DELETE">' .
            __('Wipe attachment relations', RML_TD) .
            '</a>
        <a class="rml-rest-button button" data-url="reset/folders" data-method="DELETE">' .
            __('Wipe all', RML_TD) .
            '</a>';
    }
    /**
     * Output count reset button.
     */
    public function html_rml_button_cnt_reset() {
        echo '<a class="rml-rest-button button rml-button-wipe" data-url="reset/count" data-method="DELETE">' .
            __('Reset count', RML_TD) .
            '</a>';
    }
    /**
     * Output reset slug button.
     */
    public function html_rml_button_slug_reset() {
        echo '<a class="rml-rest-button button rml-button-wipe" data-url="reset/slugs" data-method="DELETE">' .
            __('Reset', RML_TD) .
            '</a>';
    }
    /**
     * Output load frontend checkbox.
     */
    public function html_rml_load_frontend() {
        $value = get_option('rml_load_frontend', '1');
        echo '<input type="checkbox" id="rml_load_frontend"
                name="rml_load_frontend" value="1" ' .
            checked(1, $value, \false) .
            ' />
                <label>' .
            __(
                'Activate this option if you are using a page builder like Divi Page Builder, WPBakery Page Builder or Elementor.',
                RML_TD
            ) .
            '</label>';
    }
    /**
     * Output order reset button.
     */
    public function html_rml_button_order_reset() {
        echo '<a class="rml-rest-button button button-primary" data-url="reset/order" data-method="DELETE">' .
            __('Reset') .
            '</a>
            <p class="description">' .
            __('You can also reset an single folder in its folder details.', RML_TD) .
            '</p>';
    }
    /**
     * Is RML allowed to load on frontend? (Non-Admin area)
     *
     * @return boolean
     */
    public static function load_frontend() {
        return get_option('rml_load_frontend', '1') === '1';
    }
    /**
     * Get instance.
     *
     * @return Options
     */
    public static function getInstance() {
        return self::$me === null ? (self::$me = new \MatthiasWeb\RealMediaLibrary\view\Options()) : self::$me;
    }
}
