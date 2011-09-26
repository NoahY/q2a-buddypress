<?php

	class qa_html_theme_layer extends qa_html_theme_base {

	// theme replacement functions

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
	}

