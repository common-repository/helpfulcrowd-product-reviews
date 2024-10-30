<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdBase.php';

class HelpfulcrowdCustomEndpoints extends HelpfulcrowdBase
{

	public function helpfulcrowd_validate_token($request)
	{
		 $parameters = $request->get_json_params();
		if(!isset($parameters['token']) || $parameters['token'] == '' || $parameters['token'] != $this->helpfulcrowd_get_consumer_convert()) {
			$response = array(
					'code' => 403,
					'status' => 'ERROR', 
					'message' => 'Invalid Access Token.',
					'token' => $parameters['token']
				);
		} else {
			unset($parameters['token']);
			$response = $this->helpfulcrowd_settings_endpoint($parameters);
		}
		
		return new WP_REST_Response( $response, $response['code']);
	}
	
	private function helpfulcrowd_settings_endpoint($parameters) {
		$data = array();
		
		$options = array(
				'rating_position' => array(
					'woocommerce_before_shop_loop_item' => 'Above Item',
					'woocommerce_before_shop_loop_item_title' => 'Above Title',
					'woocommerce_shop_loop_item_title' => 'Below Title',
					'woocommerce_after_shop_loop_item_title' => 'Below Info',
					'woocommerce_after_shop_loop_item' => 'Below Item',
					'uael_woo_products_title_before' => 'UAEL Above Title',
					'uael_woo_products_title_after' => 'UAEL Below Title',
					'uael_woo_products_price_after' => 'UAEL Below Price',
				),
				'summary_position' => array(
					'woocommerce_product_thumbnails' => 'Below Images',
					'woocommerce_single_product_summary' => 'Below Title',
					'woocommerce_before_add_to_cart_form' => 'Above Cart Button',
					'woocommerce_after_add_to_cart_form' => 'Below Cart Button',
					'woocommerce_product_meta_start' => 'Above Meta',
					'woocommerce_product_meta_end' => 'Below Meta',
					'hc_summary_shortcode' => 'Use Shortcode'
				),
				'tabs_position' => array(
					'woocommerce_product_thumbnails' => 'Below Images',
					'woocommerce_share' => 'Below Info',
					'woocommerce_after_single_product' => 'Page Bottom',
					'hc_tabs_shortcode' => 'Use Shortcode'
				)
			);
		
		foreach($parameters as $key => $param)
		{
			if(array_key_exists($key,$options)) {
				$option_key = array_search($param, $options[$key], true);
				if($option_key !== FALSE)
				{
					//Not Found
					$param = $option_key;
				}
			}
			$data[$key] = $param;
		}
		if(update_option('helpfulcrowd_options', $data, true)) {
			$response = array(
				'code' => 200,
				'status' => 'SUCCESS',
				'message' => __('Settings Updated Successfully', 'helpfulcrowd-product-reviews')
			);
		} else {
			$response = array(
				'code' => 400,
				'status' => 'ERROR', 
				'message' => __('Error updating Settings in your store Or there may not be any changes made in settings.', 'helpfulcrowd-product-reviews')
			);
		}
		return $response;
	}
}