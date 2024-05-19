<?php
/**
 * ProboClass
 */
namespace AOneProducts\Classes;


require_once ABSPATH . "/wp-load.php";
require_once AONE_PATH . '/classes/traits/Singleton.php';

// require_once(ABSPATH . "/wp-content/plugins/woocommerce/includes/class-wc-product.php");


defined('ABSPATH') || exit;

use DOMXPath;
use WC_Product;
use DOMDocument;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Attribute;
use WC_Product_Variation;
use AOneProducts\Classes\Traits\Singleton;


if (!class_exists("Woo")):

    class Woo
    {
        use Singleton;
        protected $consumer_key = "ck_73cde9d7d03a02c83263aa7a53308924ac6cd12a";
        protected $consumer_secret = "cs_91a587cbbafc61f8e3f3824117b2e3a2d4b2179e";
        protected $varProduct;
        protected $productAttribute;
        protected $data;
        public function __construct()
        {
            // $this->varProduct  = new WC_Product_Variable();
            // $this->productAttribute  = new  WC_Product_Attribute();

            $this->setup_class();

      
            
          
            
        }
     

        public function setup_class(){
            // add_action('admin_notices', array($this, 'display_admin_notice'));
        }

        public function create_woocommerce_product($name,$content,$cartegory, $sku, $price)
        {
            global $wpdb;
           
            // $existing_product_id = wc_get_product_id_by_sku($sku);
            $existing_product_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value = %s",
                    $sku
                )
            );

            
            if ($existing_product_id && $existing_product_id > 0) {

                update_post_meta($existing_product_id, '_regular_price', $price);
                update_post_meta($existing_product_id, '_price', $price);

                $updated_product_id = $wpdb->update(
                    $wpdb->posts,
                    array('post_title' => $name),
                    array('ID' => $existing_product_id)
                );
                if (!$updated_product_id) {
                   
                    return false;
                }
                return true;
            } else {

                // Prepare product data
                $product_data = array(
                    'post_title' => $name,
                    'post_status' => 'publish',
                    'post_type' => 'product',
                    'post_content' => $content
                );

                // Insert the product post
                $product_id = wp_insert_post($product_data);

                if (is_wp_error($product_id)) {
                    $message = 'Error creating product: ' . $product_id->get_error_message();
                    // $this->display_admin_notice($message, 'error');
                    return false;
                }

                // Set SKU, price, and product type
                update_post_meta($product_id, '_sku', $sku);
                update_post_meta($product_id, '_price', $price);
                update_post_meta($product_id, '_regular_price', $price);
                // update_post_meta($product_id, '_product_attributes', array());
                update_post_meta($product_id, '_visibility', 'visible');
                update_post_meta($product_id, '_stock_status', 'instock');
                update_post_meta($product_id, 'probo_product', true);
                wp_set_object_terms($product_id, $cartegory, 'product_cat');

                // Update WooCommerce term relationships (categories, etc.) if needed
                // For example, you can use wp_set_object_terms()

                $message = 'Product created successfully with ID ' . $product_id;
                // $this->display_admin_notice($message, 'success');

                return true;
            }
        }
        // public function import_fashion_woocommerce_product($data,$desc)
        // {
        //     $existingProductId = wc_get_product_id_by_sku($data->slug);
        //     if ($existingProductId > 0) {

        //         $this->varProduct = wc_get_product($existingProductId);
        //         $meta_value = $this->varProduct->get_meta('aone_upload', true);

        //         if ($meta_value) {return true; }
        //         return true;
        //         // Update slug (if desired) - Adjust based on your slug source
        //     } else {
        //         $this->varProduct = new WC_Product_Variable();
        //     }

        //     $attributes = [];

        //     $fabricList = is_array($desc->fabric) ? implode(', ', $desc->fabric) : $desc->fabric; 
        //     $featuresList = is_array($desc->features) ? implode(".  ", $desc->features) : $desc->features; 

        //     $description = "**Fabric:** " . $fabricList . "\n\n**Features:**\n  * " . $featuresList;

        //     // Short description with limited features
        //     $shortDescription = "Made with " . $fabricList;

           
           
        //     $this->varProduct->set_name($data->name); // product title
        //     $this->varProduct->set_sku($data->slug); // product sku
            
        //     // $price = !empty($data->prices) && isset($data->prices[0]->price) ? $data->prices[0]->price : 0;
        //     $price = null;
        //     if (!empty($data->prices)) {
        //         if (is_array($data->prices)) {
        //             if (isset($data->prices[0]->price)) {
        //                 $price = $data->prices[0]->price;
        //             }
        //         } 
        //     }
        //     $this->varProduct->set_regular_price($price); // in current shop currency
        //     $quantity = ($price > 0 || $price != null) ? 500 : 0;
        //     $this->varProduct->set_stock_quantity($quantity);

        //     $this->varProduct->set_description($description);
        //     $this->varProduct->set_short_description($shortDescription);
        //     // you can also add a full product description
        //     // $this->varProduct->set_description( 'long description here...' );

        //     $image_id = upload_file_by_url($data->images[0]->https_attachment_url_product);

        //     $this->varProduct->set_image_id($image_id);

            

        //     $sizes = $data->colors[0]->sizes;
        //     $colors = $data->colors;

        //     // Initialize an empty array to store sizes
        //     $sizes_array = [];
        //     $colors_array = [];

        //     // Loop through sizes and add them to the sizes array
        //     foreach ($sizes as $size) {
        //       $sizes_array[] = $size->size;
        //     }
        //     foreach ($colors as $color) {
        //       $colors_array[] = $color->name;
        //     }

           

        //     $attributes[] = create_attribute('Size', $sizes_array);
        //     $attributes[] = create_attribute('Color', $colors_array);

        //     // $this->varProduct->set_attributes($attributes);

        //     $this->varProduct->set_attributes($attributes);

        //     $save = $this->varProduct->save();

        //     // $variation = new WC_Product_Variation();
        //     // $variation->set_parent_id($this->varProduct->get_id());
        //     // $variation->set_attributes(array('brand' => 'Biz Collection', 'color' => 'Red'));
        //     // $variation->set_regular_price(1000000); // yep, magic hat is quite expensive
        //     // $variation->save();

        //     // $variation = new WC_Product_Variation();
        //     // $variation->set_parent_id($this->varProduct->get_id());
        //     // $variation->set_attributes(array('color' => 'Black', 'brand' => 'Transsd'));
        //     // $variation->set_regular_price(500);
        //     // $variation->save();
        //     $word = str_replace('-', ' ', $data->brand);
        //     $brand = ucwords($word);

        //     wp_set_object_terms($this->varProduct->get_id(), $data->tags, 'product_tag');
        //     wp_set_object_terms($this->varProduct->get_id(), [$brand], 'product_cat');

        //     foreach ($data->colors as $color_data) {
        //       foreach ($color_data->sizes as $size_data) {
        //         $variation = new WC_Product_Variation();
        //         $variation->set_parent_id($this->varProduct->get_id());

        //         // Set variation name (combine color with size)
        //         // $variation->set_name($color_data['name'] . ' (' . $size_data['size'] . ')');

        //         // Set variation attributes
        //         $variation->set_attributes([
        //           'color' => $color_data->name,
        //           'size' => $size_data->size,
        //         ]);

        //         // Set variation price
        //         $variation->set_regular_price($price); 

        //         $variation_image_id = upload_file_by_url($color_data->images[0]->https_attachment_url_product);

        //         // Set variation image (if found)
        //         if ($variation_image_id) {
        //           $variation->set_image_id($variation_image_id);
        //         }
        //         $variation->save();
        //         // $variations[] = $variation;
        //       }
        //     }


        //     // let's suppose that our 'Accessories' category has ID = 19 
        //     // $product->set_category_ids(array(19));
        //     // you can also use $product->set_tag_ids() for tags, brands etc



        //     if ($save) {
        //         $this->varProduct->update_meta_data('aone_upload', 'true');
        //         return true;
        //     }
        //     return;

        // }
        public function import_fashion_woocommerce_product($data, $desc)
        {
            $existingProductId = wc_get_product_id_by_sku($data->slug);

            if ($existingProductId > 0) {
                $this->varProduct = wc_get_product($existingProductId);
                $meta_value = $this->varProduct->get_meta('aone_upload', true);

                if ($meta_value) {
                    return true;
                }
            } else {
                $this->varProduct = new WC_Product_Variable();
            }

            $attributes = [];

            $fabricList = is_array($desc->fabric) ? implode(', ', $desc->fabric) : $desc->fabric;
            $featuresList = is_array($desc->features) ? implode(".  ", $desc->features) : $desc->features;

            $description = "**Fabric:** " . $fabricList . "\n\n**Features:**\n  * " . $featuresList;
            $shortDescription = "Made with " . $fabricList;

            $this->varProduct->set_name($data->name);
            $this->varProduct->set_sku($data->slug);

            $price = null;
            if (!empty($data->prices) && is_array($data->prices) && isset($data->prices[0]->price)) {
                $price = $data->prices[0]->price;
            }
            $this->varProduct->set_regular_price($price);
            $quantity = ($price > 0 || $price != null) ? 500 : 0;
            $this->varProduct->set_stock_quantity($quantity);

            $this->varProduct->set_description($description);
            $this->varProduct->set_short_description($shortDescription);

            $image_id = upload_file_by_url($data->images[0]->https_attachment_url_product);
            $this->varProduct->set_image_id($image_id);

            $sizes_array = array_map(fn($size) => $size->size, $data->colors[0]->sizes);
            $colors_array = array_map(fn($color) => $color->name, $data->colors);

            $attributes[] = create_attribute('Size', $sizes_array);
            $attributes[] = create_attribute('Color', $colors_array);

            $this->varProduct->set_attributes($attributes);
            $save = $this->varProduct->save();

            $brand = ucwords(str_replace('-', ' ', $data->brand));

            wp_set_object_terms($this->varProduct->get_id(), $data->tags, 'product_tag');
            wp_set_object_terms($this->varProduct->get_id(), [$brand], 'product_cat');

            foreach ($data->colors as $color_data) {
                foreach ($color_data->sizes as $size_data) {
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($this->varProduct->get_id());
                    $variation->set_attributes([
                        'color' => $color_data->name,
                        'size' => $size_data->size,
                    ]);
                    $variation->set_regular_price($price);

                    $variation_image_id = upload_file_by_url($color_data->images[0]->https_attachment_url_product);
                    if ($variation_image_id) {
                        $variation->set_image_id($variation_image_id);
                    }
                    $variation->save();
                }
            }

            if ($save) {
                $this->varProduct->update_meta_data('aone_upload', 'true');
                return true;
            }

            return;
        }

        public function import_trends_woocommerce_product($data)
        {
            $existingProductId = wc_get_product_id_by_sku($data->code);
            if ($existingProductId) {
                $this->varProduct = wc_get_product($existingProductId);
                $meta_value = $this->varProduct->get_meta('aone_upload', true);

                if ($meta_value){
                    return true;
                }
                // return true;

                // Update slug (if desired) - Adjust based on your slug source
            } else {
                $this->varProduct = new WC_Product_Variable();
            }

            $attributes = [];

            // $fabricList = implode(', ', $data->description); 
            $fabricList = $data->description; 
            $featuresList = implode(".  ", $data->features);
            $additional_specifications = "";

            foreach ($data->additional_specifications as $spec) {
              $additional_specifications .= $spec->specification . ": " . $spec->description . "; ";
            }

            $description = "**Features:** " . $fabricList . "\n\n**Description:**\n  * " . $featuresList . "\n\n**Additional Specs:**\n  * " . $additional_specifications;

            // Short description with limited features
            $shortDescription = $featuresList;

            

           
            $this->varProduct->set_name($data->name); // product title
            $this->varProduct->set_sku($data->code); // product sku
            $price = $data->pricing->prices[0]->price;
            // $this->varProduct->set_stock_status();

            $this->varProduct->set_regular_price($price); // in current shop currency
            $quantity = ($price > 0) ? 500 : 0;
            $this->varProduct->set_stock_quantity($quantity);

            $this->varProduct->set_description($description);
            $this->varProduct->set_short_description($shortDescription);


          $image_url = "https:" . trim($data->images[0]->link); // Remove leading spaces
          $image_id = upload_file_by_url($image_url);

            $this->varProduct->set_image_id($image_id);

            

            // $sizes = $data->colors[0]->sizes;
            $colors = $data->colours;

            // Initialize an empty array to store sizes
            $colors_array = explode(', ', $colors);

           
            $attributes[] = create_attribute('Color', $colors_array);

            
            $save = $this->varProduct->save();
            $this->varProduct->set_attributes($attributes);

         
            foreach ($data->categories as $category) {
              $name = $category->name;
              
              // Check if appa_parent exists, use it if available
              if (isset($category->appa_parent)) {
                $parent_name = $category->appa_parent;
              } else {
                // If no parent, consider the category itself as the top level
                $parent_name = null;
              }

              create_categories($this->varProduct->get_id(), $name, $parent_name);
            }

            foreach ($colors_array as $color_data) {
              
              $variation = new WC_Product_Variation();
              $variation->set_parent_id($this->varProduct->get_id());

              // Set variation attributes
              $variation->set_attributes([
                'color' => $color_data,
              ]);
              // Set variation price
              $variation->set_regular_price($price); 

              $variation->save();
              
            }

            if ($save) {
                $this->varProduct->update_meta_data('aone_upload', 'true');
                return true;
            }
            return;

        }

        private function domElementToString($element)
        {
            $document = new DOMDocument();
            $document->appendChild($document->importNode($element, true));
            return $document->saveXML();
        }
        public function import_legendlife_woocommerce_product($xml)
        {
            $xml = $this->domElementToString($xml);
            $dom = new DOMDocument();
            $dom->loadXML($xml);

            $data = new DOMXPath($dom);

    
            $productName = get_path_data($data, '//item[key="product_name"]/value');
   
            $productDescription = get_path_data($data, '//item[key="product_description"]/value');
            $shortDescription = get_path_data($data, '//item[key="product_short_description"]/value');
           
            $sku = get_path_data($data, '//item[key="product_style_code"]/value');
            $categoryBrand = get_path_data($data, '//item[key="product_brands"]/value');
            $attributes = [];

            // $fabricList = implode(', ', $data->description); 
            // $fabricList = $data->description; 
            // $featuresList = implode(".  ", $data->features);
            $additional_specifications = "";

            foreach ($data->query('//item[key="product_specifications"]/value/item') as $spec) {
              $additional_specifications .= $spec->nodeValue. "\n";
            }

            $description = "**Description:**\n  * " . $productDescription . "\n\n**Additional Specs:**\n" . $additional_specifications;

           

            $existingProductId = wc_get_product_id_by_sku($sku);
            if ($existingProductId) {
              $this->varProduct = wc_get_product($existingProductId);

              // Update slug (if desired) - Adjust based on your slug source
            } else {
              $this->varProduct = new WC_Product_Variable();
            }

           
            $this->varProduct->set_name($productName); // product title
            $this->varProduct->set_sku($sku); // product sku
            $price = 40;

            $this->varProduct->set_regular_price($price); // in current shop currency
            $quantity = ($price > 0) ? 500 : 0;
            $this->varProduct->set_stock_quantity($quantity);

            $this->varProduct->set_description($description);
            $this->varProduct->set_short_description($shortDescription);

            
            $image_url = get_path_data($data, '//item[key="product_base_image"]/value'); // Remove leading spaces
            $image_id = upload_file_by_url($image_url);

            $this->varProduct->set_image_id($image_id);

            // $sizes = $data->colors[0]->sizes;
            // $colors = $data->colours;

            // Initialize an empty array to store sizes
            foreach($data->query("//item[key='product_colours_available']/value/item") as $color){
                $colors_array[] = $color->nodeValue;
            }
            

           
            $attributes[] = create_attribute('Color', $colors_array);

            $this->varProduct->set_attributes($attributes);

            $save = $this->varProduct->save();

            create_categories($this->varProduct->get_id(), $categoryBrand, null);
            foreach ($data->query('//item[key="product_categorisation"]/value/item') as $key => $category) {
              $name = $category->nodeValue;
              

              create_categories($this->varProduct->get_id(), $name, null);
            }

            $variable_products = $data->query("//item[key='product_skus']/value/item");

            foreach ($variable_products as $key => $color_data) {
              
              $variation = new WC_Product_Variation();
              $variation->set_parent_id($this->varProduct->get_id());



              // Set variation attributes
            //   $color_data =  new DOMXPath($color_data);
              $variation->set_attributes([
                'color' => get_path_data($data, "//item[key='product_colours']/value", $key),
              ]);

              $image_url = get_path_data($data, '//item[key="product_image"]/value', $key); // Remove leading spaces
              $image_id = upload_file_by_url($image_url);

             $variation->set_image_id($image_id);

              // Set variation price
              $variation->set_regular_price($price); 

              $variation->save();
              
            }

            if ($save) {
                return true;
            }
            return;

        }




       
    }



endif;