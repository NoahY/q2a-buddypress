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
			
			// avatar as image_src
			
            if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_integration_avatar_head')) {
				$avatar = preg_replace('|.*src="([^"]+)".*|i','$1',bp_core_fetch_avatar( array( 'item_id' => $this->content['q_view']['raw']['userid'], 'width' => qa_opt('buddypress_integration_avatar_w'), 'height' => qa_opt('buddypress_integration_avatar_h'), 'email' => $email ) ));
				$avatar = preg_replace('|.*SRC="([^"]+)".*|i','$1',$this->content['q_view']['avatar']);
				if(isset($avatar))
					$this->output('<link rel="image_src" href="'.$avatar.'" />');
			}
		}

		function main_parts($content)
		{
			if (qa_opt('buddypress_integration_enable') && $this->template == 'user' && !qa_get('tab')) { 
				if(qa_opt('buddypress_enable_profile'))
					$content = array_merge(array('form-buddypress-list' => $this->user_buddypress_form($content['raw']['userid'])),$content);
			}

			qa_html_theme_base::main_parts($content);

		}

	// handle replacements

		function head_title() {
			if(qa_opt('buddypress_integration_enable') && qa_opt('buddypress_display_names') && $this->template == 'user' && @$this->content['title']) {
				$rest = str_replace('^','(\S+)',qa_lang_html('profile/user_x'));
				$handle = preg_replace('|'.$rest.'|','$1',$this->content['title']);
				$name = bp_core_get_user_displayname($handle);
				if($name) {
					$this->content['title']=qa_lang_html_sub('profile/user_x', $name).' (@'.$handle.')';
					if(isset($this->content['form_activity'])) {
						$this->content['form_activity']['title'] = qa_lang_html_sub('profile/activity_by_x', $name);
					}
				}
			}	
			qa_html_theme_base::head_title();
		}

		function logged_in()
		{
			if(isset($this->content['loggedin']['data']) && qa_opt('buddypress_integration_enable') && qa_opt('buddypress_display_names')) {
				$handle = qa_get_logged_in_handle();
				$name = bp_core_get_user_displayname($handle);
				$this->content['loggedin']['data'] = str_replace('>'.$handle.'<',' title="@'.$handle.'">'.$name.'<',$this->content['loggedin']['data']);
			}
			qa_html_theme_base::logged_in();
		}

		function post_meta($post, $class, $prefix=null, $separator='<BR/>')
		{
			if(qa_opt('buddypress_integration_enable') && qa_opt('buddypress_display_names')) {
				if (isset($post['who']['data'])) {
					
					$handle = $this->who_to_handle($post['who']['data']);
					$name = bp_core_get_user_displayname($handle);
					if($name)
						$post['who']['data']  = str_replace('>'.$handle.'<',' title="@'.$handle.'">'.$name.'<',$post['who']['data']);
					if($handle && !in_array($handle,$this->bp_mentions)) // @mentions
						$this->bp_mentions[] = $handle;
					
				}
				if (isset($post['who_2']['data'])) {
					$handle2 = $this->who_to_handle($post['who_2']['data']);
					$name2 = bp_core_get_user_displayname($handle2);
					if($name2)
						$post['who_2']['data']  = str_replace('>'.$handle2.'<',' title="@'.$handle2.'">'.$name2.'<',$post['who_2']['data']);
					if($handle2 && !in_array($handle2,$this->bp_mentions)) // @mentions
						$this->bp_mentions[] = $handle2;
				}
			}
			qa_html_theme_base::post_meta($post, $class, $prefix, $separator);
			
		}		

		function ranking_label($item, $class)
		{
			if(qa_opt('buddypress_integration_enable') && qa_opt('buddypress_display_names') && $class == 'qa-top-users') {
				$handle = $this->who_to_handle($item['label']);
				$name = bp_core_get_user_displayname($handle);
				if($name)
					$item['label'] = str_replace('>'.$handle.'<',' title="@'.$handle.'">'.$name.'<',$item['label']);
			}
			qa_html_theme_base::ranking_label($item, $class);
		}
		
	// avatars
		function q_view($post) {
            if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_integration_avatars_qv'))
				$post['avatar'] = $this->get_bp_avatar($post['raw']['userid'],qa_opt('buddypress_integration_avatars_qv_size'));
			qa_html_theme_base::q_view($post);
		}
		function q_list_item($post) {
            if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_integration_avatars_qi'))
				$post['avatar'] = $this->get_bp_avatar($post['raw']['userid'],qa_opt('buddypress_integration_avatars_qi_size'));
			qa_html_theme_base::q_list_item($post);
		}
		function a_list_item($post) {
            if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_integration_avatars_a'))
				$post['avatar'] = $this->get_bp_avatar($post['raw']['userid'],qa_opt('buddypress_integration_avatars_a_size'));
			qa_html_theme_base::a_list_item($post);
		}
		function c_list_item($post) {
            if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_integration_avatars_c'))
				$post['avatar'] = $this->get_bp_avatar($post['raw']['userid'],qa_opt('buddypress_integration_avatars_c_size'));
			qa_html_theme_base::c_list_item($post);
		}

        function get_bp_avatar($uid, $size)
        {
			$user_info = get_userdata($uid);
			$email = $user_info->user_email;
			$avatar = bp_core_fetch_avatar( array( 'item_id' => $uid, 'width' => $size, 'height' => $size, 'email' => $email ) );
			
			return $avatar;
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
		
		var $bp_mentions = array();
		
		function body_suffix() {
			qa_html_theme_base::body_suffix();
			// @username autocomplete
			
            if (qa_opt('buddypress_integration_enable') && qa_opt('buddypress_integration_autocomplete') && !empty($this->bp_mentions)) {
				$this->output('<script type="text/javascript">','var bp_mention_autocomplete = [\''.implode("','",$this->bp_mentions).'\'];','</script>');
			}
		}

		
	// worker


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
		
		function user_buddypress_form($userid) {
			// displays badge list in user profile
			
			global $qa_request;

			$handles = qa_userids_to_handles(array($userid));
			$handle = $handles[$userid];
			
			if(!$handle) return;

			global $bp;

			if(qa_opt('buddypress_integration_priv_message') && qa_get_logged_in_userid() && $userid != qa_get_logged_in_userid()) {
				$fields[] = array(
					'label' => '<a href="'.wp_nonce_url( $bp->loggedin_user->domain . $bp->messages->slug . '/compose/?r='.$handle).'">'.qa_lang('misc/private_message_title').'</a>',
					'type' => 'static'
				);
			}	

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
			
			$title = '<a href="'.bp_core_get_user_domain($userid) . $bp->profile->slug .'/">'.qa_opt('buddypress_integration_title').'</a>';

			return array(				
				'ok' => ($ok && !isset($error)) ? $ok : null,
				'style' => 'wide',
				'tags' => $tags,
				'title' => $title,
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
		// grab the handle from meta
		function who_to_handle($string)
		{
			preg_match( '#qa-user-link">([^<]*)<#', $string, $matches );
			return !empty($matches[1]) ? $matches[1] : null;
		}	
	}

