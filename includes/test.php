<?php


if (!class_exists("Test")):

class Test
{


    private $base_url = "https://api-stg.timepayment.com";
    public $email;

    public $token;
    public $fields;

    private $dealer_code = "VTP4V";
    private $formId = 4635;

    public function __construct(){
        $this->setup();
    }

    public function setup(){
        add_action("rest_api_init", [$this,"create_rest_endpoint"]);
        // add_action('wpforms_process_before', [$this,'wpf_dev_process_complete'], 10, 3);
        add_action('wpforms_process', [$this,'wpf_dev_process_complete'], 10, 3);
        // add_action('wpforms_process_complete', [$this,'wpf_dev_process_complete'], 10, 4);
        $this->get_token();

    }

    public function create_rest_endpoint()
    {

        register_rest_route(
            'v1/kineticFunding',
            '/addApplication',
            array(
                'methods' => 'GET',
                'callback' => [$this,'saveFieldsToSoapApi'],
                'permission_callback' => '__return_true'
            )
        );



    }

    private function get_token()
    {
        $api_endpoint = "{$this->base_url}/oauth2/token";

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $body = [
            "grant_type" => "password",
            "client_id" => "8ce74d8caf6a499d8d43939c52471bb2",
            "authentication_type" => "Individual",
            "username" => "ewells@kinetic-funding.com",
            "password" => "+t;ZSxu5jps5%Sj"
        ];

        $args = [
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 3000
        ];

        $response = wp_remote_post($api_endpoint, $args);

        if (is_wp_error($response)) {
            $this->display_message("Error: " . $response->get_error_message(), 'error');
            return;
        }

        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body);

