<?php
    class qa_bp_admin {
		
	function allow_template($template)
	{
		return ($template!='admin');
	}

	function option_default($option) {
		
		switch($option) {
			default:
				return false;
		}
		
	}

	function admin_form(&$qa_content)
	{

	// Process form input

	    $ok = null;

            if (qa_clicked('buddypress_integration_save')) {
                qa_opt('buddypress_integration_enable',qa_post_text('buddypress_integration_enable'));
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

