<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdBase.php';

class HelpfulcrowdRegister extends HelpfulcrowdBase
{

	/**
	 * Register URL
	 */
	const HELPFULCROWD_REGISTER_URL = '/api/woocommerce/sync_with_woocommerce';

	/**
	 * Register Trial URL
	 */
	const HELPFULCROWD_REGISTER_TRIAL_URL = '/api/woocommerce/start_trial';

	/**
	 * Get Trial Detail URL
	 */
	const HELPFULCROWD_GET_DETAIL_TRIAL_URL = '/api/woocommerce/trial_detail';

	/**
	 * HelpfulcrowdRegister constructor.
	 */
	public function __construct()
	{
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			/* Init */
			if (empty(get_option('helpfulcrowd_installed'))) {
				$this->helpfulcrowd_init();
			}

			/* Trial */
			if (!empty(
				(isset($_GET['state']) && ($_GET['state'] == 'trial'))
				&& !empty(get_option('helpfulcrowd_installed'))
				&& empty($this->helpfulcrowd_check_trial())
			)) {
				$this->helpfulcrowd_activate_trial();
			}
		}
		else
		{
			throw new \Exception(__('Woocommerce Required!', 'helpfulcrowd-product-reviews'));
		}

	}

	/**
	 * Init Store
	 *
	 * @return int
	 */
	protected function helpfulcrowd_init()
	{
		$data['email'] = get_option('admin_email');
		$data['store_url'] = get_home_url();
		$data_url = parse_url(get_home_url());
		$data['legal_name'] = $data_url['host'];
		
		$data['store_name'] = get_bloginfo();
		
		if(empty($data['store_name']))
		{
			$data['store_name'] = $data['legal_name'];
		}

		$keys = $this->helpfulcrowd_get_api_keys();

		$data['consumer_key'] = $keys['consumer_key'];
		$data['consumer_secret'] = $keys['consumer_secret'];

		update_option('helpfulcrowd_consumer_key', $data['consumer_key'], true);
		update_option('helpfulcrowd_consumer_secret', $data['consumer_secret'], true);

		try {
			$result = $this->helpfulcrowd_make_call(($this->helpfulcrowd_get_host() . self::HELPFULCROWD_REGISTER_URL), $data);

			if (isset($result->errors)) {
				throw new \Exception(__('Request error.', 'helpfulcrowd-product-reviews'));
			}

			$response = json_decode($result['body'], true);
			if (!$response || !isset($response['code'])) {
				throw new \Exception(__('Invalid response.', 'helpfulcrowd-product-reviews'));
			}

			if (!in_array($response['code'], [
				self::HELPFULCROWD_STATUS_SUCCESS_NEW,
				self::HELPFULCROWD_STATUS_SUCCESS_REGISTERED
			])) {
				throw new \Exception(__('Activation error.', 'helpfulcrowd-product-reviews'));
			}

			if (empty($response['data']['store'])) {
				throw new \Exception(__('Store id is empty.', 'helpfulcrowd-product-reviews'));
			}

			update_option('helpfulcrowd_store_id', $response['data']['store'], true);

			$status = 1;
			$this->helpfulcrowd_get_trial_detail();
		} catch (\Exception $e) {
			$status = 0;
		}

		update_option('helpfulcrowd_installed', $status, true);

		return $status;
	}

	/**
	 * Activate Trial
	 *
	 * @return bool
	 */
	protected function helpfulcrowd_activate_trial()
	{
		try {
			$result = $this->helpfulcrowd_make_call(($this->helpfulcrowd_get_host() . self::HELPFULCROWD_REGISTER_TRIAL_URL), [
				'store_id' => get_option('helpfulcrowd_store_id'),
				'token' => $this->helpfulcrowd_get_consumer_convert()
			]);

			if (!empty($result->errors)) {
				throw new \Exception(__('Request error.', 'helpfulcrowd-product-reviews'));
			}

			$response = json_decode($result['body'], true);
			if (!$response || !isset($response['code'])) {
				throw new \Exception(__('Invalid response.', 'helpfulcrowd-product-reviews'));
			}

			if (!in_array($response['code'], [self::HELPFULCROWD_STATUS_SUCCESS_NEW])) {
				throw new \Exception(__('Trial register error.', 'helpfulcrowd-product-reviews'));
			}
		} catch (Exception $exception) {
			$status = false;
		}

		/**
		 * Check trial detail
		 */
		return $this->helpfulcrowd_get_trial_detail();
	}

	/**
	 * Get Trial Detail
	 *
	 * @return bool
	 */
	protected function helpfulcrowd_get_trial_detail()
	{
		$status = false;

		try {
			$result = wp_remote_get(
				($this->helpfulcrowd_get_host() . self::HELPFULCROWD_GET_DETAIL_TRIAL_URL . '?store_id=' . get_option('helpfulcrowd_store_id') . '&token=' . $this->helpfulcrowd_get_consumer_convert())
			);

			if (!empty($result->errors)) {
				throw new \Exception(__('Request error.', 'helpfulcrowd-product-reviews'));
			}

			$response = json_decode($result['body'], true);
			if (!$response || !isset($response['code'])) {
				throw new \Exception(__('Invalid response.', 'helpfulcrowd-product-reviews'));
			}

			if (!in_array($response['code'], [self::HELPFULCROWD_STATUS_SUCCESS_REGISTERED])) {
				throw new \Exception(__('Trial detail none.', 'helpfulcrowd-product-reviews'));
			}

			$date_end = $response['trial_end_at'] ? strtotime($response['trial_end_at']) : null;

			if ($date_end) {
				$status = true;

				update_option('helpfulcrowd_trial_delay', $date_end, true);
				update_option('helpfulcrowd_trial_enable', $status, true);
			}

		} catch (Exception $exception) {
		}

		return $status;
	}

	/**
	 * Get Api Keys
	 *
	 * @return array
	 */
	protected function helpfulcrowd_get_api_keys()
	{
		$consumer_key = get_option('helpfulcrowd_consumer_key');
		$consumer_secret = get_option('helpfulcrowd_consumer_secret');

		if ($consumer_key && $consumer_secret) {
			return array(
				'consumer_key' => $consumer_key,
				'consumer_secret' => $consumer_secret
			);
		}

		return $this->helpfulcrowd_generate_api_keys('new');
	}
	
	/**
	 * Reset Api Keys
	 *
	 * @return array
	 */
	public function helpfulcrowd_reset_api_keys()
	{
		$consumer_key = get_option('helpfulcrowd_consumer_key');
		$consumer_secret = get_option('helpfulcrowd_consumer_secret');

		if ($consumer_key && $consumer_secret) {
			return $this->helpfulcrowd_generate_api_keys('reset');
		}	
	}

	/**
	 * Generate Api Keys
	 *
	 * @return array
	 */
	protected function helpfulcrowd_generate_api_keys($action)
	{
		global $wpdb;
		
		if($action == 'reset')
		{
			$consumer_key = get_option('helpfulcrowd_consumer_key');
			$consumer_secret = get_option('helpfulcrowd_consumer_secret');
		}
		else
		{
			$consumer_key = 'ck_' . wc_rand_hash();
			$consumer_secret = 'cs_' . wc_rand_hash();
		}

		$data = array(
			'user_id' => get_current_user_id(),
			'description' => __('Helpfulcrowd Reviews Plugin', 'helpfulcrowd-product-reviews'),
			'permissions' => 'read_write',
			'consumer_key' => wc_api_hash($consumer_key),
			'consumer_secret' => $consumer_secret,
			'truncated_key' => substr($consumer_key, -7),
		);

		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_api_keys',
			$data,
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		$result = array();
		$result['consumer_key'] = $consumer_key;
		$result['consumer_secret'] = $consumer_secret;

		return $result;
	}
}
