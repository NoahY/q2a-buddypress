<?php

/*
        Plugin Name: Buddypress Integration
        Plugin URI: 
        Plugin Description: 
        Plugin Version: 1.0b
        Plugin Date: 2011-08-15
        Plugin Author: NoahY
        Plugin Author URI: 
        Plugin License: GPLv2
        Plugin Minimum Question2Answer Version: 1.3
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
			header('Location: ../../');
			exit;
	}

	qa_register_plugin_module('event', 'qa-bp-check.php','qa_buddypress_event','Buddypress Event');
	
	qa_register_plugin_layer('qa-bp-layer.php', 'Buddypress Layer');	
	
	qa_register_plugin_module('module', 'qa-bp-admin.php', 'qa_bp_admin', 'Buddypress Admin');


/*
	Omit PHP closing tag to help avoid accidental output
*/
