<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdRegister.php';

class HelpfulcrowdResetAPI extends HelpfulcrowdRegister
{	
	public function __construct()
	{
		if((isset($_GET['hcapis']) && ($_GET['hcapis'] == 'reset')) && !$this->helpfulcrowd_check_APIs()) 
		{
			$this->helpfulcrowd_reset_api_keys();
		}
	}
	
	
	public function helpfulcrowd_check_APIs()
	{
		if (empty(get_option('helpfulcrowd_installed'))){
			return true;
		}
		global $wpdb;
		
		$consumer_key = get_option('helpfulcrowd_consumer_key');
		$truncated_key = substr($consumer_key, -7);
		$consumer_secret = get_option('helpfulcrowd_consumer_secret');

		if ($consumer_key && $consumer_secret) {
			$key = $wpdb->get_row( $wpdb->prepare("
							SELECT *
							FROM {$wpdb->prefix}woocommerce_api_keys
							WHERE consumer_secret = %s 
							AND truncated_key = %s
						", $consumer_secret, $truncated_key), ARRAY_A);
			if(!$key)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}

}