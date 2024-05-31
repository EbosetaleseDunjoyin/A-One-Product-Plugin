<?php
/**
 * ProboClass
 */
namespace AOneProducts\Classes;


defined("ABSPATH") || exit;

use AOneProducts\Classes\Traits\Singleton;

if (!class_exists("Trends")):


    class Trends
    {

        use Singleton;
        protected $username;
        protected $password;
        protected $base_url = "https://au.api.trends.nz/api";
        public function __construct()
        {
            $this->username = "david@a-one.com.au";
            $this->password = "0b9TSRpWEubn";

            if (!$this->username) {
                display_admin_notice("No API key set for Trends", 'error');
            }

        }




        public function get_trends_products($page = 1, $page_size=50)
        {
            $api_endpoint = "{$this->base_url}/v1/products.json?inc_inactive=no&page_no={$page}&page_size={$page_size}";

            $api_key = base64_encode("{$this->username}:{$this->password}");

            $headers = array(
                // 'Content-Type' => 'application/json',
                'Authorization' => "Basic {$api_key}",
            );

            $args = array(
                'headers' => $headers,
                'timeout' => 10000
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