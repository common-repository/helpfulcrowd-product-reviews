<?php

// Check
defined('ABSPATH') || exit;
defined('WP_UNINSTALL_PLUGIN') || exit;

// POST uninstall
try {
	$url = 'https://app.helpfulcrowd.com/api/woocommerce/uninstall_app';
	$stageKey = 'woocommerce-stage-secret';

	$result = wp_remote_post(
		$url,
		array(
			'body' => [
				'store_id' => get_option('helpfulcrowd_store_id'),
				'token' => base64_encode(get_option('helpfulcrowd_consumer_key') . ':' . $stageKey)
			],
			'sslverify' => false
		)
	);
} catch (Exception $e) {
}

//Delete Woocommerce API Entry
global $wpdb;
$description = __('Helpfulcrowd Reviews Plugin', 'helpfulcrowd-product-reviews');
$table = $wpdb->prefix . 'woocommerce_api_keys';
$wpdb->delete($table,array( 'description' => $description));

// Remove options
foreach (wp_load_alloptions() as $option => $value) {
	if (strpos($option, 'helpfulcrowd_') === 0) {
		delete_option($option);
	}
}
