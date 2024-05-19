<?php
/**
 * ProboClass
 */
namespace AOneProducts\Classes;


defined("ABSPATH") || exit;

// require_once (ABSPATH . 'wp-includes/SimplePie/SimpleXML.php');

use DOMDocument;
use AOneProducts\Classes\Traits\Singleton;

if (!class_exists("LegendLife")):


    class LegendLife
    {

        use Singleton;
        protected $username;
        protected $apikey;
        public $session_id;
        protected $base_url = "https://www.legendlife.com.au:443/index.php/api/soap/";
        public function __construct()
        {
            $this->username = "Public_API_User";
            $this->apikey = "A4FC624A-AF25-4BEB-A6E1-00BF6038C719";

            if (!$this->username && !$this->apikey ) {
                display_admin_notice("No Username and Api Key set for Legend Life", 'error');
            }

            $this->get_login_details();

            // if (!$this->session_id) {
            //     display_admin_notice("No Session id set for Legend Life", 'error');
            // }

        }





        public function get_login_details()
        {
            $api_endpoint = $this->base_url;

            $headers = array(
                'Content-Type' => 'text/xml; charset=utf-8',
            );

            $xml_body = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                  xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
                  xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                  xmlns:urn="urn:Magento">
                <soapenv:Header/>
                <soapenv:Body>
                    <login soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                        <username xsi:type="xsd:string">' . $this->username . '</username>
                        <apiKey xsi:type="xsd:string">' . $this->apikey . '</apiKey> </login>
                </soapenv:Body>
            </soapenv:Envelope>';

            $args = array(
                'headers' => $headers,
                'body' => $xml_body,
            );

            $response = wp_remote_post($api_endpoint, $args);

            if (is_wp_error($response)) {
                display_admin_notice("Error: " . $response->get_error_message(), 'error');
              
            }

            $response_body = wp_remote_retrieve_body($response);
            // $response_body = json_decode($response['body']);

            $doc = new DOMDocument();
            $doc->loadXML($response_body);

            // Get the value of loginReturn
            


            if (!$doc) {
            // if (is_wp_error($xml)) {
                // return "Error: Failed to parse XML response. {$response_body}";
                display_admin_notice("Error: Failed to parse XML response.", 'error');
                // return "Error: " . $response->get_error_message();
            }

            $login_return = $doc->getElementsByTagName('loginReturn')->item(0)->nodeValue;

            if (!$login_return) {
                display_admin_notice("Error: Unable to extract session ID.");
            }

            $this->session_id = $login_return;

            // return $this->session_id;
        }


        public function get_legend_life_products($page = 1, $page_size=100)
        {
            $api_endpoint = $this->base_url;

            $headers = array(
                'Content-Type' => 'text/xml; charset=utf-8',
            );

            $xml_body = '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:Magento">   
                    <soapenv:Header/>   
                    <soapenv:Body>        
                        <urn:call soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">            
                        <sessionId xsi:type="xsd:string">'.$this->session_id.'</sessionId>            
                        <resourcePath xsi:type="xsd:string">epicentre_products.publiclist</resourcePath>            
                        <args xsi:type="xsd:anyType">                
                            <page_number xsi:type="xsd:integer">'.$page.'</page_number>                
                            <page_size xsi:type="xsd:integer">'.$page_size.'</page_size>            
                        </args>        
                        </urn:call>   
                    </soapenv:Body>
                </soapenv:Envelope>';

            $args = array(
                'headers' => $headers,
                'body' => $xml_body,
            );

            $response = wp_remote_post($api_endpoint, $args);

            if (is_wp_error($response)) {
                display_admin_notice("Error: " . $response->get_error_message(), 'error');

            }

            $response_body = wp_remote_retrieve_body($response);
            // $response_body = json_decode($response['body']);

            // $doc = new DOMDocument();
            // $doc->loadXML($response_body);

            if (is_wp_error($response_body)) {
                $error_message = $response->get_error_message();
                return [
                    'status' => false,
                    'message' => "Error Occured: {$error_message}",
                    'data' => null,
                ];
            } else {
                // Successful case (assuming $result contains the data)
                // $result = json_decode($response['body']);
                // return $result;
                return [
                    'status' => true,
                    'message' => 'Data retrieved successfully.',
                    'data' => $response_body,
                ];
            }

        }

        private function domElementToString($element)
        {
            $document = new DOMDocument();
            $document->appendChild($document->importNode($element, true));
            return $document->saveXML();
        }

       
     

    }



endif;