<?php
/*
Plugin Name: Notiflyer
Plugin URI: http://www.engageweb.co.uk/notiflyer-wordpress-plugin-6954.html 
Description: Add custom messages to comment notification emails.
Author: Engage Web - Steven Morris
Version: 1.0
Author URI: http://www.engageweb.co.uk/about-us/meet-the-team#Steven
License: GPL2
*/


//Add the Menu options


if ( is_admin() )
{ // admin actions
				add_action('admin_menu', 'notiflyer_menu');
				add_action( 'admin_init', 'register_notiflyer_settings' );
}

function register_notiflyer_settings()
{ // whitelist options
				register_setting( 'notiflyer_options', 'notiflyer_email_insert' );
}


function notiflyer_menu() {
				add_options_page('Notiflyer Options', 'Notiflyer', 'read', 'Notiflyer', 'notiflyer_options');
}

function notiflyer_options()
{
				echo '<div class="wrap">';
				echo '<h2>Notiflyer Options</h2>';
				echo '<form method="post" action="options.php" style="max-width:900px;">';
				wp_nonce_field('update-options');
				echo '<table class="form-table">';
				echo '<tr valign="top">';
				echo '<th style="width:50%;"><h3>Current Text</h3></th><th style="width:50%;"><h3>New Text</h3></th>';
				echo '</tr>';
				echo '<tr valign="top">';
				echo '<td><p>' . get_option('notiflyer_email_insert') . '</p></td>';	
				echo '<td><textarea type="text" name="notiflyer_email_insert" rows="10" style="width:100%"/>' . get_option('notiflyer_email_insert') . '</textarea></td>';
				echo '</tr>';
				echo '<tr><td></td><td>';
				echo '<input type="submit" class="button-primary" value="Save Changes" />';
				echo '</td></tr>';
				
				echo '</table>';
					
				echo '<input type="hidden" name="action" value="update" />';
				echo '<input type="hidden" name="page_options" value="notiflyer_email_insert" />';
						
			
				echo '</form>';
				echo '</div>';
}


if ( ! function_exists('wp_notify_postauthor') ){

function wp_notify_postauthor($comment_id, $comment_type='') {
				$comment = get_comment($comment_id);
				$post    = get_post($comment->comment_post_ID);
				$user    = get_userdata( $post->post_author );
			
				if ('' == $user->user_email) return false; // If there's no email to send the comment to
			
				$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
			
				$blogname = get_option('blogname');
			
				if ( empty( $comment_type ) ) $comment_type = 'comment';
			
				if ('comment' == $comment_type) {
								$notify_message  = sprintf( __('New comment on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
								$notify_message .= sprintf( __('Author : %1$s'), $comment->comment_author ) . "\r\n";
								$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
								$notify_message .= __('You can see all comments on this post here: ') . "\r\n";
								$notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";		
								$notify_message .= get_option('notiflyer_email_insert') . "\r\n\r\n ";
								$subject = sprintf( __('[%1$s] Comment: "%2$s"'), $blogname, $post->post_title );
				} elseif ('trackback' == $comment_type) {
								$notify_message  = sprintf( __('New trackback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
								$notify_message .= sprintf( __('Website: %1$s'), $comment->comment_author ) . "\r\n";
								$notify_message .= __('Excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
								$notify_message .= __('You can see all trackbacks on this post here: ') . "\r\n";
								$notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";		
								$subject = sprintf( __('[%1$s] Trackback: "%2$s"'), $blogname, $post->post_title );
				} elseif ('pingback' == $comment_type) {
								$notify_message  = sprintf( __('New pingback on your post #%1$s "%2$s"'), $comment->comment_post_ID, $post->post_title ) . "\r\n";
								$notify_message .= sprintf( __('Website: %1$s'), $comment->comment_author ) . "\r\n";
								$notify_message .= __('Excerpt: ') . "\r\n" . sprintf('[...] %s [...]', $comment->comment_content ) . "\r\n\r\n";
								$notify_message .= __('You can see all pingbacks on this post here: ') . "\r\n";
								$notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";		
								$subject = sprintf( __('[%1$s] Pingback: "%2$s"'), $blogname, $post->post_title );
				}
			
			
				$wp_email = 'wordpress@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
			
				if ( '' == $comment->comment_author ) {
								$from = "From: \"$blogname\" <$wp_email>";
					if ( '' != $comment->comment_author_email )
								$reply_to = "Reply-To: $comment->comment_author_email";
				} else {
								$from = "From: \"$comment->comment_author\" <$wp_email>";
					if ( '' != $comment->comment_author_email )
								$reply_to = "Reply-To: \"$comment->comment_author_email\" <$comment->comment_author_email>";
				}
			
				$message_headers = "$from\n"
					. "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
			
				if ( isset($reply_to) )
					$message_headers .= $reply_to . "\n";
			
				$notify_message = apply_filters('comment_notification_text', $notify_message, $comment_id);
				$subject = apply_filters('comment_notification_subject', $subject, $comment_id);
				$message_headers = apply_filters('comment_notification_headers', $message_headers, $comment_id);
			
				@wp_mail($user->user_email, $subject, $notify_message, $message_headers);
			
				return true;
	}
}
?>