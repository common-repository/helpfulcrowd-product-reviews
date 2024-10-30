<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdBase.php';

/**
 * Class HelpfulcrowdMessage
 */
class HelpfulcrowdMessage extends HelpfulcrowdBase
{
	/**
	 * Messages Array
	 *
	 * @var array
	 */
	private static $messages = [];

	/**
	 * Messages URL
	 *
	 * @var string
	 */
	private static $msgUrl = '/api/woocommerce/messages_for_plugin';

	/**
	 * HelpfulcrowdMessage constructor.
	 */
	public function __construct()
	{
		$this->helpfulcrowd_get_messages();
	}

	/**
	 * Get Messages
	 *
	 * @return bool
	 */
	private function helpfulcrowd_get_messages()
	{
		$status = true;
		try {
			$result = wp_remote_get((
				$this->helpfulcrowd_get_host() . self::$msgUrl . '?store_id='
				. get_option('helpfulcrowd_store_id') . '&token=' . $this->helpfulcrowd_get_consumer_convert()
			));

			if (!empty($result->errors)) {
				throw new \Exception(__('Request error.', 'helpfulcrowd-product-reviews'));
			}

			$response = json_decode($result['body'], true);
			if (!$response) {
				throw new \Exception(__('Invalid response.', 'helpfulcrowd-product-reviews'));
			}

			self::$messages = $response;
		} catch (Exception $exception) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Get Message By Alias
	 *
	 * @param $alias
	 * @return mixed|null
	 */
	public static function helpfulcrowd_get_message($alias)
	{
		return key_exists($alias, self::$messages) ? self::$messages[$alias] : null;
	}
}
