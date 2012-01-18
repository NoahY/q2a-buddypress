<?php

	class qa_buddypress_event {
		
// main event processing function
		
		function process_event($event, $userid, $handle, $cookieid, $params) {
			if (qa_opt('buddypress_integration_enable') && $userid) {
				switch ($event) {

					// when a new question, answer or comment is created. The $params array contains full information about the new post, including its ID in $params['postid'] and textual content in $params['text'].
					case 'q_post':
						if(qa_post_text('is_expert_question') == 'yes' || !qa_opt('buddypress_integration_post_q')) 
							return; // don't broadcast expert questions
						$this->post($event,$userid,$handle,$params,'Q');
						break;
					case 'a_post':
						if(qa_post_text('is_expert_question') == 'yes' || !qa_opt('buddypress_integration_post_a'))
							return;
						
						if(qa_opt('expert_question_enable')) {
							
							$pid = $params['parentid'];
							
							$parent = qa_db_read_one_assoc(
								qa_db_query_sub(
									"SELECT type, parentid FROM ^posts WHERE postid=#",
									$pid
								), true
							);
							
							if(strpos($parent['type'],'A') === 0) {
								$pid = $parent['parentid'];				
							}
							
							$expert = qa_db_read_one_value(
								qa_db_query_sub(
									"SELECT meta_value FROM ^postmeta WHERE post_id=# AND meta_key='is_expert_question'",
									$pid
								), true
							);
							if($expert) return;
						}
													
						$this->post($event,$userid,$handle,$params,'A');
						break;
					case 'c_post':
						if(!qa_opt('buddypress_integration_post_c'))
							return;
						if(qa_opt('expert_question_enable')) {
							
							$pid = $params['parentid'];
							
							$parent = qa_db_read_one_assoc(
								qa_db_query_sub(
									"SELECT type, parentid FROM ^posts WHERE postid=#",
									$pid
								), true
							);
							
							if(strpos($parent['type'],'A') === 0) {
								$pid = $parent['parentid'];				
							}
							
							$expert = qa_db_read_one_value(
								qa_db_query_sub(
									"SELECT meta_value FROM ^postmeta WHERE post_id=# AND meta_key='is_expert_question'",
									$pid
								), true
							);
							if($expert) return;
						}
						
						$this->post($event,$userid,$handle,$params,'C');
						break;
					default:
						break;
				}
			}
		}

		
		
		function post($event,$userid,$handle,$params,$type) {

			// remove mentions
			
			if(!qa_opt('buddypress_mentions')) remove_filter( 'bp_activity_after_save', 'bp_activity_at_name_filter_updates' );

			switch($type) {
				case 'Q':
					$suffix = ' question ';
					break;
				case 'A':
					$suffix = 'n %answer% to the question ';
					break;
				case 'C':
					$suffix = ' %comment% on the question ';
					break;
			}
			
			// poll integration
			
			if (qa_post_text('is_poll')) {
				if($type == 'A') return;
				if($type == 'Q') {
					$suffix = str_replace('question','poll',$suffix);
				}
				else $suffix = str_replace('question','poll',$suffix);
			}

			$content = $params['content'];

			// activity post
			
			
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
				$context = $suffix.'"<a href="'.$activity_url.'">'.$parent['title'].'</a>"';
			}
			else {
				$activity_url = qa_path_html(qa_q_request($params['postid'], $params['title']), null, qa_opt('site_url'));
				$context = $suffix.'"<a href="'.$activity_url.'">'.$params['title'].'</a>"';
			}
			if(qa_opt('buddypress_display_names'))
				$name = bp_core_get_user_displayname($handle)?bp_core_get_user_displayname($handle):$handle;
			else 
				$name = $handle;
			
			$action = '<a href="' . bp_core_get_user_domain($userid) . '" rel="nofollow">'.$name.'</a> posted a'.$context.'on <a href="'.qa_opt('site_url').'">'.qa_opt('site_title').'</a>';

			if(qa_opt('buddypress_integration_include_content')) {

				$informat=$params['format'];					

				$viewer=qa_load_viewer($content, $informat);
				
				if (qa_opt('buddypress_integration_max_post_length') && strlen( $content ) > (int)qa_opt('buddypress_integration_max_post_length') ) {
					$content = substr( $content, 0, (int)qa_opt('buddypress_integration_max_post_length') );
					$content = $content.' ...';
				}		
					
				$content=$viewer->get_html($content, $informat, array());
			}
			else $content = null;

			global $phpmailer;
			if(class_exists('PHPMailer') && !is_object($phpmailer)) {
				$phpmailer = new PHPMailer( true );
			}

			$act_id = qa_buddypress_activity_post(
				array(
					'action' => $action,
					'content' => $content,
					'primary_link' => $activity_url,
					'component' => 'bp-qa',
					'type' => 'activity_qa_'.$type,
					'user_id' => $userid,
					'item_id' => null
				)
			);
		}
	}

