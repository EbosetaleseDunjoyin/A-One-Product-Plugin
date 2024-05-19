<?php
/*
 * Plugin Name:       A One Products 
 * Description:       Handle the A One product plugin.
 * Version:           1.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Wordpress
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       a-one-products
 */




if (!defined('ABSPATH')) {
    exit; // Prevent direct access to the script
}

if (!defined('AONE_PATH')) {
    define('AONE_PATH', plugin_dir_path(__FILE__));
}
if (!defined('AONE_ASSETS')) {
    define('AONE_ASSETS', AONE_PATH . 'assets');
}
if (!defined('AONE_ASSETS_URL')) {
    define('AONE_ASSETS_URL', plugin_dir_url(__FILE__) . 'assets');
}

require_once AONE_PATH . 'vendor/autoload.php'; // Check the correct path to the autoload file

if (!class_exists('AOneStage')) {
    class AOneStage
    {
        public function __construct()
        {
            // $this->setup();
        }

        public function setup()
        {
            include_once AONE_PATH . '/includes/options-page.php';
            include_once AONE_PATH . '/includes/utilites.php'; 
            include_once AONE_PATH . '/includes/api.php';
            include_once AONE_PATH . '/includes/cron.php';
            include_once AONE_PATH . '/includes/test.php';

            add_action('admin_enqueue_scripts', [$this, 'aOne_enqueue_admin_scripts']);
        }

        public function aOne_enqueue_admin_scripts()
        {
            wp_enqueue_script('aOne-admin-scripts', AONE_ASSETS_URL . '/admin.js', [], filemtime(AONE_ASSETS . '/admin.js'), true);

            wp_localize_script('aOne-admin-scripts', 'aOne_admin_params', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'root_url' => esc_url_raw(rest_url())
            )
            );
        }
    }

    $aone = new AOneStage();
    $aone->setup();
}