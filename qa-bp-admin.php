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
		    case 'buddypress_integration_avatar_w':
			return 50;
		    case 'buddypress_integration_avatar_h':
			return 50;
		    case 'buddypress_integration_title':
			return 'Profile';
		    case 'buddypress_integration_css':
			return '
.qa-bp-profile-group-title{
    font-size:16px;
    text-decoration: underline;
}
.qa-bp-profile-group-edit{
    text-decoration: underline !important;
}
';
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
		    qa_opt('buddypress_display_names',(bool)qa_post_text('buddypress_display_names'));
		    qa_opt('buddypress_integration_include_content',(bool)qa_post_text('buddypress_integration_include_content'));
		    qa_opt('buddypress_integration_avatars',(bool)qa_post_text('buddypress_integration_avatars'));
		    qa_opt('buddypress_integration_avatar_h',(int)qa_post_text('buddypress_integration_avatar_h'));
		    qa_opt('buddypress_integration_avatar_w',(int)qa_post_text('buddypress_integration_avatar_w'));
		    qa_opt('buddypress_integration_max_post_length',(int)qa_post_text('buddypress_integration_max_post_length'));
		    qa_opt('buddypress_enable_profile',(int)qa_post_text('buddypress_enable_profile'));
		    qa_opt('buddypress_integration_title',qa_post_text('buddypress_integration_title'));
		    $ok = qa_lang('admin/options_saved');
		}
            }
	    else if (qa_clicked('buddypress_integration_reset')) {
		foreach($_POST as $i => $v) {
		    $def = $this->option_default($i);
		    if($def !== null) qa_opt($i,$def);
		}
		$ok = qa_lang('admin/options_reset');
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
                'label' => 'Use Buddypress display names instead of handles in meta',
                'tags' => 'NAME="buddypress_display_names"',
                'value' => qa_opt('buddypress_display_names'),
                'type' => 'checkbox',
            );

            $fields[] = array(
                'label' => 'Enable Buddypress mentions (see notes below)',
                'tags' => 'NAME="buddypress_mentions"',
                'value' => qa_opt('buddypress_mentions'),
                'type' => 'checkbox',
            );
 
	    $fields[] = array(
                'type' => 'blank',
            );          
            
            
            $fields[] = array(
                'label' => 'Enable Buddypress avatars',
                'tags' => 'NAME="buddypress_integration_avatars"',
                'value' => qa_opt('buddypress_integration_avatars'),
                'type' => 'checkbox',
            );
 
            $fields[] = array(
                'label' => 'Buddypress avatar width',
                'tags' => 'NAME="buddypress_integration_avatar_w"',
                'value' => qa_opt('buddypress_integration_avatar_w'),
                'type' => 'number',
            );
 
            $fields[] = array(
                'label' => 'Buddypress avatar height',
                'tags' => 'NAME="buddypress_integration_avatar_h"',
                'value' => qa_opt('buddypress_integration_avatar_h'),
                'type' => 'number',
            );
            
 
	    $fields[] = array(
                'type' => 'blank',
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
 
            
            $fields[] = array(
                'type' => 'blank',
            );
            $fields[] = array(
                'label' => 'Enable Buddypress profile box',
                'tags' => 'NAME="buddypress_enable_profile"',
                'value' => qa_opt('buddypress_enable_profile'),
                'type' => 'checkbox',
            );
            $fields[] = array(
                'label' => 'Buddypress profile box title',
                'tags' => 'NAME="buddypress_integration_title"',
                'value' => qa_opt('buddypress_integration_title'),
            );
            $fields[] = array(
                'label' => 'Buddypress profile box css',
                'tags' => 'NAME="buddypress_integration_css"',
                'value' => qa_opt('buddypress_integration_css'),
		'type' => 'textarea',
		'rows' => 20
            );
 

            return array(           
                'ok' => ($ok && !isset($error)) ? $ok : null,
                    
                'fields' => $fields,
             
                'buttons' => array(
                    array(
                        'label' => qa_lang_html('main/save_button'),
                        'tags' => 'NAME="buddypress_integration_save"',
                    ),
                    array(
                        'label' => qa_lang_html('admin/reset_options_button'),
                        'tags' => 'NAME="buddypress_integration_reset"',
                    ),
                ),
            );
        }
    }