        if (isset($response_data->access_token)) {
            $this->token = $response_data->access_token;
        } else {
            $this->display_message("Error: Unable to retrieve access token.", 'error');
        }
    }

    public function add_application_to_timestamp()
    {

        $api_endpoint = "{$this->base_url}/dealer/v1/applications";

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ];
        // var_dump($this->fields);
        if (!is_array($this->fields)) {
            $this->display_message("Issue Occured, Fields not found", 'error');
            return [
                'status' => false,
                'message' => "Issue Occured, Fields not found"
            ];

        }
        $ownership1 = $this->fields['fields'][43];
        $ownership2 = $this->fields['fields'][98];

        $sum = $ownership1 + $ownership2;
        
        if ($sum != 100) {
            $this->display_message("Ownership Percentage total must be equal to 100.", 'error');
            return [
                'status' => false,
                'message' => "Ownership Percentage total must be equal to 100."
            ];

        }

        switch ($this->fields['fields'][6]) {
            case 'Corporation':
                $businessType= "C";
                break;
            case 'Proprietorship':
                $businessType= "S";
                break;
            case 'Partnership':
                $businessType= "P";
                break; 
            default:
                $businessType= "L";
                break;
        }

        $body = [
            "dealer" => [
                "code" => $this->dealer_code
            ],
            "type" => "B",
            "business" => [
                "name" => $this->fields['fields'][4],
                "dba" => $this->fields['fields'][5] ?? '',
                "federalTaxId" => $this->fields['fields'][10],  // Replace with a valid Federal Tax ID
                "type" => $businessType,
                "timeInBusiness" => $this->fields['fields'][9],
                "timeInBusinessUnits" => "years",
                "phone1" => $this->fields['fields'][8],
                "email" => $this->fields['fields'][86],
                "address1" => $this->fields['fields'][7]['address1'],
                "city" => $this->fields['fields'][7]['city'],
                "state" => $this->fields['fields'][7]['state'],
                "zip" => $this->fields['fields'][7]['postal']
            ],
            "people" => [
                    [
                        "firstName" => $this->fields['fields'][87]['first'],
                        "lastName" => $this->fields['fields'][87]['last'],
                        "ssn" => "123456789",
                        "title" => $this->fields['fields'][38],
                        "ownershipPercentage" => $ownership1,
                        "phone1" => $this->fields['fields'][42],
                        "email" => $this->fields['fields'][37],
                        "address1" => $this->fields['fields'][89]['address1'],
                        "city" => $this->fields['fields'][89]['city'],
                        "state" => $this->fields['fields'][89]['state'],
                        "zip" => $this->fields['fields'][89]['postal'],
                        "role" => [
                            "personalGuarantor" => in_array("Personal Guarantor", $this->fields['fields'][90]) ? "true" : "false",
                            "primaryContact" => in_array("Primary Contact", $this->fields['fields'][90]) ? "true" : "false",
                            "cosigner" => in_array("Co Signer", $this->fields['fields'][90]) ? "true" : "false"
                        ]
                    ],
                    [
                        "firstName" => $this->fields['fields'][93]['first'],
                        "lastName" => $this->fields['fields'][93]['last'],
                        "ssn" => $this->fields['fields'][99],
                        "title" => $this->fields['fields'][97],
                        "ownershipPercentage" => $ownership2,
                        "phone1" => $this->fields['fields'][96],
                        "email" => $this->fields['fields'][95],
                        "address1" => $this->fields['fields'][94]['address1'],
                        "city" => $this->fields['fields'][94]['city'],
                        "state" => $this->fields['fields'][94]['state'],
                        "zip" => $this->fields['fields'][94]['postal'],
                        "role" => [
                            "personalGuarantor" => in_array("Personal Guarantor", $this->fields['fields'][100]) ? "true" : "false",
                            "primaryContact" => in_array("Primary Contact", $this->fields['fields'][100]) ? "true" : "false",
                            "cosigner" => in_array("Co Signer", $this->fields['fields'][100]) ? "true" : "false"
                        ]
                    ]
            ],
            "assets" => [
                [
                    "costPerUnit" =>(int) $this->fields['fields'][101],
                    "quantity" => (int)$this->fields['fields'][102],
                    "description" => $this->fields['fields'][17],
                    "condition" => $this->fields['fields'][104] == "New" ? "N" : "U",
                ]
            ],
            "source" => [
                "application" => "woocommerce",
                "ipAddress" => $this->getClientIP()
            ],
            "submit" => "true"
        ];

        $args = [
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 3000
        ];

        $response = wp_remote_post($api_endpoint, $args);

        if (is_wp_error($response)) {
            $this->display_message("Response Error: " . $response->get_error_message(), 'error');
            return [
                'status' => false,
                'message' => $response->get_error_message()
            ];
            
        }

        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body);

        if (isset($response_data->error)) {
            $this->display_message("Response Body Error: " . $response_data->error->message, 'error');
            return[
                'status' => false,
                'message' => $response_data->error->message
            ];
        } elseif(isset($response_data->applicationNumber)) {
            $this->display_message("Application submitted successfully.", 'success');
            return [
                'status' => false,
                'message' => "Application submitted successfully."
            ];
        }
    }


    
    public function wpf_dev_process_complete($fields, $entry, $form_data)
    {

        // Optional, you can limit to specific forms. Below, we restrict output to
        // form #5.
        if (absint($form_data['id']) !== $this->formId) {
            return;
        }
        // print_r($fields);
        var_dump($fields ) .PHP_EOL. PHP_EOL . PHP_EOL . PHP_EOL;
        var_dump($entry ) . PHP_EOL . PHP_EOL;

        $this->fields = $entry;
        // $this->email = $fields[1]['value'];
        
        // print_r($this->saveFieldsToSoapApi());
        $appSave = $this->add_application_to_timestamp();

        if(!$appSave['status']){
                wpforms()->process->errors[$form_data['id']]["{$this->formId}"] = esc_html__("{$appSave['message']}");
        }

        

    }

    public function getClientIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    private function display_message($message, $status = "success")
    {
        $backgroundColor = $status === "success" ? "green" : "red";

        echo "<div style='display:fixed;top:0;left:0;width:100%;z-index:99;text-align:center;background-color:{$backgroundColor};color:white;padding:6px;border-radius:4px;margin-bottom:10px;'>{$message}</div>";
    }
    

}

new Test;

endif;



// $sync = carbon_get_theme_option('aone_sync_product');








