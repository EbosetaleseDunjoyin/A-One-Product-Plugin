<?php 

// use option;
use Carbon_Fields\Helper\Helper;
/**
 * Utilities
 * 
 * @package probo-products
 */

require_once AONE_PATH . '/includes/api.php';

if(!function_exists('display_admin_notice')):

function display_admin_notice($message, $type = 'success')
{
    add_action('admin_notices', function () use ($message, $type) {
        ?>
          <div class="notice notice-<?php  echo esc_html($type); ?> is-dismissible">
                <p><?php  echo esc_html($message); ?></p>
            </div>
        <?php
    });
}

endif;

if (!function_exists('display_aone_api_notice')):

    // add_action('admin_notices', 'display_aone_api_notice');

    function display_aone_api_notice()
    {
        $notice = get_transient('aone_api_notice');

        if ($notice) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice) . '</p></div>';
            delete_transient('aone_api_notice'); // Remove the transient after displaying the notice
        }
    }

endif;

if(!function_exists('create_attribute')):
    function create_attribute($name, $options, $visible = true, $variation = true)
    {
        $attribute = new WC_Product_Attribute();
        $attribute->set_name($name);
        $attribute->set_options($options);
        $attribute->set_visible($visible);
        $attribute->set_variation($variation);
        return $attribute;
    }
endif;

if(!function_exists('check_categories')):
    function check_categories($product_id, $category)
    {
        
        // Get existing product categories
        $existing_categories = wp_get_post_terms($product_id, 'product_cat');


        // Check if the brand category already exists
        $brand_category_id = term_exists($category, 'product_cat');

        // If brand category doesn't exist, create it
        if (!$brand_category_id) {
            $brand_category_id = wp_create_term($category, 'product_cat');
        } else {
            // Convert the term object to its ID (if retrieved as an object)
            $brand_category_id = $brand_category_id['term_id'];
        }

        // Combine existing categories and the new brand category
        $all_categories = array_merge(wp_list_pluck($existing_categories, 'term_id'), array($brand_category_id));

        // Set the product categories
        wp_set_object_terms($product_id, $all_categories, 'product_cat');
    }
endif;
if(!function_exists('create_categories')):
    function create_categories($product_id, $category, $parent = null)
    {

        $category_id = term_exists($category, 'product_cat', $parent);

        // If the category doesn't exist, create it with the parent (if applicable)
        if (!$category_id) {
            $category_id = wp_insert_term($category, 'product_cat', ['parent' => $parent]);
        }

        // Assign the category to the product
        wp_set_object_terms($product_id, $category, 'product_cat');
    }
endif;


if (!function_exists('upload_file_by_url')):
    function upload_file_by_url($image_url)
    {

        // it allows us to use download_url() and wp_handle_sideload() functions
        require_once (ABSPATH . 'wp-admin/includes/file.php');

        // download to temp dir
        $temp_file = download_url($image_url);

        if (is_wp_error($temp_file)) {
            return false;
        }

        // move the temp file into the uploads directory
        $file = array(
            'name' => basename($image_url),
            'type' => mime_content_type($temp_file),
            'tmp_name' => $temp_file,
            'size' => filesize($temp_file),
        );
        $sideload = wp_handle_sideload(
            $file,
            array(
                'test_form' => false // no needs to check 'action' parameter
            )
        );

        if (!empty($sideload['error'])) {
            // you may return error message if you want
            return false;
        }

        // it is time to add our uploaded image into WordPress media library
        $attachment_id = wp_insert_attachment(
            array(
                'guid' => $sideload['url'],
                'post_mime_type' => $sideload['type'],
                'post_title' => basename($sideload['file']),
                'post_content' => '',
                'post_status' => 'inherit',
            ),
            $sideload['file']
        );

        if (is_wp_error($attachment_id) || !$attachment_id) {
            return false;
        }

        // update medatata, regenerate image sizes
        require_once (ABSPATH . 'wp-admin/includes/image.php');

        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata($attachment_id, $sideload['file'])
        );

        return $attachment_id;

    }
endif;


// if (!function_exists('aone_get_theme_option')) :
//     function aone_get_theme_option($name, $container_id = '')
//     {
//         return Helper::get_theme_option($name, $container_id);
//     }
// endif;
add_action('init', 'aone_get_theme_option_after_fields_registered');

function aone_get_theme_option_after_fields_registered()
{
    if (!function_exists('aone_get_theme_option')) {
        function aone_get_theme_option($name, $container_id = '')
        {
            return Helper::get_theme_option($name, $container_id);
        }
    }
}

add_action('init', array('\\Carbon_Fields\\Carbon_Fields', 'boot'));

if (!function_exists('one_get_theme_option')) {
    function one_get_theme_option($name)
    {
        return get_option("_{$name}");
    }
}

if(!function_exists('get_path_data')):
    function get_path_data($data, $name, $index = 0)
    {

        $productNameNodeList = $data->query($name);
        if ($productNameNodeList->length > 0) {
            return $productNameNodeList->item($index)->nodeValue;
        }
    }
endif;