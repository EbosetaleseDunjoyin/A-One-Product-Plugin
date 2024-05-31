<?php
/**
 * ProboClass
 */
namespace AOneProducts\Classes;



require_once ABSPATH . "/wp-load.php";
require_once AONE_PATH . '/classes/traits/Singleton.php';

// require_once(ABSPATH . "/wp-content/plugins/woocommerce/includes/class-wc-product.php");


defined('ABSPATH') || exit;

require AONE_PATH . '/vendor/autoload.php';

use DOMXPath;

use Exception;
use WC_Product;
use DOMDocument;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Attribute;
use WC_Product_Variation;
use Automattic\WooCommerce\Client;
use AOneProducts\Classes\Traits\Singleton;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if (!class_exists("Woo")):

    class WooApi
    {
        use Singleton;
        protected $consumer_key = "ck_73cde9d7d03a02c83263aa7a53308924ac6cd12a";
        protected $consumer_secret = "cs_91a587cbbafc61f8e3f3824117b2e3a2d4b2179e";
        protected $varProduct;
        protected $productAttribute;
        public $woocommerce;
        protected $fashionbiz;
        // protected $woocommerce;
        protected $data;
        public function __construct()
        {
            // $this->varProduct  = new WC_Product_Variable();
            // $this->productAttribute  = new  WC_Product_Attribute();
            $base_url = "http://woocomerce.local/";
            // $base_url = site_url();

            $this->woocommerce = new Client(
                $base_url,
                $this->consumer_key,
                $this->consumer_secret,
                [
                    'version' => 'wc/v3',
                    'timeout' => 30000
                ]
            );

            $this->fashionbiz = FashionBiz::get_instance();


            // $this->setup_class();

            
        }
     

        public function setup_class(){
            // add_action('admin_notices', array($this, 'display_admin_notice'));
        }


        public function get_batch_fashionBizss_products($products)
        {
            try {
                $formattedProducts = array_map(function ($product) {
                    $single = $this->fashionbiz->get_fashion_biz_single_products($product->slug);

                    $singleResult = $single['data'];
                    $desc = $singleResult->description;

                    $fabricList = '';
                    $featuresList = '';

                    if (is_array($desc->fabric)) {
                        $fabricList = implode(', ', $desc->fabric);
                    } else {
                        $fabricList = $desc->fabric;
                    }

                    if (is_array($desc->features)) {
                        $featuresList = implode(". ", $desc->features); // Use ". " for cleaner separation
                    } else {
                        $featuresList = $desc->features;
                    }

                    $description = "**Fabric:** " . $fabricList . "\n\n**Features:**\n  * " . $featuresList;

                    // Short description with limited features (adjust limit as needed)
                    $short_description = "Made with " . (strlen($fabricList) > 100 ? substr($fabricList, 0, 100) . '...' : $fabricList);
                    $price = null;
                    if (!empty ($product->prices)) {
                        if (is_array($product->prices)) {
                            if (isset ($product->prices[0]->price)) {
                                $price = $product->prices[0]->price;
                            }
                        }
                    }
                    $sizes_array = array_map(fn($size) => $size->size, $product->colors[0]->sizes);
                    $colors_array = array_map(fn($color) => $color->name, $product->colors);

                    // Generating variations
                    $variations = [];
                    foreach ($colors_array as $color) {
                        foreach ($sizes_array as $size) {
                            $variations[] = [
                                'regular_price' => $price,
                                'attributes' => [
                                    [
                                        "name" => "Color",
                                        "option" => $color
                                    ],
                                    [
                                        "name" => "Size",
                                        "option" => $size
                                    ]
                                ]
                            ];
                        }
                    }

                    return [
                        "name" => $product->name,
                        "sku" => $product->slug,
                        'type' => 'variable',
                        "description" => $description ?? '',
                        "short_description" => $short_description ?? '',
                        "images" => array_map(function ($image) {
                            return [
                                "src" => $image->https_attachment_url_product,
                            ];
                        }, $product->images),
                        "categories" => [
                            [
                                'name' => 'Biz Collections'
                            ]
                        ],
                        'regular_price' => $price,
                        "tags" => array_map(function ($tag) {
                            return [
                                "name" => $tag
                            ];
                        }, $product->tags),
                        "attributes" => [
                            [
                                "name" => "Size",
                                "options" => $sizes_array
                            ],
                            [
                                "name" => "Color",
                                "options" => $colors_array
                            ]
                        ],
                        "variations" => $variations
                    ];
                }, $products);

                $data = [
                    "create" => $formattedProducts
                ];

                $this->woocommerce->post('products/batch', $data);

                return [
                    'status' => true,
                    'message' => "Batch upload successful"
                ];

            } catch (HttpClientException $e) {
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                    'data' => $formattedProducts
                ];
            }
        }


        public function upload_fashion_biz_product($product){
            try{
                $existingProductId = wc_get_product_id_by_sku($product->slug);

                // Check if the product exists
                // if ($existingProductId > 0) {
                //     $varProduct = wc_get_product($existingProductId);
                //     $meta_value = $varProduct->get_meta('aone_upload', true);

                //     // If the product has already been uploaded, skip the import
                //     if ($meta_value) {
                //         return true;
                //     }
                // }
            
                $single = $this->fashionbiz->get_fashion_biz_single_products($product->slug);



                $singleResult = $single['data'];
                $desc = $singleResult->description;

                $fabricList = '';
                $featuresList = '';

                if (is_array($desc->fabric)) {
                    $fabricList = implode(', ', $desc->fabric);
                } else {
                    $fabricList = $desc->fabric;
                }

                if (is_array($desc->features)) {
                    $featuresList = implode(". ", $desc->features); // Use ". " for cleaner separation
                } else {
                    $featuresList = $desc->features;
                }

                $description = "**Fabric:** " . $fabricList . "\n\n**Features:**\n  * " . $featuresList;

                // Short description with limited features (adjust limit as needed)
                $short_description = "Made with " . (strlen($fabricList) > 100 ? substr($fabricList, 0, 100) . '...' : $fabricList);
                $price = null;
                if (!empty($product->prices)) {
                    if (is_array($product->prices)) {
                        if (isset($product->prices[0]->price)) {
                            $price = $product->prices[0]->price;
                        }
                    }
                }
                $sizes_array = array_map(fn($size) => $size->size, $product->colors[0]->sizes);
                $colors_array = array_map(fn($color) => $color->name, $product->colors);

              

                $productData = [
                    "name" => $product->name,
                    "sku" => $product->slug,
                    'type' => 'variable',
                    "description" => $description ?? '',
                    "short_description" => $short_description ?? '',
                    "images" => array_map(function ($image) {
                        return [
                            "src" => $image->https_attachment_url_product,
                        ];
                    }, $product->images),
                    
                    'regular_price' => $price,
                    "tags" => array_map(function ($tag) {
                        return [
                            "name" => $tag
                        ];
                    }, $product->tags),
                    "attributes" => [
                        [
                            "name" => "Size",
                            "options" => $sizes_array
                        ],
                        [
                            "name" => "Color",
                            "options" => $colors_array
                        ]
                    ],
                    // "variations" => $variations
                ];
               
                if($existingProductId > 0){
                    $savedProduct = $this->woocommerce->put("products/{$existingProductId}", $productData);
                }else{
                    $savedProduct = $this->woocommerce->post('products', $productData);
                }

                $brand = ucwords(str_replace('-', ' ', $product->brand));
                wp_set_object_terms($savedProduct->id, [$brand], 'product_cat');

                $this->add_product_variation($savedProduct->id,  $product);


                return [
                    'status' => true,
                    'message' => "Upload successful",
                    'data' => $savedProduct
                ];

            } catch (HttpClientException $e) {
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                    'data' => $productData
                ];
            }


        }

        public function get_batch_fashionBizs_products($products)
        {
            try {
                // Fetch all existing products' SKUs in one query
                $existingProducts = wc_get_products(['limit' => -1, 'return' => 'ids']);
                $existingSkus = [];
                foreach ($existingProducts as $productId) {
                    $product = wc_get_product($productId);
                    $existingSkus[$product->get_sku()] = $productId;
                }

                $createArray = [];
                $updateArray = [];

                foreach ($products as $product) {
                    $single = $this->fashionbiz->get_fashion_biz_single_products($product->slug);
                    $singleResult = $single['data'];
                    $desc = $singleResult->description;

                    $fabricList = '';
                    $featuresList = '';

                    if (is_array($desc->fabric)) {
                        $fabricList = implode(', ', $desc->fabric);
                    } else {
                        $fabricList = $desc->fabric;
                    }

                    if (is_array($desc->features)) {
                        $featuresList = implode(". ", $desc->features); // Use ". " for cleaner separation
                    } else {
                        $featuresList = $desc->features;
                    }

                    $description = "**Fabric:** " . $fabricList . "\n\n**Features:**\n  * " . $featuresList;

                    // Short description with limited features (adjust limit as needed)
                    $short_description = "Made with " . (strlen($fabricList) > 20 ? substr($fabricList, 0, 20) . '...' : $fabricList);
                    $price = null;
                    if (!empty($product->prices)) {
                        if (is_array($product->prices)) {
                            if (isset($product->prices[0]->price)) {
                                $price = $product->prices[0]->price;
                            }
                        }
                    }
                    $sizes_array = array_map(fn($size) => $size->size, $product->colors[0]->sizes);
                    $colors_array = array_map(fn($color) => $color->name, $product->colors);

                    $productData = [
                        "name" => $product->name,
                        "sku" => $product->slug,
                        'type' => 'simple',
                        // 'type' => 'variable',
                        "description" => $description ?? '', // Assuming 'brand' is a custom field
                        "short_description" => $short_description ?? '', // Assuming 'original_brand' is a custom field
                        "images" => array_map(function ($image) {
                            return [
                                "src" => $image->https_attachment_url_product,
                            ];
                        }, $product->images),
                        "categories" => [
                            ["name" => "Biz Collection"]
                        ],
                        'regular_price' => $price,
                        "tags" => array_map(function ($tag) {
                            return [
                                "name" => $tag
                            ];
                        }, $product->tags),
                        "attributes" => [
                            [
                                "name" => "sizes",
                                "options" => $sizes_array
                            ],
                            [
                                "name" => "colors",
                                "options" => $colors_array
                            ]
                        ],
                        "variations" => []
                    ];

                    if (isset($existingSkus[$product->slug])) {
                        $productData['id'] = $existingSkus[$product->slug];
                        $updateArray[] = $productData;
                    } else {
                        $createArray[] = $productData;
                    }
                }

                $data = [
                    "create" => $createArray,
                    "update" => $updateArray
                ];

                $this->woocommerce->post('products/batch', $data);

                return [
                    'status' => true,
                    'message' => "Batch upload successful"
                ];

            } catch (HttpClientException $e) {
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                    'data' => $data
                ];
            }
        }

        public function get_batch_fashionBiz_products($products)
        {
            try {
                $batchProducts = [];

                foreach ($products as $product) {
                    // Fetch the single product data
                    $single = $this->fashionbiz->get_fashion_biz_single_products($product->slug);
                    if (!isset($single['data'])) {
                        continue; // Skip if no data found
                    }

                    $singleResult = $single['data'];
                    $desc = $singleResult->description;

                    $fabricList = is_array($desc->fabric) ? implode(', ', $desc->fabric) : $desc->fabric;
                    $featuresList = is_array($desc->features) ? implode(". ", $desc->features) : $desc->features;

                    $description = "**Fabric:** " . $fabricList . "\n\n**Features:**\n  * " . $featuresList;
                    $short_description = "Made with " . (strlen($fabricList) > 100 ? substr($fabricList, 0, 100) . '...' : $fabricList);
                    $price = !empty($product->prices) && is_array($product->prices) && isset($product->prices[0]->price) ? $product->prices[0]->price : null;

                    $sizes_array = array_map(fn($size) => $size->size, $product->colors[0]->sizes);
                    $colors_array = array_map(fn($color) => $color->name, $product->colors);

                    // Prepare product data
                    $productData = [
                        "name" => $product->name,
                        "sku" => $product->slug,
                        'type' => 'variable',
                        "description" => $description,
                        "short_description" => $short_description,
                        "images" => array_map(function ($image) {
                            return [
                                "src" => $image->https_attachment_url_product,
                            ];
                        }, $product->images),
                        "categories" => [
                            [
                                'name' => 'Biz Collections'
                            ],
                            [
                                'name' => 'Clothes'
                            ]
                        ],
                        "tags" => array_map(function ($tag) {
                            return [
                                "name" => $tag
                            ];
                        }, $product->tags),
                        "attributes" => [
                            [
                                "name" => "Size",
                                "options" => $sizes_array
                            ],
                            [
                                "name" => "Color",
                                "options" => $colors_array
                            ]
                        ]
                    ];

                    // Check if the product exists
                    $existingProductId = wc_get_product_id_by_sku($product->slug);
                    if ($existingProductId) {
                        $productData['id'] = $existingProductId;
                        $batchProducts['update'][] = $productData;
                    } else {
                        $batchProducts['create'][] = $productData;
                    }
                }

                // Perform the batch create/update
                $response = $this->woocommerce->post('products/batch', $batchProducts);

                // Process the response to add variations
                foreach ($response->create as $createdProduct) {
                    $this->add_variations_to_product($createdProduct->id, $createdProduct->attributes, $products);
                }

                foreach ($response->update as $updatedProduct) {
                    $this->add_variations_to_product($updatedProduct->id, $updatedProduct->attributes, $products);
                }

                return [
                    'status' => true,
                    'message' => "Batch upload successful"
                ];

            } catch (HttpClientException $e) {
                return [
                    'status' => false,
                    'message' => $e->getMessage()
                ];
            }
        }




        private function add_variations_to_product($productId, $attributes, $products)
        {
            foreach ($products as $product) {
                if (wc_get_product_id_by_sku($product->slug) == $productId) {
                    $sizes_array = array_map(fn($size) => $size->size, $product->colors[0]->sizes);
                    $colors_array = array_map(fn($color) => $color->name, $product->colors);

                    $variations = [];
                    foreach ($colors_array as $color) {
                        foreach ($sizes_array as $size) {
                            $variationAttributes = [];
                            foreach ($attributes as $attribute) {
                                if ($attribute['name'] == 'Color' && in_array($color, $attribute['options'])) {
                                    $variationAttributes[] = [
                                        'id' => $attribute['id'],
                                        'option' => $color
                                    ];
                                }
                                if ($attribute['name'] == 'Size' && in_array($size, $attribute['options'])) {
                                    $variationAttributes[] = [
                                        'id' => $attribute['id'],
                                        'option' => $size
                                    ];
                                }
                            }

                            $variations[] = [
                                'regular_price' => $product->prices[0]->price ?? null,
                                'attributes' => $variationAttributes
                            ];
                        }
                    }

                    $this->woocommerce->post("products/{$productId}/variations/batch", ['create' => $variations]);
                }
            }
        }
       
        private function add_products_variation($productId, $product)
        {
            $price = null;
            if (!empty($product->prices)) {
                if (is_array($product->prices)) {
                    if (isset($product->prices[0]->price)) {
                        $price = $product->prices[0]->price;
                    }
                }
            }

            foreach ($product->colors as $color_data) {
                foreach ($color_data->sizes as $size_data) {
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($productId);
                    $variation->set_attributes([
                        'color' => $color_data->name,
                        'size' => $size_data->size,
                    ]);
                    $variation->set_regular_price($price);

                  
                    $variation->save();
                }
            }
            
        }

        private function add_product_variation($productId, $product)
        {
            // Fetch the price if available
            $price = null;
            if (!empty($product->prices) && is_array($product->prices)) {
                $price = $product->prices[0]->price ?? null;
            }

            // Prepare variations to be added
            $variations = [];

            foreach ($product->colors as $color_data) {
                foreach ($color_data->sizes as $size_data) {
                    $variation_attributes = [
                        'color' => $color_data->name,
                        'size' => $size_data->size,
                    ];

                    $variations[] = [
                        'parent_id' => $productId,
                        'attributes' => $variation_attributes,
                        'regular_price' => $price,
                    ];
                }
            }

            // Add variations in a batch
            foreach ($variations as $variation_data) {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($variation_data['parent_id']);
                $variation->set_attributes($variation_data['attributes']);
                if ($variation_data['regular_price'] !== null) {
                    $variation->set_regular_price($variation_data['regular_price']);
                }
                $variation->save();
            }
        }





        public function import_fashion_woocommerce_product($data,$desc)
        {
            $existingProductId = wc_get_product_id_by_sku($data->slug);
            if ($existingProductId > 0) {

                $this->varProduct = wc_get_product($existingProductId);
                $meta_value = $this->varProduct->get_meta('aone_upload', true);

                if ($meta_value) {return true; }
                return true;
                // Update slug (if desired) - Adjust based on your slug source
            } else {
                $this->varProduct = new WC_Product_Variable();
            }

            $attributes = [];

            $fabricList = is_array($desc->fabric) ? implode(', ', $desc->fabric) : $desc->fabric; 
            $featuresList = is_array($desc->features) ? implode(".  ", $desc->features) : $desc->features; 

            $description = "**Fabric:** " . $fabricList . "\n\n**Features:**\n  * " . $featuresList;

            // Short description with limited features
            $shortDescription = "Made with " . $fabricList;

           
           
            $this->varProduct->set_name($data->name); // product title
            $this->varProduct->set_sku($data->slug); // product sku
            
            // $price = !empty($data->prices) && isset($data->prices[0]->price) ? $data->prices[0]->price : 0;
            $price = null;
            if (!empty($data->prices)) {
                if (is_array($data->prices)) {
                    if (isset($data->prices[0]->price)) {
                        $price = $data->prices[0]->price;
                    }
                } 
            }
            $this->varProduct->set_regular_price($price); // in current shop currency
            $quantity = ($price > 0 || $price != null) ? 500 : 0;
            $this->varProduct->set_stock_quantity($quantity);

            $this->varProduct->set_description($description);
            $this->varProduct->set_short_description($shortDescription);
            // you can also add a full product description
            // $this->varProduct->set_description( 'long description here...' );

            $image_id = upload_file_by_url($data->images[0]->https_attachment_url_product);

            $this->varProduct->set_image_id($image_id);

            

            $sizes = $data->colors[0]->sizes;
            $colors = $data->colors;

            // Initialize an empty array to store sizes
            $sizes_array = [];
            $colors_array = [];

            // Loop through sizes and add them to the sizes array
            foreach ($sizes as $size) {
              $sizes_array[] = $size->size;
            }
            foreach ($colors as $color) {
              $colors_array[] = $color->name;
            }

           

            $attributes[] = create_attribute('Size', $sizes_array);
            $attributes[] = create_attribute('Color', $colors_array);

            // $this->varProduct->set_attributes($attributes);

            $this->varProduct->set_attributes($attributes);

            $save = $this->varProduct->save();

            // $variation = new WC_Product_Variation();
            // $variation->set_parent_id($this->varProduct->get_id());
            // $variation->set_attributes(array('brand' => 'Biz Collection', 'color' => 'Red'));
            // $variation->set_regular_price(1000000); // yep, magic hat is quite expensive
            // $variation->save();

            // $variation = new WC_Product_Variation();
            // $variation->set_parent_id($this->varProduct->get_id());
            // $variation->set_attributes(array('color' => 'Black', 'brand' => 'Transsd'));
            // $variation->set_regular_price(500);
            // $variation->save();
            $word = str_replace('-', ' ', $data->brand);
            $brand = ucwords($word);

            wp_set_object_terms($this->varProduct->get_id(), $data->tags, 'product_tag');
            wp_set_object_terms($this->varProduct->get_id(), [$brand], 'product_cat');

            foreach ($data->colors as $color_data) {
              foreach ($color_data->sizes as $size_data) {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($this->varProduct->get_id());

                // Set variation name (combine color with size)
                // $variation->set_name($color_data->name . ' (' . $size_data->size . ')');

                // Set variation attributes
                $variation->set_attributes([
                  'color' => $color_data->name,
                  'size' => $size_data->size,
                ]);

                // Set variation price
                $variation->set_regular_price($price); 

                $variation_image_id = upload_file_by_url($color_data->images[0]->https_attachment_url_product);

                // Set variation image (if found)
                if ($variation_image_id) {
                  $variation->set_image_id($variation_image_id);
                }
                $variation->save();
                // $variations[] = $variation;
              }
            }


            // let's suppose that our 'Accessories' category has ID = 19 
            // $product->set_category_ids(array(19));
            // you can also use $product->set_tag_ids() for tags, brands etc



            if ($save) {
                $this->varProduct->update_meta_data('aone_upload', 'true');
                return true;
            }
            return;

        }


        




       
    }



endif;