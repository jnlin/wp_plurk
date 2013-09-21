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

require __DIR__ . '/plurkAPI.php';

function do_post_plurk($post_id)
{
    if (!('publish' == $_POST['post_status'] and 'publish' != $_POST['original_post_status'])) {
	return;
    }

    if (!get_option('plurk_consumer_key') or !get_option('plurk_consumer_secret') or 
	    !get_option('plurk_token_key') or !get_option('plurk_token_secret')) {
	return;
    }

    $post = get_post($post_id);
    $url = get_permalink($post_id);

    $plurk = new PlurkAPI(get_option('plurk_consumer_key'),
	    get_option('plurk_consumer_secret'),
	    get_option('plurk_token_key'),
	    get_option('plurk_token_secret'));

    $ret = $plurk->callAPI('/APP/Timeline/plurkAdd', array(
		'content' => "$url ({$post->post_title})",
		'qualifier' => ':',
		));

    add_post_meta($post_id, 'plurk-post-id', $ret->plurk_id, true);
}

add_action('publish_post', 'do_post_plurk');
add_action('admin_menu', 'plurk_option_page');
add_action('admin_init', 'plurk_register_settings' );
add_filter('the_content','plurk_show_posts', 999998);

function plurk_show_posts($content = '')
{
    global $post;
    if (!is_single()) {
	return $content;
    }

    $id = get_post_meta($post->ID, 'plurk-post-id');

    if (is_array($id)) {
	$id = $id[0];
    }

    if (!$id or intval($id) <= 0) {
	return $content;
    }

    $plurk = new PlurkAPI(get_option('plurk_consumer_key'),
	    get_option('plurk_consumer_secret'),
	    get_option('plurk_token_key'),
	    get_option('plurk_token_secret'));

    $ret = $plurk->callAPI('/APP/Responses/get', array(
		'plurk_id' => $id,
		));

    // to array
    $ret = json_decode(json_encode($ret), true);

    $responses = $ret['responses'];
    $friends = $ret['friends'];

    $pid = base_convert($id, 10, 36);

    if (get_option('plurk_use_iframe')) {
	$plurk_url = 'http://www.plurk.com/m/p/' . $pid;
	$html = '<iframe style="border-width: 0px; max-width:99%; min-width: 70%; height: 300px; display:block; margin-top: 20px; margin-bottom: 20px" src="' . $plurk_url . '"></iframe>';

    } else { 
	$plurk_url = 'http://www.plurk.com/p/' . $pid;
	$html = '<h4><a href="' . $plurk_url . '" target="_blank">噗浪上的回應</a></h4><ul class="response" style="max-height: 250px; overflow-y: scroll">';

	$i = 0;
	foreach ($responses as $res) {
	    $nickname = $friends[$res['user_id']]['nick_name'];
	    $displayname = $friends[$res['user_id']]['display_name'];
	    $avatar_id = $friends[$res['user_id']]['avatar'];
	    $avatar = sprintf('<div class="avatar"><a href="http://www.plurk.com/%s"><img src="https://avatars.plurk.com/%s-medium%s.gif"></a></div>', htmlspecialchars($nickname), htmlspecialchars($res['user_id']), htmlspecialchars($avatar_id));

	    $user = sprintf('<div class="user"><a class="user-nick" href="http://www.plurk.com/%s">%s</a> <span class="response-time" style="display:inline">%s</span></div>', htmlspecialchars($nickname), htmlspecialchars($displayname), htmlspecialchars($res['posted']));

	    $message = sprintf('<div class="message" style="padding-left: 53px"><span class="content">%s</span></div>', $res['content']);

	    $li = '<li class="' . (0 == $i % 2 ? 'odd' : 'even') . '"><article>' . $avatar . $user . $message . '<div class="clear"></div></article></li>';

	    $html .= $li;
	    $i++;
	}

	$html .= '</ul>';
    }

    return $content . $html;
}

function your_plugin_settings_link($links) { 
    $settings_link = '<a href="options-general.php?page=wp_plurk/plurk.php">' . __('Settings') . '</a>'; 
    array_unshift($links, $settings_link); 
    return $links; 
}

$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );

function plurk_register_settings()
{
    register_setting('plurk-settings-group', 'plurk_consumer_key');
    register_setting('plurk-settings-group', 'plurk_consumer_secret');
    register_setting('plurk-settings-group', 'plurk_token_key');
    register_setting('plurk-settings-group', 'plurk_token_secret');
}

function plurk_option_page()
{
    add_options_page('Plurk', 'Plurk', 'manage_options', __FILE__, 'plurk_settings_page');
}

function plurk_settings_page()
{
    ?>
    <div class="wrap">
    <h2>API Key and Secret</h2>

    <p>Please read <a href="https://github.com/jnlin/wp_plurk/blob/master/README.md">https://github.com/jnlin/wp_plurk/blob/master/README.md</a> to get your API key and secret.</p>
    
    <form method="post" action="options.php">
    <?php //wp_nonce_field('update-options'); ?>
    <!-- starting -->
    <?php settings_fields('plurk-settings-group'); ?>
    <?php do_settings_sections('plurk-settings-group'); ?>
    <!-- ending -->
    
    <?php
    ?>

    <table class="form-table">

    <tr valign="top">
    <th scope="row">Consumer Key</th>
    <td><input type="text" name="plurk_consumer_key" value="<?= htmlspecialchars(get_option('plurk_consumer_key')) ?>" style='width:600px;'></td>
    </tr>
    <tr valign="top">
    <th scope="row">Consumer Secret</th>
    <td><input type="text" name="plurk_consumer_secret" value="<?= htmlspecialchars(get_option('plurk_consumer_secret')) ?>" style='width:600px;'></td>
    </tr>
    <tr valign="top">
    <th scope="row">Token Key</th>
    <td><input type="text" name="plurk_token_key" value="<?= htmlspecialchars(get_option('plurk_token_key')) ?>" style='width:600px;'></td>
    </tr>
    <tr valign="top">
    <th scope="row">Token Secret</th>
    <td><input type="text" name="plurk_token_secret" value="<?= htmlspecialchars(get_option('plurk_token_secret')) ?>" style='width:600px;'></td>
    </tr>

    </table>

    <?php submit_button(); ?>

    </form>
    
    </div>

<?php }

