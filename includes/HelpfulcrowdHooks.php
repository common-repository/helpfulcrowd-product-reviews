<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdBase.php';

/**
 * Class HelpfulcrowdHooks
 */
class HelpfulcrowdHooks extends HelpfulcrowdBase
{

	/**
	 * UPDATE EMAIL ENDPOINT
	 */
	const HELPFULCROWD_UPDATE_EMAIL = '/api/woocommerce/update_email';

	/**
	 * UPDATE SETTINGS ENDPOINT
	 */
	const HELPFULCROWD_UPDATE_SETTINGS = '/api/woocommerce/update_settings';
	
	/**
	 * Update Admin Email
	 *
	 * @param $email
	 *
	 * @return bool|int
	 */
	public function helpfulcrowd_update_admin_email($email)
	{
		if (!empty(get_option('helpfulcrowd_installed'))) {
			return $this->helpfulcrowd_update_email($email);
		}

		return false;
	}
	
	/**
	 * Update Helpfulcrowd Settings
	 *
	 * @param $settings|array
	 *
	 * @return bool|int
	 */
	public function helpfulcrowd_update_plugin_settings($settings)
	{
		if (!empty(get_option('helpfulcrowd_installed'))) {
			return $this->helpfulcrowd_update_settings($settings);
		}

		return false;
	}

	/**
	 * Update helpfulcrowd email
	 *
	 * @param $email
	 *
	 * @return int
	 */
	private function helpfulcrowd_update_email($email)
	{
		try {
			$data = [
				'store_id' => get_option('helpfulcrowd_store_id'),
				'token' => $this->helpfulcrowd_get_consumer_convert(),
				'email' => $email
			];

			$result = wp_remote_request(
				($this->helpfulcrowd_get_host() . self::HELPFULCROWD_UPDATE_EMAIL), [
					'body' => $data,
					'method' => 'PUT',
					'sslverify' => false
				]
			);

			if (!empty($result->errors)) {
				throw new \Exception(__('Request error.', 'helpfulcrowd-product-reviews'));
			}

			$response = json_decode($result['body'], true);
			if (!$response || !isset($response['code'])) {
				throw new \Exception(__('Invalid response.', 'helpfulcrowd-product-reviews'));
			}

			if (!in_array($response['code'], [self::HELPFULCROWD_STATUS_SUCCESS_REGISTERED])) {
				throw new \Exception(__('Error in registering a new email.', 'helpfulcrowd-product-reviews'));
			}

			$status = true;
		} catch (\Exception $e) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Update Helpfulcrowd Settings
	 *
	 * @param $settings|array
	 *
	 * @return bool|int
	 */
	private function helpfulcrowd_update_settings($settings)
	{
		try {
			$data = $settings;
			$data['store_id'] = get_option('helpfulcrowd_store_id');
			$data['token'] = $this->helpfulcrowd_get_consumer_convert();
			$result = wp_remote_request(
				($this->helpfulcrowd_get_host() . self::HELPFULCROWD_UPDATE_SETTINGS), [
					'body' => $data,
					'method' => 'PUT',
					'sslverify' => false
				]
			);

			if (!empty($result->errors)) {
				throw new \Exception(__('Request error.', 'helpfulcrowd-product-reviews'));
			}

			$response = json_decode($result['body'], true);
			if (!$response || !isset($response['code'])) {
				throw new \Exception(__('Invalid response.', 'helpfulcrowd-product-reviews'));
			}

			if (!in_array($response['code'], [self::HELPFULCROWD_STATUS_SUCCESS_REGISTERED])) {
				throw new \Exception(__('Error in Updating settings.', 'helpfulcrowd-product-reviews'));
			}

			$status = true;
		} catch (\Exception $e) {
			$status = false;
		}

		return $status;
	}
}
