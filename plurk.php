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
	    !get_option('plurk_token_key') or get_option('plurk_token_secret')) {
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
add_filter('the_content','plurk_show_posts');

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

    $pid = base_convert($id, 10, 36);

    $plurk_url = 'http://www.plurk.com/m/p/' . $pid;

    $html = '<iframe style="border-width: 0px; max-width:99%; min-width: 70%; height: 300px; display:block; margin-top: 20px; margin-bottom: 20px" src="' . $plurk_url . '"></iframe>';

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

    <p>Please read <a href="https://github.com/jnlin/wp_plurk/">https://github.com/jnlin/wp_plurk/</a> to get your API key and secret.</p>
    
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

