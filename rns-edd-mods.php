<?php

/* Plugin Name: RNS EDD Customizations
* Author: Russell Fair
* Version: 0.2
* Description: Integrates Easy Digital Downloads with the RNS content stack
*/

function rns_add_subscriber_enhancements(){
    require_once('lib/subscriber-extras.php');
    new RNS_Subscriber_Extras;
}
add_action('plugins_loaded', 'rns_add_subscriber_enhancements');
