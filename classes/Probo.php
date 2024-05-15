<?php 
/**
 * ProboClass
 */
namespace AOneProducts\Classes;


defined("ABSPATH") || exit;

use AOneProducts\Classes\Traits\Singleton;

if(!class_exists("Probo")):
    
    
    class Probo
    {

        use Singleton;
        protected $api_key;
        public function __construct(){
            $this->api_key = 'assdsd';

            if(!$this->api_key){
                display_admin_notice("No API key set for Probo", 'error');
            }

        }

       

        public function get_probo_product($per_page = 20, $page = 1) {
            $api_endpoint = "https://api.proboprints.com/products?page={$page}&per_page={$per_page}";

            $headers = array(
                // "Authorization: Api-key vsai-pk-f71d129a-be49-4d49-bf32-39a0e74420d3",
                // "Content-Type: multipart/form-data",
                "Authorization: Bearer {$this->api_key}",
            );

            $curl = curl_init($api_endpoint);

            // Set cURL options for GET request
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPGET, true);

            // Set additional headers if needed
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            // Execute cURL session
            $result = curl_exec($curl);

            // Check for cURL errors
            $error_message = "";
            if (curl_errno($curl)) {
                $error_message = curl_error($curl);
                // Handle cURL error
            }

            // Close cURL session
            curl_close($curl);





            $result = json_decode($result);

            return $result;
        }
        public function save_probo_product_order($request) {
            $api_endpoint = "https://api.proboprints.com/order";

            $headers = array(
                // "Authorization: Api-key vsai-pk-f71d129a-be49-4d49-bf32-39a0e74420d3",
                "Content-Type: application/json",
                "Authorization: Bearer {$this->api_key}",
            );


            $currentUserId = get_current_user_id();
            $currentEmail = get_the_author_meta('user_email', $currentUserId);

            // Generate dynamic values for "id" and "reference" fields
            $id = $currentUserId . '_' . $currentEmail . '_' . time();
            $reference = $currentEmail . '_' . time();

            $body = array(
                "order_type" => "test",
                "id" => $id,
                "reference" => $reference,
                "contact_email" => $currentEmail,
                "deliveries" => $request['deliveries'],
                "products" => $request['products']
            );

            $curl = curl_init($api_endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            $result = curl_exec($curl);
            $error_message = "";
            if (curl_errno($curl)) {
                $error_message = curl_error($curl);
                // Handle cURL error
            }

            // Close cURL session
            curl_close($curl);





            $result = json_decode($result);

            return $result;
        }
    }
    


endif;