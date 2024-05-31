<?php 
/**
 * ProboClass
 */
namespace AOneProducts\Classes;


defined("ABSPATH") || exit;

use AOneProducts\Classes\Traits\Singleton;

if(!class_exists("FashionBiz")):
    
    
    class FashionBiz
    {

        use Singleton;
        protected $api_key;
        protected $base_url = "https://www.fashionbizapis.com/api/v3/products/biz-collection/au";
        public function __construct(){
            $this->api_key = "a7cb63433b3fb6020eadb6b49c07678e84650a11";

            if(!$this->api_key){
                display_admin_notice("No API key set for FashionBiz", 'error');
            }

        }

       

        // public function get_fashion_biz_products($page = 1) {
        //     $api_endpoint = "{$this->base_url}?page={$page}";
        //     // $api_endpoint = "https://api.proboprints.com/products?page={$page}&per_page={$per_page}";

        //     $headers = array(
        //         // "Authorization: Api-key vsai-pk-f71d129a-be49-4d49-bf32-39a0e74420d3",
        //         // "Content-Type: multipart/form-data",
        //         "Content-Type:application/json",
        //         "Authorization: Token {$this->api_key}",
        //     );

        //     $curl = curl_init($api_endpoint);

        //     // Set cURL options for GET request
        //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($curl, CURLOPT_HTTPGET, true);

        //     // Set additional headers if needed
        //     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        //     // Execute cURL session
        //     $result = curl_exec($curl);

        //     // Check for cURL errors
        //     $error_message = "";
        //     if (curl_errno($curl)) {
        //         $error_message = curl_error($curl);
        //         // Handle cURL error
        //     }

        //     // Close cURL session
        //     curl_close($curl);





        //     $result = json_decode($result);

        //     return $result;
        // }
        public function get_fashion_biz_products($page = 1) {
            $api_endpoint = "{$this->base_url}?page={$page}";

            $headers = array(
                'Content-Type' => 'application/json',
                'Authorization' => "Token {$this->api_key}",
            );

            $args = array(
                'headers' => $headers,
                'timeout' => 30000
            );

            $response = wp_remote_get($api_endpoint, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log($error_message);
                return [
                    'status' => false,
                    'message' => "Error Occured: {$error_message}",
                    'data' => null,
                ];

            } else {
                // Successful case (assuming $result contains the data)
                $result = json_decode($response['body']);
                // return $result;
                return [
                    'status' => true,
                    'message' => 'Data retrieved successfully.',
                    'data' => $result,
                ];
            }
            
        }
        
        // public function get_fashion_biz_single_product($slug) {
        //     $api_endpoint = "{$this->base_url}/{$slug}";
        //     // $api_endpoint = "https://api.proboprints.com/products?page={$page}&per_page={$per_page}";

        //     $headers = array(
        //         // "Authorization: Api-key vsai-pk-f71d129a-be49-4d49-bf32-39a0e74420d3",
        //         // "Content-Type: multipart/form-data",
        //         "Content-Type:application/json",
        //         "Authorization: Token {$this->api_key}",
        //     );

        //     $curl = curl_init($api_endpoint);

        //     // Set cURL options for GET request
        //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //     curl_setopt($curl, CURLOPT_HTTPGET, true);

        //     // Set additional headers if needed
        //     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        //     // Execute cURL session
        //     $result = curl_exec($curl);

        //     // Check for cURL errors
        //     $error_message = "";
        //     if (curl_errno($curl)) {
        //         $error_message = curl_error($curl);
        //         // Handle cURL error
        //     }

        //     // Close cURL session
        //     curl_close($curl);





        //     $result = json_decode($result);

        //     return $result;
        // }

         public function get_fashion_biz_single_products($slug) {
            $api_endpoint = "{$this->base_url}/{$slug}";

            $headers = array(
                'Content-Type' => 'application/json',
                'Authorization' => "Token {$this->api_key}",
            );

            $args = array(
                'headers' => $headers,
                'timeout' => 3000
                
            );

            $response = wp_remote_get($api_endpoint, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                return [
                    'status' => false,
                    'message' => "Error Occured: {$error_message}",
                    'data' => null,
                ];
            } else {
                // Successful case (assuming $result contains the data)
                $result = json_decode($response['body']);
                // return $result;
                return [
                    'status' => true,
                    'message' => 'Data retrieved successfully.',
                    'data' => $result,
                ];
            }
            
        }
      
    }
    


endif;