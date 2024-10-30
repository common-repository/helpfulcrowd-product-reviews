<?php

defined('ABSPATH') || exit;

/*
 * Base Controller
 */

class HelpfulcrowdBase
{

	/**
	 * Stage Key
	 *
	 * @var string
	 */
	private $stageKey = 'woocommerce-stage-secret';

	/**
	 * Helpfulcrowd Host
	 *
	 * @var string
	 */
	private $helpfulcrowdHost = 'https://app.helpfulcrowd.com';

	/**
	 * Status Code
	 */
	const HELPFULCROWD_STATUS_SUCCESS_NEW = 100;
	const HELPFULCROWD_STATUS_SUCCESS_REGISTERED = 200;
	const HELPFULCROWD_STATUS_ERROR = 300;

	/**
	 * Make Call request
	 *
	 * @param $url
	 * @param $data
	 * @return array|WP_Error
	 */
	protected function helpfulcrowd_make_call($url, $data)
	{
		$result = wp_remote_post(
			$url,
			array(
				'body' => $data,
				'sslverify' => false
			)
		);

		return $result;
	}

	/**
	 * Get consumer convert string
	 *
	 * @return string
	 */
	protected function helpfulcrowd_get_consumer_convert()
	{
		return base64_encode(get_option('helpfulcrowd_consumer_key') . ':' . $this->stageKey);
	}

	/**
	 * Check trial
	 *
	 * @return bool
	 */
	protected function helpfulcrowd_check_trial()
	{
		return get_option('helpfulcrowd_trial_enable', false);
	}

	/**
	 * Check trial delay
	 *
	 * @return false|mixed
	 */
	protected function helpfulcrowd_check_trial_delay()
	{
		return ($this->helpfulcrowd_get_trial_delay() > time());
	}

	/**
	 * Get trial delay
	 *
	 * @return false|mixed
	 */
	protected function helpfulcrowd_get_trial_delay()
	{
		return get_option('helpfulcrowd_trial_delay', (time() - 50));
	}

	/**
	 * Get trial left
	 *
	 * @return false|float
	 */
	protected function helpfulcrowd_get_trial_left()
	{
		return ceil(($this->helpfulcrowd_get_trial_delay() - time()) / (60 * 60 * 24));
	}

	/**
	 * Get subscription plans
	 *
	 * @return false|mixed|void
	 */
	protected function helpfulcrowd_get_subscription_plan()
	{
		return get_option('helpfulcrowd_subscription_plan', __('FREE Helpful', 'helpfulcrowd-product-reviews'));
	}

	/**
	 * Get Helpfulcrowd host
	 *
	 * @return string
	 */
	protected function helpfulcrowd_get_host()
	{
		return $this->helpfulcrowdHost;
	}

	/**
	 * Get Plugin file
	 *
	 * @param $file
	 * @return string
	 */
	protected function helpfulcrowd_get_plugin_url($file)
	{
		return plugins_url($file, __FILE__);
	}
}
