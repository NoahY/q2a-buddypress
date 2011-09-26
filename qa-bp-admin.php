<?php
    class qa_bp_admin {
		
	function allow_template($template)
	{
		return ($template!='admin');
	}

	function option_default($option) {
		
		switch($option) {
		    case 'buddypress_integration_max_post_length':
			return 0;
		    default:
			return null;
		}
		
	}

	function admin_form(&$qa_content)
	{

	// Process form input

	    $ok = null;

            if (qa_clicked('buddypress_integration_save')) {
		if(!function_exists( 'bp_activity_add' )) {
		    $ok = 'Buddypress not found - please check your Wordpress/Q2A integration setup.';
		    qa_opt('buddypress_integration_enable', false);
		}
		else {
		    qa_opt('buddypress_integration_enable',(bool)qa_post_text('buddypress_integration_enable'));
		    qa_opt('buddypress_mentions',(bool)qa_post_text('buddypress_mentions'));
		    qa_opt('buddypress_integration_include_content',(bool)qa_post_text('buddypress_integration_include_content'));
		    qa_opt('buddypress_integration_max_post_length',(int)qa_post_text('buddypress_integration_max_post_length'));
		    $ok = 'Settings Saved.';
		}
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
                'label' => 'Enable Buddypress mentions (see notes below)',
                'tags' => 'NAME="buddypress_mentions"',
                'value' => qa_opt('buddypress_mentions'),
                'type' => 'checkbox',
            );
 
            
            
            $fields[] = array(
                'label' => 'Include content summary of posts in activity stream',
                'tags' => 'onclick="if(this.checked) jQuery(\'#bp_hide\').fadeIn(); else jQuery(\'#bp_hide\').fadeOut();" NAME="buddypress_integration_include_content"',
                'value' => qa_opt('buddypress_integration_include_content'),
                'type' => 'checkbox',
		'note' => '<span style="font-size:85%;">If this is unchecked, @username mentions will not function properly.  A better way to go about hiding the content in the stream is to add the following code to your buddypress theme stylesheet, so content will show only in the user mentions tab:<br><br><i>.activity_qa .activity-inner {<br>&nbsp;&nbsp;&nbsp;&nbsp;display:none;<br>}
<br>.mentions .activity_qa .activity-inner {<br>&nbsp;&nbsp;&nbsp;&nbsp;display:block;<br>}</i></span>'
            );
 
            
            $fields[] = array(
                'label' => '<table id="bp_hide" style="display:'.(qa_opt('buddypress_integration_include_content')?'block':'none').'"><tr><td>Max. characters to post to activity stream',
                'tags' => 'NAME="buddypress_integration_max_post_length"',
                'value' => qa_opt('buddypress_integration_max_post_length'),
                'type' => 'number',
		'note' => '<span style="font-size:85%; font-weight:normal;">Setting this to 0 preserves the entire content (recommended for @username mention integration).</span></td></tr></table>'
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

