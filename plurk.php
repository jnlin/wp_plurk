<?php
/**
 * @package Plurk
 * @version 0.9
 */
/*
Plugin Name: Plurk
Plugin URI: https://github.com/jnlin/wp_plurk
Description: Notify your friends when you make a new post
Author: Jui-Nan Lin
Version: 0.9
Author URI: http://github.com/jnlin/wp_plurk
*/

require __DIR__ . '/plurkoauth-master/plurkAPI.php';

function do_post_plurk($post_id)
{
    if (!('publish' == $_POST['post_status'] and 'publish' != $_POST['original_post_status'])) {
	return;
    }

    $post = get_post($post_id);
    $url = get_permalink($post_id);

    $plurk = new PlurkAPI();

    $plurk->callAPI('/APP/Timeline/plurkAdd', array(
		'content' => "$url ({$post->post_title})",
		'qualifier' => ':',
		'limited_to' => '[3143486]',
		));
}

add_action('publish_post', 'do_post_plurk');

?>
