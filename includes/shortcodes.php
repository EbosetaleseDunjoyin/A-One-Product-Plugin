<?php 

add_shortcode("virtual_staging","show_virtual_form");


function show_virtual_form() {
    ob_start();
    include_once AONE_PATH . '/templates/virtual-staging-form.php';
    return ob_get_clean();
}