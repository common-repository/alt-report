<?php

/*
  Plugin Name: AlT Report
  Version: 1.12.0
  Plugin URI: http://wordpress.lived.fr/plugins/alt-report/
  Description: Gestion des bugs
  Author: AlTi5
  Author URI: http://wordpress.lived.fr/alti5/
  Text Domain: alt_report
  Domain Path: /languages/
 */


if (!class_exists('alt-report')) {

    class alt_report {

        public static function hooks() {

            add_action('plugins_loaded', array(__CLASS__, 'constants'));
            add_action('plugins_loaded', array(__CLASS__, 'includes'));
            add_action('plugins_loaded', array(__CLASS__, 'load_textdomain'));
            register_activation_hook(__FILE__, array(__CLASS__, 'plugin_activation'));

            if (is_admin()) {
                add_action('admin_menu', array(__CLASS__, 'add_settings_panels'));
                add_action('admin_enqueue_scripts', array(__CLASS__, 'custom_admin_scripts'));
            } else {
                add_action('wp_enqueue_scripts', array(__CLASS__, 'altreport_enqueue_scripts'));
            }
        }

        /**
         * Add script and css plugin to front, only when necessary.
         */
        public static function altreport_enqueue_scripts() {
            $altreport_infos = unserialize(get_option('altreport_infos'));
            $user_infos = wp_get_current_user();
            $user_role = $add_script = '';

            if (isset($user_infos) && $user_infos->ID != 0) {
                $user_role = '|' . implode("|", $user_infos->roles) . '|';
                if (strpos($altreport_infos['user_log'], $user_role) !== false) {
                    $add_script = "ok";
                }
            } else if (strpos($altreport_infos['user_log'], "|all|") !== false) {
                $add_script = "ok";
            }
            if ($add_script == 'ok') {
                wp_enqueue_script('html2canvas', ALTREPORT_LOAD_ASSETS . 'html2canvas.js', array('jquery'), ALTREPORT_VERSION);
                wp_enqueue_script('altreport', ALTREPORT_LOAD_ASSETS . 'altreport.js', array('jquery'), ALTREPORT_VERSION);
                wp_enqueue_style('altreport_front', ALTREPORT_LOAD_ASSETS . 'altreport_front.css', array(), ALTREPORT_VERSION);
                wp_enqueue_style('altreport', ALTREPORT_LOAD_ASSETS . 'altreport.css.php', array(), ALTREPORT_VERSION);
            }
        }

        /**
         * Allows to make the plugin translatable
         */
        public static function load_textdomain() {
            load_plugin_textdomain('alt_report', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * Adds scripts and css for back office
         * @param type $hook
         * @return type
         */
        public static function custom_admin_scripts($hook) {
            if ($hook != 'rapport_page_settings') {
                return;
            }
            wp_enqueue_style('altreport_tblbords', ALTREPORT_LOAD_ASSETS . 'altreport_tblbords.css', array(), ALTREPORT_VERSION);
            wp_enqueue_script('altreport_tblbords', ALTREPORT_LOAD_ASSETS . 'altreport_tblbords.js', array(), ALTREPORT_VERSION);
        }

        /**
         * Add the Parameter menu in the side menu of administration on the post type report
         */
        public static function add_settings_panels() {
            add_submenu_page(
                    'edit.php?post_type=rapport', __('Paramétrage', 'alt_report'), __('Paramétrage', 'alt_report'), 'administrator', 'settings', array(__CLASS__, 'settings')
            );
            add_submenu_page(
                    'edit.php?post_type=rapport', __('Paramétrage Redmine', 'alt_report'), __('Paramétrage Redmine', 'alt_report'), 'administrator', 'settings-redmine', array(__CLASS__, 'settings_redmine')
            );
            add_submenu_page(
                    'edit.php?post_type=rapport', __('Statistiques', 'alt_report'), __('Statistiques', 'alt_report'), 'administrator', 'statistiques', array(__CLASS__, 'statistiques')
            );
        }

        /**
         * The recurring plugin values
         */
        public static function constants() {
            $upload_dir = wp_upload_dir();
            define('ALTREPORT_VERSION', '1.11.0' . time());
            define('ALTREPORT_DIR', trailingslashit(plugin_dir_path(__FILE__)));
            define('ALTREPORT_URI', trailingslashit(plugin_dir_url(__FILE__)));
            define('ALTREPORT_LOAD', $upload_dir['basedir'] . '/' . trailingslashit('alt_report'));
            define('ALTREPORT_LOAD_URL', $upload_dir['baseurl'] . '/' . trailingslashit('alt_report'));
            define('ALTREPORT_LOAD_ASSETS', ALTREPORT_URI . trailingslashit('assets'));
        }

        /**
         * Useful functions for the plugin
         */
        public static function includes() {
            require_once 'includes/functions.php';
        }

        /**
         * Manages the plugin activation. Only works when you activate the plugin
         */
        public static function plugin_activation() {
            require_once 'includes/parametrage.php';
        }

        /**
         * Manages the dashboard display
         * @global type $tblbords_option
         */
        public static function settings() {
            global $tblbords_option;
            require_once 'template/settings.php';
        }

        /**
         * Manages the dashboard display
         * @global type $tblbords_option
         */
        public static function settings_redmine() {
            global $tblbords_option;
            require_once 'template/settings_redmine.php';
        }

        /**
         * Manages the dashboard display
         * @global type $tblbords_option
         */
        public static function statistiques() {
            global $tblbords_option;
            require_once 'template/statistiques.php';
        }

        /**
         * Allows update and save of meta
         * @param type $option
         * @param type $value
         */
        private static function set_option($option, $value) {
            if (get_option($option) !== FALSE) {
                update_option($option, $value);
            } else {
                add_option($option, $value, '', 'no');
            }
        }

        /**
         * Return the id of the current post
         * @param type $post_id
         * @return type
         */
        public static function save_post($post_id) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return $post_id;
            if (isset($_POST['post_type'])) {
                if ('distributeurs' != $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
                    return $post_id;
                }
            }
            if ($parent_post_id = wp_is_post_revision($post_id)) {
                $post_id = $parent_post_id;
            }
        }

    }

    alt_report::hooks();
}

