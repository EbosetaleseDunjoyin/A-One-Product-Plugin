<?php

// require_once AONE_PATH . '/classes/Probo.php';
require_once AONE_PATH . '/classes/Woo.php';
require_once AONE_PATH . '/classes/FashionBiz.php';
require_once AONE_PATH . '/classes/WooApi.php';
require_once AONE_PATH . '/classes/Trends.php';
require_once AONE_PATH . '/classes/LegendLife.php';

use AOneProducts\Classes\Woo;
use AOneProducts\Classes\Probo;
use AOneProducts\Classes\Trends;
use AOneProducts\Classes\WooApi;
use AOneProducts\Classes\FashionBiz;
use AOneProducts\Classes\LegendLife;
use AOneProducts\Classes\Processes\WP_FashionBiz;

add_action("rest_api_init", "create_rest_endpoint");

// $sync = carbon_get_theme_option('aone_sync_product');

function  create_rest_endpoint(){
    // register_rest_route('v1/aOneProduct', '/getProductsAndSave', array(
    //     'methods' => 'GET',
    //     'callback' => 'getProductsAndSave',
    //     'permission_callback' => '__return_true'
    // ));

    register_rest_route('v1/aOneProduct', '/testProductsAndSave', array(
        'methods' => 'GET',
        'callback' => 'getLegendLifeProductsAndSave',
        'permission_callback' => '__return_true'
    ));
    

   
}



function getFashionBizProductsAndSave(): WP_REST_Response
{
    $wooComm = Woo::get_instance();
    $fashionBiz = FashionBiz::get_instance();
    // $wp_fashionBiz = WP_FashionBiz::get_instance();

    // Initial error checking
    if (!$wooComm || !$fashionBiz) {
        $status = false;
        $message = "Failed to get WooCommerce or FashionBiz instances.";
        $code = 400;
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => null,
        ];
        return new WP_REST_Response($response, $code);
    }

    
    $hasMoreProducts = true; // Flag to track if there are more products

    for($page = 1;  $hasMoreProducts; $page++){
          $getData = $fashionBiz->get_fashion_biz_products($page);

        // Handle API call errors
        if (is_wp_error($getData)) {
            $status = false;
            $message = "Error retrieving products: " . $getData->get_error_message();
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => null,
            ];
            error_log($message);
            return new WP_REST_Response($response, $code);

        }

        $data = $getData['data'];

        // Check for empty data or error status
        if ($data === null && !$getData['status']) {
            $status = false;
            $message = $getData['message'];
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => null,
            ];
            error_log($message);
            return new WP_REST_Response($response, $code);
        }

        // Check for invalid page or empty product list and stop looping
        if (isset($data->detail) && $data->detail === "Invalid page.") {
            $hasMoreProducts = false;
            $message = "Reached the end of products.";
            error_log($message);// Adjust message if needed
            break;
        } else if (empty($data->products)) {
            $hasMoreProducts = false;
            $message = "No products found on this page.";
            error_log($message); // Adjust message if needed
            continue; // Continue to check next page even if no products on current page
        }

        // Process products on current page (consider batching if performance is critical)
        for ($index = 0; $index <= 7; $index++) {
        // for ($index = 8; $index <= 15; $index++) {
       
            $productData = $data->products[$index];
            $singleData = $fashionBiz->get_fashion_biz_single_products($productData->slug);
            if (is_wp_error($singleData)) {
                // Handle single product retrieval error (log or display individual message)
                continue; // Skip to next product on error
            }

            $singleResult = $singleData['data'];
            $desc = $singleResult->description;

            // $saveData = $wooComm->import_fashion_woocommerce_product();
            // $wp_fashionBiz->push_to_queue( [$productData, $desc] );
		

		    
             $wooComm->import_fashion_woocommerce_product($productData, $desc);

            // Handle import success/failure (log or display individual message)
        }
        $hasMoreProducts = false;
        // $wp_fashionBiz->save()->dispatch();
    }

    // do {
      

    //     $page++; // Increment page for next iteration

    // } while ($hasMoreProducts); // Loop until no more products or invalid page

    // Final response based on all products processed
    if (!$hasMoreProducts) {
        $status = true;
        $message = "Successfully processed products.";
        $code = 200;
    } else {
        $status = false;
        $message = "Unexpected error.";
        error_log($message);// Adjust message if needed
    }

    $response = [
        'status' => $status,
        'message' => $message,
        'data' => "", // Adjust if you want to return specific data
    ];

    return new WP_REST_Response($response, $code);
}


