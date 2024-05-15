<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;
// use Carbon_Fields\Helper\Helper;

add_action("after_setup_theme","a_one_load_carbon_fields");
add_action("carbon_fields_register_fields", "a_one_create_options_page");
// add_action("init", "create_virtual_ai_render_post_type");
// add_action("add_meta_boxes", "create_virtual_ai_render_meta_box");


function a_one_load_carbon_fields(): void {
    // require_once ('vendor/autoload.php');
    \Carbon_Fields\Carbon_Fields::boot();
}

function a_one_create_options_page() : void {
    Container::make('theme_options', __('aOne Products'))
        ->set_icon('dashicons-products')
        ->set_page_menu_position(50)
        ->add_fields(array(
            Field::make('html', 'crb_information_text')
                ->set_html('<h3 style="margin-bottom:center;">aOne Product Setup</h3>'),
           
                Field::make('text', 'trigger_sync_product', __('Click to trigger Product Sync'))
                    ->set_attribute('type', 'button')
                    ->set_attribute('placeholder','Click to trigger Product Sync'),
                Field::make('checkbox', 'aone_sync_product', __('Sync Products'))
                    ->set_option_value('no')
                    
        ));
}


