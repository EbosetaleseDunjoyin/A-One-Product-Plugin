<?php
namespace AOneProducts\Classes\Processes;
require_once AONE_PATH . '/vendor/autoload.php';
require_once AONE_PATH . '/classes/Woo.php';
require_once AONE_PATH . '/classes/FashionBiz.php';

use FashionBiz;
use WP_Background_Process;
use AOneProducts\Classes\Woo;
use AOneProducts\Classes\Traits\Singleton;

if(!function_exists('WP_FashionBiz')):
    class WP_FashionBiz extends WP_Background_Process {

        use Singleton;

        protected $action = "get_fashion_biz_products";
        protected $wooComm;


        public function __construct(){
            $this->wooComm = Woo::get_instance();
        }



        protected function task($item){
            $save = $this->wooComm->import_fashion_woocommerce_product($item[0], $item[1]);

            return $save;

        }



    }

endif;