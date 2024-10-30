<?php

defined('ABSPATH') || exit;

if (is_admin()) {
	
	if(!get_option('helpfulcrowd_adminConsent') && !empty(get_option('helpfulcrowd_installed') && !empty(get_option('helpfulcrowd_store_id'))))
	{
		update_option('helpfulcrowd_adminConsent','Yes', true);
	}
	require_once 'HelpfulcrowdSettings.php';
	require_once 'HelpfulcrowdRegister.php';
	require_once 'HelpfulcrowdResetAPI.php';

	if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		add_action('woocommerce_loaded', 'helpfulcrowd_admin_start');
	} else {
		helpfulcrowd_admin_start();
	}

	add_action('update_option_admin_email', 'helpfulcrowd_option_hook', 10, 3);
	add_action('update_option_helpfulcrowd_options', 'helpfulcrowd_settings_hook', 10, 3);
	add_action('admin_notices', 'helpfulcrowd_reset_api_notice');
	
} else {
	require_once 'HelpfulcrowdWidgets.php';
	add_action('woocommerce_loaded', 'helpfulcrowd_front_start');
}

//Custom Endpoint to sync settings /helpfulcrowd/v1/update-settings
add_action( 'rest_api_init', 'helpfulcrowd_register_route' );
require_once 'HelpfulcrowdHooks.php';
require_once 'HelpfulcrowdCustomEndpoints.php';
//require_once 'HelpfulcrowdSocials.php';

//add_action('woocommerce_loaded', 'helpfulcrowd_rest_start');

function helpfulcrowd_admin_start()
{
	$settings = new HelpfulcrowdSettings();
}

function helpfulcrowd_front_start()
{
	$widgets = new HelpfulcrowdWidgets();
}

//function helpfulcrowd_rest_start()
//{
//	$socials = new HelpfulcrowdSocials();
//}

function helpfulcrowd_reset_api_notice()
{
	$resetAPI = new HelpfulcrowdResetAPI();
	
	if(!$resetAPI->helpfulcrowd_check_APIs())
	{
		helpfulcrowd_admin_notice();
	}
}

function helpfulcrowd_admin_notice(){
?>
	<div class="notice notice-warning is-dismissible">
		<p>API's configured for Helpfulcrowd Plugin are Updated or Revoked. 
			<a href="<?php echo admin_url('admin.php?page=helpfulcrowd&hcapis=reset'); ?>">Click Here</a> to reset API's.</p>
	</div>
	<?php
}


function helpfulcrowd_option_hook($old_value, $value, $option)
{
	$hooks = new HelpfulcrowdHooks();

	if ($option == 'admin_email') {
		$hooks->helpfulcrowd_update_admin_email($value);
	}
}

function helpfulcrowd_settings_hook($old_value, $value, $option)
{
	$hooks = new HelpfulcrowdHooks();

	if ($option == 'helpfulcrowd_options') {
		$settings = get_option('helpfulcrowd_options');
		$options = array(
				'woocommerce_before_shop_loop_item' => 'Above Item',
				'woocommerce_before_shop_loop_item_title' => 'Above Title',
				'woocommerce_shop_loop_item_title' => 'Below Title',
				'woocommerce_after_shop_loop_item_title' => 'Below Info',
				'woocommerce_after_shop_loop_item' => 'Below Item',
				'uael_woo_products_title_before' => 'UAEL Above Title',
				'uael_woo_products_title_after' => 'UAEL Below Title',
				'uael_woo_products_price_after' => 'UAEL Below Price',
				'woocommerce_product_thumbnails' => 'Below Images',
				'woocommerce_single_product_summary' => 'Below Title',
				'woocommerce_before_add_to_cart_form' => 'Above Cart Button',
				'woocommerce_after_add_to_cart_form' => 'Below Cart Button',
				'woocommerce_product_meta_start' => 'Above Meta',
				'woocommerce_product_meta_end' => 'Below Meta',
				'woocommerce_share' => 'Below Info',
				'woocommerce_after_single_product' => 'Page Bottom',
				'hc_tabs_shortcode' => 'Use Shortcode',
				'hc_summary_shortcode' => 'Use Shortcode'
				);
		foreach($settings as $key => $setting)
		{
			if(isset($options[$setting]))
			{
				$settings[$key] = $options[$setting];
			}
		}
		$settings['email'] = get_option('admin_email');
		$hooks->helpfulcrowd_update_plugin_settings($settings);
	}
	
}

function helpfulcrowd_register_route() {
	register_rest_route( 'helpfulcrowd/v1', 'update-settings', array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array(new HelpfulcrowdCustomEndpoints, 'helpfulcrowd_validate_token'),
				'permission_callback' => '__return_true'
			)
		);
}
