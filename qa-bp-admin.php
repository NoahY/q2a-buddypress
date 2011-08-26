<?php
    class qa_bp_admin {
		
	function allow_template($template)
	{
		return ($template!='admin');
	}

	function option_default($option) {
		
		switch($option) {
		    case 'buddypress_integration_max_post_length':
			return 140;
		    default:
			return null;
		}
		
	}

	function admin_form(&$qa_content)
	{

	// Process form input

	    $ok = null;

            if (qa_clicked('buddypress_integration_save')) {
                qa_opt('buddypress_integration_enable',(bool)qa_post_text('buddypress_integration_enable'));
                qa_opt('buddypress_integration_include_content',(int)qa_post_text('buddypress_integration_include_content'));
                qa_opt('buddypress_integration_max_post_length',(int)qa_post_text('buddypress_integration_max_post_length'));
                $ok = 'Settings Saved.';
            }
            
                    
        // Create the form for display

            
            $fields = array();
            
            $fields[] = array(
                'label' => 'Enable Buddypress integration',
                'tags' => 'NAME="buddypress_integration_enable"',
                'value' => qa_opt('buddypress_integration_enable'),
                'type' => 'checkbox',
            );
 
            
            
            $fields[] = array(
                'label' => 'Include content summary of posts in activity stream',
                'tags' => 'NAME="buddypress_integration_include_content"',
                'value' => qa_opt('buddypress_integration_include_content'),
                'type' => 'checkbox',
            );
 
            
            $fields[] = array(
                'label' => 'Max. characters to post to activity stream',
                'tags' => 'NAME="buddypress_integration_max_post_length"',
                'value' => qa_opt('buddypress_integration_max_post_length'),
                'type' => 'number',
            );
 

            return array(           
                'ok' => ($ok && !isset($error)) ? $ok : null,
                    
                'fields' => $fields,
             
                'buttons' => array(
                    array(
                        'label' => 'Save',
                        'tags' => 'NAME="buddypress_integration_save"',
                    )
                ),
            );
        }
    }

