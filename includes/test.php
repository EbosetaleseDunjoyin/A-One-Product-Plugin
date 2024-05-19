<?php
use AOneProducts\Classes\Traits\Singleton;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if (!class_exists("Woo")):

class Test
{
    use Singleton;

    public $name;
    public $email;

    public function __construct(){
        $this->setup();
    }

    public function setup(){
        add_action("rest_api_init", [$this,"create_rest_endpoint"]);
        add_action('wpforms_process_complete', [$this,'wpf_dev_process_complete'], 10, 4);

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

    

    public function saveFieldsToSoapApi()
    {
        // print_r($this->name."\n");
        // print_r($this->email."\n");

        return "Data:". $this->name."\n". $this->email."\n";
    }

    
    public function wpf_dev_process_complete($fields, $entry, $form_data, $entry_id)
    {

        // Optional, you can limit to specific forms. Below, we restrict output to
        // form #5.
        if (absint($form_data['id']) !== 4635) {
            return;
        }
        // print_r($fields);

        $this->name = $fields[0]['value'];
        $this->email = $fields[1]['value'];
        
        print_r($this->saveFieldsToSoapApi());

        

    }
    

}

new Test;

endif;



// $sync = carbon_get_theme_option('aone_sync_product');








