<?php

	class qa_buddypress_event {
		
// main event processing function
		
		function process_event($event, $userid, $handle, $cookieid, $params) {
			if (qa_opt('buddypress_integration_enable')) {
				switch ($event) {

					// when a new question, answer or comment is created. The $params array contains full information about the new post, including its ID in $params['postid'] and textual content in $params['text'].
					case 'q_post':
						$this->post($event,$userid,$params,' question');
						break;
					case 'a_post':
						$this->post($event,$userid,$params,'n %answer% to the question ');
						break;
					case 'c_post':
						$this->post($event,$userid,$params,' %comment% on the question ');
						break;
					default:
						break;
				}
			}
		}

		
		
		function post($event,$userid,$params,$suffix) {
			
			if ( !function_exists( 'bp_core_install' ) ) {
				error_log('Q2A Buddypress Plugin: Buddypress not found - please check your Wordpress/Q2A integration setup.');
				return;
			}
			
			$content = $params['content'];

			// mentions
			
			include_once( ABSPATH . WPINC . '/registration.php' );
			
			$pattern = '/[@]+([A-Za-z0-9-_\.]+)/';
			preg_match_all( $pattern, $content, $usernames );

			// Make sure there's only one instance of each username
			if ($usernames = array_unique( $usernames[1])) {

				foreach( (array)$usernames as $username ) {
					if ( !$user_id = username_exists( $username ) )
						continue;

					// Increase the number of new @ mentions for the user
					$new_mention_count = (int)get_user_meta( $user_id, 'bp_new_mention_count', true );
					update_user_meta( $user_id, 'bp_new_mention_count', $new_mention_count + 1 );
					$content = str_replace( "@$username", "<a href='" . bp_core_get_user_domain( bp_core_get_userid( $username ) ) . "' rel='nofollow'>@$username</a>", $content );
				}
			}

			// activity post
			
			require_once QA_INCLUDE_DIR.'qa-app-users.php';
			
			$publictohandle=qa_get_public_from_userids(array($userid));
			$handle=@$publictohandle[$userid];
			
			if($event != 'q_post') {
				$parent = qa_db_read_one_assoc(
					qa_db_query_sub(
						'SELECT * FROM ^posts WHERE postid=#',
						$params['parentid']
					),
					true
				);
				if($parent['type'] == 'A') {
					$parent = qa_db_read_one_assoc(
						qa_db_query_sub(
							'SELECT * FROM ^posts WHERE postid=#',
							$parent['parentid']
						),
						true
					);					
				}
				$anchor = qa_anchor(($event == 'a_post'?'A':'C'), $params['postid']);
				$suffix = preg_replace('/%([^%]+)%/','<a href="'.qa_path_html(qa_q_request($parent['postid'], $parent['title']), null, qa_opt('site_url'),null,$anchor).'">$1</a>',$suffix);
				$activity_url = qa_path_html(qa_q_request($parent['postid'], $parent['title']), null, qa_opt('site_url'));
				$context = $suffix.'"<a href="'.$activity_url.'">'.$parent['title'].'</a>".';
			}
			else {
				$activity_url = qa_path_html(qa_q_request($params['postid'], $params['title']), null, qa_opt('site_url'));
				$context = ' question "<a href="'.$activity_url.'">'.$params['title'].'</a>".';
			}
			
			$action = '<a href="' . bp_core_get_user_domain($userid) . '" rel="nofollow">'.$handle.'</a> posted a'.$context;

			$informat=$params['format'];					

			$viewer=qa_load_viewer($content, $informat);
			
			if (qa_opt('buddypress_integration_max_post_length') && strlen( $content ) > (int)qa_opt('buddypress_integration_max_post_length') ) {
				$content = substr( $content, 0, (int)qa_opt('buddypress_integration_max_post_length') );
				$content = $content.' ...';
			}		
				
			$content=$viewer->get_html($content, $informat, array());

			bp_activity_add(
				array(
					'action' => $action,
					'content' => $content,
					'primary_link' => $activity_url,
					'component' => 'bp-qa',
					'type' => 'activity_qa',
					'user_id' => $userid,
					'item_id' => null
				)
			);
		}
	}

