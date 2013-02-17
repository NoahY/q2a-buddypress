<?php

/*
        Plugin Name: Buddypress Integration
        Plugin URI: https://github.com/NoahY/q2a-buddypress
        Plugin Update Check URI: https://raw.github.com/NoahY/q2a-buddypress/master/qa-plugin.php
        Plugin Description: 
        Plugin Version: 1.1
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


	function qa_buddypress_activity_post($args) {
	    
	    global $bp;
	    
	    $defaults = array(
		    'content' => false,
		    'user_id' => $bp->loggedin_user->id
	    );
	    $r = wp_parse_args( $args, $defaults );
	    extract( $r, EXTR_SKIP );	
	    	
	    // Record this on the user's profile
	    $from_user_link   = bp_core_get_userlink( $user_id );
	    $activity_action  = $action;
	    $activity_content = $content;
	    $primary_link     = bp_core_get_userlink( $user_id, false, true );
	    
	    // Now write the values
	    $activity_id = bp_activity_add( array(
		    'user_id'      => $user_id,
		    'action'       => apply_filters( 'bp_activity_new_update_action', $activity_action ),
		    'content'      => apply_filters( 'bp_activity_new_update_content', $activity_content ),
		    'primary_link' => apply_filters( 'bp_activity_new_update_primary_link', $primary_link ),
		    'component'    => $bp->activity->id,
		    'type'         => $type
	    ) );
	    
	    // Add this update to the "latest update" usermeta so it can be fetched anywhere.
	    bp_update_user_meta( $bp->loggedin_user->id, 'bp_latest_update', array( 'id' => $activity_id, 'content' => wp_filter_kses( $content ) ) );

	    do_action( 'bp_activity_posted_update', $content, $user_id, $activity_id );
	    
	    return $activity_id;
	}

/*
	Omit PHP closing tag to help avoid accidental output
*/