function getTrendsProductsAndSave(): WP_REST_Response
{
    $wooComm = Woo::get_instance();
    $trends = Trends::get_instance();

    // Initial error checking
    if (!$wooComm || !$trends) {
        $status = false;
        $message = "Failed to get WooCommerce or trends instances.";
        $code = 400;
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => null,
        ];
        error_log($message);
        return new WP_REST_Response($response, $code);
    }
    // $firstData = $trends->get_trends_products(1,1);

    // if ($firstData['data'] === null) {
    //     $status = false;
    //     $message = $firstData['message'];
    //     $code = 400;
    //     $response = [
    //         'status' => $status,
    //         'message' => $message,
    //         'data' => $firstData['data'],
    //     ];
    //     return new WP_REST_Response($response, $code);
    // }

    // $product_count=$firstData['data']->total_items;
    // $pages = round($product_count/100);



    $hasMoreProducts = true; // Flag to track if there are more products


    for ($page = 1; $hasMoreProducts; $page++) {
        $getData = $trends->get_trends_products($page);

        // Handle API call errors
        if (is_wp_error($getData)) {
            $status = false;
            $message = "Error retrieving products: " . $getData->get_error_message();
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => null,
            ];
            error_log($message);
            return new WP_REST_Response($response, $code);
        }

        $data = $getData['data'];

        // Check for empty data or error status
        if ($data === null && !$getData['status']) {
            $status = false;
            $message = $getData['message'];
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => null,
            ];
            error_log($message);
            return new WP_REST_Response($response, $code);
        }

        // Check for invalid page or empty product list and stop looping
        if (isset($data->data) && count($data->data) === 0) {
            $hasMoreProducts = false;
            $message = "Reached the end of products.";
            // Adjust message if needed
            error_log($message);
            break;
        }

        // Process products on current page (consider batching if performance is critical)

        foreach ($data->data as $productData) {
            $wooComm->import_trends_woocommerce_product($productData);
        }
        $hasMoreProducts = false;
        //  break;
        // Handle import success/failure (log or display individual message)

    }


    // Final response based on all products processed
    if (!$hasMoreProducts) {
        $status = true;
        $message = "Successfully processed products.";
        $code = 200;
    } else {
        $status = false;
        $message = "Unexpected error."; // Adjust message if needed
        error_log("An issue occured with products");
    }

    $response = [
        'status' => $status,
        'message' => $message,
        'data' => "", // Adjust if you want to return specific data
    ];

    return new WP_REST_Response($response, $code);
}


function getLegendLifeProductsAndSave(): WP_REST_Response
{
    $wooComm = Woo::get_instance();
    $legendLife = LegendLife::get_instance();

    // Initial error checking
    if (!$wooComm || !$legendLife) {
        $status = false;
        $message = "Failed to get WooCommerce or trends instances.";
        $code = 400;
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => null,
        ];
        return new WP_REST_Response($response, $code);
    }


    
    $hasMoreProducts = true; // Flag to track if there are more products


    for($page = 1;  $hasMoreProducts; $page++){
          $getData = $legendLife->get_legend_life_products($page);

        // Handle API call errors
        if (is_wp_error($getData)) {
            $status = false;
            $message = "Error retrieving products: " . $getData->get_error_message();
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => null,
            ];
            return new WP_REST_Response($response, $code);
        }

        $data = $getData['data'];

        // Check for empty data or error status
        // if ($getData['status']) {
        if ($data === null && !$getData['status']) {
            $status = false;
            $message = $getData['message'];
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => $data,
            ];
            return new WP_REST_Response($response, $code);
        }

        $dom = new DOMDocument();
        $dom->loadXML($data);

        $dataIndex = new DOMXPath($dom);


        $page_total = get_path_data($dataIndex,'//item[key="page_total"]/value');
        // Check for invalid page or empty product list and stop looping
        if ($page_total <= $page) {
            $hasMoreProducts = false;
            $message = "Reached the end of products."; // Adjust message if needed
            break;
        } 

        // Process products on current page (consider batching if performance is critical)
        $products = $dataIndex->query('//item[key="data"]/value/item');
       
         foreach ($products as $productData) {
           $wooComm->import_legendlife_woocommerce_product($productData);

         }
        //  break;
        $hasMoreProducts = false;
            // Handle import success/failure (log or display individual message)
        
    }


    // Final response based on all products processed
    if (!$hasMoreProducts) {
        $status = true;
        $message = "Successfully processed products.";
        $code = 200;
    } else {
        $code = 400;
        $status = false;
        $message = "Unexpected error."; // Adjust message if needed
    }

    $response = [
        'status' => $status,
        'message' => $message,
        'data' => "", // Adjust if you want to return specific data
    ];

    return new WP_REST_Response($response, $code);
}






