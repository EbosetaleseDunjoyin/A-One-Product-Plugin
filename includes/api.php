<?php

// require_once AONE_PATH . '/classes/Probo.php';
require_once AONE_PATH . '/classes/Woo.php';
require_once AONE_PATH . '/classes/FashionBiz.php';
require_once AONE_PATH . '/classes/processes/WP_FashionBiz.php';
require_once AONE_PATH . '/classes/Trends.php';
require_once AONE_PATH . '/classes/LegendLife.php';

use AOneProducts\Classes\Woo;
use AOneProducts\Classes\Probo;
use AOneProducts\Classes\Trends;
use AOneProducts\Classes\FashionBiz;
use AOneProducts\Classes\Processes\WP_FashionBiz;
use AOneProducts\Classes\LegendLife;

add_action("rest_api_init", "create_rest_endpoint");

// $sync = carbon_get_theme_option('aone_sync_product');

function  create_rest_endpoint(){
    register_rest_route('v1/aOneProduct', '/getProductsAndSave', array(
        'methods' => 'GET',
        'callback' => 'getProductsAndSave',
        'permission_callback' => '__return_true'
    ));

    register_rest_route('v1/aOneProduct', '/testProductsAndSave', array(
        'methods' => 'GET',
        'callback' => 'getFashionBizProductsAndSave',
        'permission_callback' => '__return_true'
    ));
    

   
}

function getProductsAndSave(): WP_REST_Response
{
    $probo = Probo::get_instance();
    $wooComm = Woo::get_instance();
    // Your API endpoint URL
    $noOfProducts = 456;
    $apiKey = "asdsddsd";
    if (!$apiKey ) {
        $status = false;
        $message = "No API key found.";
        $code = 400;
        $showNotice = false;
        $response = [
            'status' => $status,
            'message' => $message,
            // 'data' => $result,
        ];

        return new WP_REST_Response($response, $code);

    }
    if(!$noOfProducts && $noOfProducts < 0){
        $status = false;
        $message = "No Probo product found.";
        $code = 400;
        $showNotice = false;

    }else{
        $per_page = round($noOfProducts / 2);
        $pages = round($noOfProducts/$per_page);

        for ($page=1; $page <= $pages; $page++) { 
            # code...
            $results = $probo->get_probo_product($per_page , $page );
            if($results->data && count($results->data) < 0){
                $status = false;
                $message = "No Probo product found.";
                $code = 400;
                $response = [
                    'status' => $status,
                    'message' => $message,
                    'data' => $results,
                ];

                return new WP_REST_Response($response, $code);
            }

            if($results->meta && $results->meta->items && $results->meta->pages){
                $pages = $results->meta->pages;
                $noOfProducts = $results->meta->items;
            }

            foreach($results->data as $key => $result){
                if($result->active){

                    $name = $result->translations->en->title;
                    $content = $result->translations->en->description;
                    $sku = $result->code;
                    $price = 0;
                    $cartegory = $result->article_group_name;
    
                    $saveData = $wooComm->create_woocommerce_product($name, $content, $cartegory, $sku, $price);
                }
            }
        }
        // $woo->create_woocommerce_product($name, $sku, $price);
        
        if ($saveData == true) {
            
            $status = true;
            $message = "Done Successfully.";
            $code = 200;
            $showNotice = true;
        } else {
            $status = false;
            $message = "Failed to get probo data";
            $code = 400;
            $showNotice = false;
    
        }
    }


    

    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $results,
    ];


    return new WP_REST_Response($response, $code);
}
function testProductsAndSave(): WP_REST_Response
{
        // $probo = Probo::get_instance();
        

        $wooComm = Woo::get_instance();
        $fashionBiz = FashionBiz::get_instance();
        $trends = Trends::get_instance();

    
        // Your API endpoint URL

        $getData = $trends->get_trends_products();
        $data = $getData['data'];
        // $status = false;
        // $message = $getData['message'];
        // $code = 400;
        // $response = [
        //     'status' => $status,
        //     'message' => $message,
        //     'data' => $data,
        // ];

        // return new WP_REST_Response($response, $code);

        if ($getData['data'] === null && !$getData['status']) {
            $status = false;
            $message = $getData['message'];
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => $getData['data'],
            ];

            return new WP_REST_Response($response, $code);
        }
        $noOfProducts = $getData['data'];
        $pages = round($noOfProducts->total_count / count($noOfProducts->products));

        
        $singleData = $fashionBiz->get_fashion_biz_single_products($data->slug);
        $singleResult = $singleData['data'];
        if ($singleData['data'] == null && !$singleData['status']) {
            $status = false;
            $message = $singleData['message'];
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => $singleData['data'],
            ];

            return new WP_REST_Response($response, $code);
        }
        $desc = $singleResult->description;

        //  for ($page=1; $page <= $pages; $page++) { 
        //     # code...
        //     $results = $fashionBiz->get_fashion_biz_products($page);
        //     if($results->data && count($results->data) < 0){
        //         $status = false;
        //         $message = "No Probo product found.";
        //         $code = 400;
        //         $response = [
        //             'status' => $status,
        //             'message' => $message,
        //             'data' => $results,
        //         ];

        //         return new WP_REST_Response($response, $code);
        //     }
        //  }
       
        $saveData = $wooComm->import_fashion_woocommerce_product($data, $desc);
        
        if ($saveData == true) {
            
            $status = true;
            $message = "Done Successfully.";
            $code = 200;
            $showNotice = true;
        } else {
            $status = false;
            $message = "Failed to get probo data";
            $code = 400;
            $showNotice = false;
    
        }


    

    $response = [
        'status' => $status,
        'message' => $message,
        'data' => "",
    ];


    return new WP_REST_Response($response, $code);
}

function getFashionBizProductsAndSave(): WP_REST_Response
{
    $wooComm = Woo::get_instance();
    $fashionBiz = FashionBiz::get_instance();
    $wp_fashionBiz = WP_FashionBiz::get_instance();

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
        foreach ($data->products as $productData) {
            $singleData = $fashionBiz->get_fashion_biz_single_products($productData->slug);
            if (is_wp_error($singleData)) {
                // Handle single product retrieval error (log or display individual message)
                continue; // Skip to next product on error
            }

            $singleResult = $singleData['data'];
            $desc = $singleResult->description;

            // $saveData = $wooComm->import_fashion_woocommerce_product();
            // $wp_fashionBiz->push_to_queue( [$productData, $desc] );
		

		    
            $saveData = $wooComm->import_fashion_woocommerce_product($productData, $desc);

            // Handle import success/failure (log or display individual message)
        }
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


    for($page = 1;  $hasMoreProducts; $page++){
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

//    $id = $legendLife->get_login_details();
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
        if ($data === null && !$getData['status']) {
            $status = false;
            $message = $getData['message'];
            $code = 400;
            $response = [
                'status' => $status,
                'message' => $message,
                'data' => null,
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






