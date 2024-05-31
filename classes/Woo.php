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


        public function import_fashion_woocommerce_product($data, $desc)
        {
            $existingProductId = wc_get_product_id_by_sku($data->slug);

            if ($existingProductId > 0) {
                $this->varProduct = wc_get_product($existingProductId);
                $meta_value = $this->varProduct->get_meta('aone_upload', true);

                if ($meta_value) {
                    // return true;
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
            if (!empty($data->prices)) {
                if (is_array($data->prices)) {
                    if (isset($data->prices[0]->price)) {
                        $price = $data->prices[0]->price;
                    }
                }
            }
            // $this->varProduct->set_regular_price($price);
            $quantity = ($price > 0 || $price != null) ? 500 : 0;
            $this->varProduct->set_stock_quantity($quantity);

            $this->varProduct->set_description($description);
            $this->varProduct->set_short_description($shortDescription);

            $image_id = upload_file_by_url($data->images[0]->https_attachment_url_product);
            $this->varProduct->set_image_id($image_id);
            $image_ids = [];
            foreach ($data->colors as $color_data) {
                

                $variation_image_id = upload_file_by_url($color_data->images[0]->https_attachment_url_product);
                if ($variation_image_id) {
                    $image_ids[] = $variation_image_id;
                }
                
            }
            if(count($image_ids) > 0){
                $this->varProduct->set_gallery_image_ids( $image_ids );
            }

            


            $sizes_array = array_map(fn($size) => $size->size, $data->colors[0]->sizes);
            $colors_array = array_map(fn($color) => $color->name, $data->colors);

            $attributes[] = create_attribute('Size', $sizes_array);
            $attributes[] = create_attribute('Color', $colors_array);

            $this->varProduct->set_attributes($attributes);
            $save = $this->varProduct->save();

            
            wp_set_object_terms($this->varProduct->get_id(), $data->tags, 'product_tag');
            $brand = ucwords(str_replace('-', ' ', $data->brand));
            wp_set_object_terms($this->varProduct->get_id(), [$brand], 'product_cat');

            foreach ($data->colors as $color_data) {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($this->varProduct->get_id());
                $variation->set_attributes([
                    'color' => $color_data->name,
                ]);
                // $variation->set_regular_price($price);

                $variation_image_id = upload_file_by_url($color_data->images[0]->https_attachment_url_product);
                if ($variation_image_id) {
                    $variation->set_image_id($variation_image_id);
                }
                $variation->save();
            }
            foreach ($sizes_array as $size_data) {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($this->varProduct->get_id());
                $variation->set_attributes([
                    'size' => $size_data,
                ]);
                // $variation->set_regular_price($price);

                // $variation_image_id = upload_file_by_url($color_data->images[0]->https_attachment_url_product);
                // if ($variation_image_id) {
                //     $variation->set_image_id($variation_image_id);
                // }
                $variation->save();
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
                    // return true;
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

            // $this->varProduct->set_regular_price($price); // in current shop currency
            $quantity = ($price > 0) ? 500 : 0;
            $this->varProduct->set_stock_quantity($quantity);

            $this->varProduct->set_description($description);
            $this->varProduct->set_short_description($shortDescription);


            $image_url = "https:" . trim($data->images[0]->link); // Remove leading spaces
            $image_id = upload_file_by_url($image_url);

            $this->varProduct->set_image_id($image_id);

            $image_ids = [];
            if(count($data->images) > 1){

                foreach ($data->images as $key => $color_data) {
    
                    if($key == 0) continue;
    
                    $image_url = "https:" . trim($color_data->link);
                    $variation_image_id = upload_file_by_url($image_url);
                    if ($variation_image_id) {
                        $image_ids[] = $variation_image_id;
                    }
    
                }
            }
            if (count($image_ids) > 0) {
                $this->varProduct->set_gallery_image_ids($image_ids);
            }
            
            

            // $sizes = $data->colors[0]->sizes;
            $colors = $data->colours;

            // Initialize an empty array to store sizes
            $colors_array = explode(', ', $colors);

           
            $attributes[] = create_attribute('Color', $colors_array);

            
            $save = $this->varProduct->save();
            $this->varProduct->set_attributes($attributes);

         
            // foreach ($data->categories as $category) {
            //   $name = $category->name;
              
            //   // Check if appa_parent exists, use it if available
            //   if (isset($category->appa_parent)) {
            //     $parent_name = $category->appa_parent;
            //     create_categories($this->varProduct->get_id(), $parent_name, null);
            //   } else {
            //     // If no parent, consider the category itself as the top level
            //     $parent_name = null;
            //   }

            //   create_categories($this->varProduct->get_id(), $name, $parent_name);
            // }

            foreach ($data->categories as $category) {
                $name = $category->name;

                // Check if appa_parent exists, use it if available
                if (isset($category->appa_parent)) {
                    $parent_name = $category->appa_parent;
                    // Create parent category if it doesn't exist
                   
                    wp_set_object_terms($this->varProduct->get_id(), [$parent_name, $name], 'product_cat');
                } else {
                    // If no parent, set parent ID to 0 (top level)
                    wp_set_object_terms($this->varProduct->get_id(), [$name], 'product_cat', true);
                }

                // Create category if it doesn't exist

                // Set product categories

            }


            foreach ($colors_array as $color_data) {
              
              $variation = new WC_Product_Variation();
              $variation->set_parent_id($this->varProduct->get_id());

              // Set variation attributes
              $variation->set_attributes([
                'color' => $color_data,
              ]);
              // Set variation price
            //   $variation->set_regular_price($price); 

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
                $meta_value = $this->varProduct->get_meta('aone_upload', true);

                if ($meta_value) {
                    // return true;
                }
              // Update slug (if desired) - Adjust based on your slug source
            } else {
              $this->varProduct = new WC_Product_Variable();
            }

           
            $this->varProduct->set_name($productName); // product title
            $this->varProduct->set_sku($sku); // product sku
            $price = 40;

            // $this->varProduct->set_regular_price($price); // in current shop currency
            $quantity = ($price > 0) ? 500 : 0;
            $this->varProduct->set_stock_quantity($quantity);

            $this->varProduct->set_description($description);
            $this->varProduct->set_short_description($shortDescription);

            
            $image_url = get_path_data($data, '//item[key="product_base_image"]/value'); // Remove leading spaces
            $image_id = upload_file_by_url($image_url);

            $this->varProduct->set_image_id($image_id);

            if (count($data->query("//item[key='product_alt_images']/value/item")) > 0) {

                foreach ($data->query("//item[key='product_alt_images']/value/item") as $key => $images) {


                    // $image_url = "https:" . trim($images->nodeValue);
                    $variation_image_id = upload_file_by_url($images->nodeValue);
                    if ($variation_image_id) {
                        $image_ids[] = $variation_image_id;
                    }

                }
            }
            if (count($image_ids) > 0) {
                $this->varProduct->set_gallery_image_ids($image_ids);
            }
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
            //   $variation->set_regular_price($price); 

              $variation->save();
              
            }

            if ($save) {
                $this->varProduct->update_meta_data('aone_upload', 'true');
                return true;
            }
            return;

        }




       
    }



endif;