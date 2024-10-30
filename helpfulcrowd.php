<?php

/**
 * Plugin Name: Helpfulcrowd Product Reviews
 * Plugin URI: https://www.helpfulcrowd.com/
 * Description: HelpfulCrowd collects, manages and displays reviews for your WooCommerce online shop, including video, photo reviews, reminders, social integrations, Google rich snippets, and much more.
 * Version: 1.2.6
 * Requires at least: 5.2
 * Requires PHP: 7.0
 * Author: Helpfulcrowd
 * Text Domain: helpfulcrowd-product-reviews
*/

defined('ABSPATH') || exit;

define('HELPFULCROWD_PLUGIN_NAME', 'helpfulcrowd-product-reviews');
define('HELPFULCROWD_PLUGIN_SHORT_NAME', 'helpfulcrowd');

require_once 'includes/core.php';
require_once 'includes/HelpfulcrowdRegister.php';


function helpfulcrowd_author_admin_notice()
{
	//Generate plugin activation link and pass permission when User click link
	
	$path = plugin_basename( __FILE__ );
	$link = wp_nonce_url(admin_url('plugins.php?action=activate&permission=1&plugin='.$path), 'activate-plugin_'.$path);

    wp_die(sprintf(__('<div class="notice notice-info is-dismissible"><p>Let app read email address and Store URL to connect Store to Helpfulcrowd. REST API\'s will be auto generated on successful connection with app to sync products and orders. </p><p>Click "Connect" to activate and connect app Or "Back" to cancel activation:</p> <p style="float:right;"><a style="font-size: 16px;font-weight: bold;" href="%1$s">Connect</a></p></div>', 'helpfulcrowd-product-reviews'),$link),__('Permissions','helpfulcrowd-product-reviews'),array('link_url' => admin_url('plugins.php'), 'link_text' => '<< ' . __('Back','helpfulcrowd-product-reviews')));
}

function helpfulcrowd_activate()
{ 
	if(!isset($_GET['permission']))
	{
		update_option('helpfulcrowd_adminConsent','', true);
	}

	if(get_option('helpfulcrowd_adminConsent') == 'No')
	{
		try{
			update_option('helpfulcrowd_adminConsent','Yes', true);
			$register = new HelpfulcrowdRegister();
		}
		catch (\Exception $ex) {
			update_option('helpfulcrowd_adminConsent','', true);
			wp_die($ex->getMessage(),'Error',array('link_url' => admin_url('plugins.php'), 'link_text' => '<< ' . __('Back','helpfulcrowd-product-reviews')));
		}
	}
	else
	{
		update_option('helpfulcrowd_adminConsent','No', true);
		add_action('admin_notices', 'helpfulcrowd_author_admin_notice');
		helpfulcrowd_author_admin_notice();		
	}
}
register_activation_hook( __FILE__, 'helpfulcrowd_activate' );