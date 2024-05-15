<?php 

/**
 * CLasses
 * 
 * @package probo-product
 */

require_once PROBO_PATH .'/classes/traits/Singleton.php';
require_once PROBO_PATH .'/classes/Probo.php';
require_once PROBO_PATH .'/classes/Woo.php';

use AOneProducts\Classes\Probo;
use AOneProducts\Classes\Woo;


function do_product()
{
    $woo = Woo::get_instance();
    $probo = Probo::get_instance();

    // $woo->test("Works");

    $name = "Probo Product 3";
    $sku = "probo_12323";
    $price = "4000";
    // $woo->create_woocommerce_product($name, $sku, $price);
}


add_action('woocommerce_new_order', 'trigger_custom_api_on_purchase', 10, 1);

function trigger_custom_api_on_purchase($order_id)
{
    // Get order details
    $order = wc_get_order($order_id);

    $products = [];
    $deliveries = $order->get_address('billing');;
    // Loop through order items
    foreach ($order->get_items() as $item_id => $item) {
        // Check if the purchased product has a specific meta key
        $product_meta_key = get_post_meta($item->get_product_id(), 'probo_product', true);
        if ($product_meta_key == true) {
            // Trigger your custom API here
            // Use tools like cURL or other PHP libraries to make the API request
            array_push($products,$item);
        }
    }
    $request=[
        'products' => $products,
        'deliveries' => $deliveries
    ];
    $probo = Probo::get_instance();
    $result = $probo->save_probo_product_order($request);

    
}

