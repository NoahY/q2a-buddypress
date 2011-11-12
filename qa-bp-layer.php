<?php

	class qa_html_theme_layer extends qa_html_theme_base {

// theme replacement functions

	// user profile

		function head_custom() {
			qa_html_theme_base::head_custom();

			if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_enable_profile') && $this->template == 'user' && !qa_get('tab')) { 
				$this->output('
<style>',qa_opt('buddypress_integration_css'),'</style>');
			}
		}

		function main_parts($content)
		{
			if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_enable_profile') && $this->template == 'user' && !qa_get('tab')) { 
					$content = array('form-buddypress-list' => $this->user_buddypress_form())+$content; 
			}

			qa_html_theme_base::main_parts($content);

		}

	// handle replacements

		function logged_in()
		{
			if(isset($this->content['loggedin']['data']) && qa_opt('buddypress_integration_enable') && qa_opt('buddypress_display_names')) {
				$handle = qa_get_logged_in_handle();
				$name = bp_core_get_user_displayname($handle);
				$this->content['loggedin']['data'] = str_replace('>'.$handle.'<',' title="@'.$handle.'">'.$name.'<',$this->content['loggedin']['data']);
			}
			qa_html_theme_base::logged_in();
		}

		function post_meta_who($post, $class)
		{
			if(qa_opt('buddypress_integration_enable') && qa_opt('buddypress_display_names')) {
				if (isset($post['who']['data'])) {
					
					$handle = strip_tags($post['who']['data']);
					$name = bp_core_get_user_displayname($handle);
					if($name)
						$post['who']['data']  = str_replace('>'.$handle.'<',' title="@'.$handle.'">'.$name.'<',$post['who']['data']);
					
				}
				if (isset($post['who_2']['data'])) {
					$handle = strip_tags($post['who_2']['data']);
					$name = bp_core_get_user_displayname($handle);
					if($name)
						$post['who_2']['data']  = str_replace('>'.$handle.'<',' title="@'.$handle.'">'.$name.'<',$post['who_2']['data']);
				}
			}
			qa_html_theme_base::post_meta_who($post, $class);
			
		}		

	// avatars

        function post_avatar($post, $class, $prefix=null)
        {
            if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_integration_avatars')) {
				if (isset($prefix))
					$this->output($prefix);
				
				$id = $post['raw']['userid'];
				$user_info = get_userdata($id);
				$email = $user_info->user_email;
				$avatar = bp_core_fetch_avatar( array( 'item_id' => $id, 'width' => qa_opt('buddypress_integration_avatar_w'), 'height' => qa_opt('buddypress_integration_avatar_h'), 'email' => $email ) );
				$this->output('<SPAN CLASS="'.$class.'-avatar">', $avatar, '</SPAN>');
			}
			else
				qa_html_theme_base::post_avatar($post, $class, $prefix=null);
        }	

	// @mentions

		function q_view_content($q_view)
		{
			if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_mentions') && isset($q_view['content'])){
				$q_view['content'] = bp_activity_at_name_filter($q_view['content']);
			}
			qa_html_theme_base::q_view_content($q_view);
		}
		function a_item_content($a_item)
		{
			if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_mentions') && isset($a_item['content'])) {
				$a_item['content'] = bp_activity_at_name_filter($a_item['content']);
			}
			qa_html_theme_base::a_item_content($a_item);
		}
		function c_item_content($c_item)
		{
			if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_mentions') && isset($c_item['content'])) {
				$c_item['content'] = bp_activity_at_name_filter($c_item['content']);
			}
			qa_html_theme_base::c_item_content($c_item);
		}

		function mention_replace($content) {
					
			include_once( ABSPATH . WPINC . '/registration.php' );
			
			$pattern = '/[@]+([A-Za-z0-9-_\.]+)/';
			preg_match_all( $pattern, $content, $usernames );

			// Make sure there's only one instance of each username
			if ( !$usernames = array_unique( $usernames[1] ) )
				return $content;

			foreach( (array)$usernames as $username ) {
				if ( !$user_id = username_exists( $username ) )
					continue;

				$content = str_replace( "@$username", "<a href='" . bp_core_get_user_domain( bp_core_get_userid( $username ) ) . "' rel='nofollow'>@$username</a>", $content );

			}
			return $content;
		}
		
	// worker
		
		function user_buddypress_form() {
			// displays badge list in user profile
			
			global $qa_request;
			
			$handle = preg_replace('/^[^\/]+\/([^\/]+).*/',"$1",$qa_request);
			$userid = $this->getuserfromhandle($handle);
			
			if(!$userid) return;

			global $bp;
			$idx = 1;
			if ( bp_has_profile(array('user_id' => $userid)) ) : 
				while ( bp_profile_groups() ) : bp_the_profile_group();

					if ( bp_profile_group_has_fields() ) :
						$fields[] = array(
								'label' => '<span class="qa-bp-profile-group-title">'.bp_get_the_profile_group_name().'</span>',
								'type' => 'static',
								'value' => (qa_get_logged_in_userid() === $userid ? ' <a class="qa-bp-profile-group-edit" href="'.bp_loggedin_user_domain() . $bp->profile->slug . '/edit/group/'.$idx++.'">'.qa_lang_html('question/edit_button').'</a>' :''),
						);

						while ( bp_profile_fields() ) : bp_the_profile_field();

							if ( bp_field_has_data() ) :

								$fields[] = array(
										'label' => bp_get_the_profile_field_name(),
										'type' => 'static',
										'value' => preg_replace('|</*p>|','',bp_get_the_profile_field_value())
								);										

							endif;

						endwhile;


					endif;

				endwhile;

			endif;

			$ok = null;
			$tags = null;
			$buttons = array();
			
			return array(				
				'ok' => ($ok && !isset($error)) ? $ok : null,
				'style' => 'wide',
				'tags' => $tags,
				'title' => '<a href="'.bp_core_get_user_domain($userid) . $bp->profile->slug .'/">'.qa_opt('buddypress_integration_title').'</a>',
				'fields'=>$fields,
				'buttons'=>$buttons,
			);
			
		}

		function getuserfromhandle($handle) {
			require_once QA_INCLUDE_DIR.'qa-app-users.php';
			
			$publictouserid=qa_get_userids_from_public(array($handle));
			$userid=@$publictouserid[$handle];
				
			if (!isset($userid)) return;
			return $userid;
		}		
	}

