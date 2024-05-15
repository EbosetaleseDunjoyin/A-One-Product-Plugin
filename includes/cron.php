<?php
// require_once (AONE_PATH . '/includes/utilites.php');

require_once AONE_PATH . '/classes/LegendLife.php';
// require_once AONE_PATH . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

use AOneProducts\Classes\LegendLife;


// LegendLife::get_instance();

// $sync = true;
// $sync = carbon_get_theme_option('aone_sync_product');
$sync = one_get_theme_option('aone_sync_product');
// $sync = get_option('aone_sync_product');
//Cron Jobs
// get_fashion_biz_products

if($sync):

function schedule_fashion_biz_cron()
{
    if (!as_has_scheduled_action('get_fashion_biz_products')) {
            as_schedule_recurring_action(time(), 86400, 'get_fashion_biz_products');
    }
}
add_action('init', 'schedule_fashion_biz_cron');
add_action('get_fashion_biz_products', 'getFashionBizProductsAndSave');


function schedule_trends_cron()
{
    if (!as_has_scheduled_action('get_trends_products')) {
        as_schedule_recurring_action(time(), 86400, 'get_trends_products');
    }
}
add_action('init', 'schedule_trends_cron');
add_action('get_trends_products', 'getTrendsProductsAndSave');


// function schedule_legend_life_cron()
// {
//     if (!wp_next_scheduled('get_legend_life_products')) {
//         wp_schedule_event(time(), 'daily', 'get_legend_life_products');
//     }
// }
// add_action('wp', 'schedule_legend_life_cron');
// add_action('get_legend_life_products', 'getLegendLifeProductsAndSave');
// function get_trends_products_function()
// {
//     getTrendsProductsAndSave();
// }


display_admin_notice("Products are syncing", 'success');

endif;