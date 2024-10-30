<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdBase.php';

/**
 * Class HelpfulcrowdSocials
 */
class HelpfulcrowdSocials extends HelpfulcrowdBase
{

	/*
	 * Register URL
	 */
	const HELPFULCROWD_REGISTER_URL = '/api/woocommerce/social_connect';

	/*
	 * Status Code OK
	 */
	const HELPFULCROWD_STATUS_SUCCESS_NEW = 100;

	/**
	 * Request social url
	 * @var string
	 */
	protected $request_url;

	/**
	 * Callback social url
	 * @var string
	 */
	protected $callback_url;

	/**
	 * Session alias
	 * @var string
	 */
	protected $alias = 'helpfulcrowd';

	/**
	 * Socials
	 *
	 * @var array[]
	 */
	protected $socials = [
			'facebook',
			'twitter'
	];

	/**
	 * HelpfulcrowdSocials constructor.
	 */
	public function __construct()
	{
		$this->helpfulcrowd_register_rest();
		$this->helpfulcrowd_handle_delete_social_link();

		$this->request_url = 'https://helpfulcrowd-social.frd.com.ua/oauth/';
		$this->callback_url = self::helpfulcrowd_get_alias(HELPFULCROWD_PLUGIN_SHORT_NAME) . '/v1/social';
	}

	/**
	 * Register
	 */
	public function helpfulcrowd_register_rest()
	{
		add_action('rest_api_init', function () {
			$namespace = self::helpfulcrowd_get_alias(HELPFULCROWD_PLUGIN_SHORT_NAME) . '/v1';
			$route = '/social/(?P<social>\w+)/(?P<session>\w+)/(?P<user_id>\d+)(?:/(?P<session_serp>\w+))?';

			register_rest_route($namespace, $route, array(
					'methods' => WP_REST_Server::READABLE,
					'callback' => array($this, 'helpfulcrowd_social_request'),
					'permission_callback' => function (WP_REST_Request $request) {
						return true;
					},
					'args' => array(
							'social' => array(
									'validate_callback' => function ($param, $request, $key) {
										return in_array($param, $this->socials) ? $param : null;
									}
							),
							'session' => array(
									'validate_callback' => function ($param, $request, $key) {
										return $this->helpfulcrowd_decomposition($param);
									}
							),
							'user_id' => array(
									'validate_callback' => function ($param, $request, $key) {
										return is_numeric($param);
									}
							),
					),
			));
		});
	}

	/**
	 * Social rest request
	 *
	 * @param WP_REST_Request $request
	 */
	public function helpfulcrowd_social_request(WP_REST_Request $request)
	{

		$social = $request->get_param('social');
		$session = $request->get_param('session');
		$session_serp = $request->get_param('session_serp');
		$user_id = $request->get_param('user_id');

		$redirect_url = add_query_arg(array('page' => HELPFULCROWD_PLUGIN_SHORT_NAME, 'tab' => 'settings', 'updated' => true), admin_url());
		if (empty($social) || empty($session) || empty($user_id) || !in_array($social, $this->socials)) {
			$this->helpfulcrowd_social_error_request();
		}

		$option = $this->helpfulcrowd_get_option_alias($social) . '_user_id';
		if (empty(get_option($option, null))) {
			add_option($option, $user_id);
		} else {
			update_option($option, $user_id);
		}

		$session = self::helpfulcrowd_minimize($this->helpfulcrowd_decomposition($session));

		$option = $this->helpfulcrowd_get_option_alias($social) . '_token';
		if (empty(get_option($option, null))) {
			add_option($option, $session);
		} else {
			update_option($option, $session);
		}

		if (!empty($session_serp)) {
			$session_serp = self::helpfulcrowd_minimize($this->helpfulcrowd_decomposition($session_serp));

			$option = $this->helpfulcrowd_get_option_alias($social) . '_secret_token';
			if (empty(get_option($option, null))) {
				add_option($option, $session_serp);
			} else {
				update_option($option, $session_serp);
			}
		}

		$this->helpfulcrowd_connect_social([
				'store_id' => get_option('helpfulcrowd_store_id'),
				'token' => $this->helpfulcrowd_get_consumer_convert(), //get_option( 'helpfulcrowd_consumer_key' ),
				'provider' => $social,
				'uid' => $user_id,
				'credentials' => [
						'token' => self::helpfulcrowd_unminimize($session),
						'secret' => (!empty($session_serp) ? self::helpfulcrowd_unminimize($session_serp) : ''),
						'refresh_token' => ''
				],
		]);

		exit(wp_redirect($redirect_url));
	}

	public function helpfulcrowd_social_error_request()
	{
		$redirect_url = add_query_arg(array('page' => HELPFULCROWD_PLUGIN_SHORT_NAME, 'tab' => 'settings', 'error' => true), admin_url());
		exit(wp_redirect($redirect_url));
	}

	/**
	 * Social Data sending
	 *
	 * @param $data
	 * @return bool
	 */
	protected function helpfulcrowd_connect_social($data)
	{
		$result = $this->helpfulcrowd_make_call(($this->helpfulcrowd_get_host() . self::HELPFULCROWD_REGISTER_URL), $data);
		$status = true;

		try {
			if (!empty($result->errors)) {
				throw new \Exception(__('Request error.', 'helpfulcrowd-product-reviews'));
			}

			$response = json_decode($result['body'], true);

			if (!$response || !isset($response['code'])) {
				throw new \Exception(__('Invalid response.', 'helpfulcrowd-product-reviews'));
			}

			if (!in_array($response['code'], [self::HELPFULCROWD_STATUS_SUCCESS_NEW])) {
				throw new \Exception(__('Social register error.', 'helpfulcrowd-product-reviews'));
			}
		} catch (Exception $exception) {
			$status = false;
		}

		return $status;
	}

	/**
	 * Get Socials
	 *
	 * @return array[]|string[]
	 */
	public function helpfulcrowd_get_socials()
	{
		return $this->socials;
	}

	/**
	 * Init Social Form
	 * @return bool
	 */
	public static function helpfulcrowd_init_socials_form()
	{
		return (new HelpfulcrowdSocials)->helpfulcrowd_render_socials_form();
	}

	/**
	 * Handle Delete Social Link
	 */
	public function helpfulcrowd_handle_delete_social_link()
	{
		add_action('admin_post_delete_social', [$this, 'helpfulcrowd_delete_social_link']);
		add_action('admin_post_nopriv_delete_social', [$this, 'helpfulcrowd_delete_social_link']);
	}

	/**
	 * Delete social link
	 * @param WP_HTTP_Requests_Response $request
	 */
	public function helpfulcrowd_delete_social_link()
	{
		if (isset($_POST['social']) && in_array($_POST['social'], $this->socials)) {
			$social = sanitize_text_field($_POST['social']);
			if (!empty(get_option($this->helpfulcrowd_get_option_alias($social) . '_token', null))) {
				delete_option($this->helpfulcrowd_get_option_alias($social) . '_token');
			}

			if (!empty(get_option($this->helpfulcrowd_get_option_alias($social) . '_secret_token', null))) {
				delete_option($this->helpfulcrowd_get_option_alias($social) . '_secret_token');
			}

			if (!empty(get_option($this->helpfulcrowd_get_option_alias($social) . '_user_id', null))) {
				delete_option($this->helpfulcrowd_get_option_alias($social) . '_user_id');
			}
		}

		exit(wp_safe_redirect(wp_get_referer()));
	}

	/**
	 * Render Social Form
	 */
	private function helpfulcrowd_render_socials_form()
	{
		$socials = $this->helpfulcrowd_get_socials_meta();

		?>
		<div class="social_wrap">
			<?php foreach ($socials as $social => $link): ?>
				<div class="social_wrap_item">
					<div class="social_wrap_item_content">
						<div class="social_wrap_item_icon">
							<img src="<?php echo esc_url($this->helpfulcrowd_asset_icon(self::helpfulcrowd_get_alias($social), 'svg')); ?>" width="18"
								 alt="">
						</div>
						<div class="social_wrap_item_text"><?php echo esc_html(ucfirst($social)); ?></div>
					</div>
					<?php $used = !empty(get_option($this->helpfulcrowd_get_option_alias($social) . '_token', null)); ?>
					<div class="social_wrap_item_button social_wrap_item_button--<?php echo esc_attr(self::helpfulcrowd_get_alias($social)); ?> <?php echo $used ? 'social_wrap_item_button--remove' : ''; ?>">
						<a href="<?php echo !$used ? esc_url($link) : '#'; ?>"
						   class="social_wrap_item_button_btn" <?php if ($used): ?> onclick="event.preventDefault();document.getElementById('form-<?php echo esc_js(self::helpfulcrowd_get_alias($social)); ?>').submit()" <?php endif; ?>>
							<?php if (!$used): ?>
								<?php _e('Connect with', 'helpfulcrowd-product-reviews') . ' ' . ucfirst($social); ?>
							<?php else: ?>
								<?php _e('Disconnect', 'helpfulcrowd-product-reviews'); ?>
							<?php endif; ?>
						</a>

						<?php if ($used): ?>
							<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: none"
								  method="post" id="form-<?php echo esc_attr(self::helpfulcrowd_get_alias($social)); ?>">
								<input type="hidden" name="action" value="delete_social">
								<input type="hidden" name="social" value="<?php echo esc_attr($social); ?>">
							</form>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php

		return true;
	}

	/**
	 * Get Social Meta
	 *
	 * @return array
	 */
	private function helpfulcrowd_get_socials_meta()
	{
		$socials = $this->helpfulcrowd_get_socials();

		$inputs = [];
		foreach ($socials as $social) {
			$inputs[$social] = $this->helpfulcrowd_get_request_link($social);
		}

		return $inputs;
	}

	/**
	 * Get Social Url
	 *
	 * @param $social
	 * @return string
	 */
	private function helpfulcrowd_get_request_link($social)
	{
		return $this->request_url . '?social=' . $social . '&callback=' . get_rest_url(null, $this->callback_url);
	}

	/**
	 * Asset Icon
	 *
	 * @param $icon
	 * @param string $ext
	 *
	 * @return string
	 */
	private function helpfulcrowd_asset_icon($icon, $ext = 'png')
	{
		return $this->helpfulcrowd_get_plugin_url('../assets/img/socials/' . $icon . '.' . $ext);
	}

	/**
	 * Get Alias
	 *
	 * @param $store_name
	 * @return false|string
	 */
	private static function helpfulcrowd_get_alias($value)
	{
		$alias = str_replace(['-', ' '], '_', $value); // replace symbol
		$alias = preg_replace('![^\w\d\_]*!', '', $alias); // filter string
		$alias = preg_replace("/\_{2,}/", "_", $alias); // remove double symbol
		$alias = mb_strtolower($alias); // strtolower string

		return $alias;
	}

	/**
	 * Decomposition session key
	 *
	 * @param $encoded
	 * @return false|string
	 */
	private function helpfulcrowd_decomposition($encoded)
	{
		$strofsym = "qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=";
		$x = 0;

		while ($x++ <= strlen($strofsym)) {
			$tmp = md5(md5($this->alias . $strofsym[$x - 1]) . $this->alias);
			$encoded = str_replace($tmp[3] . $tmp[6] . $tmp[1] . $tmp[2], $strofsym[$x - 1], $encoded);
		}

		return base64_decode($encoded);
	}

	/**
	 * Get Option Name
	 *
	 * @param $social
	 * @return string
	 */
	private function helpfulcrowd_get_option_alias($social)
	{
		return 'helpfulcrowd_' . self::helpfulcrowd_get_alias($social);
	}

	/**
	 * Minimize
	 *
	 * @param $string
	 * @return string
	 */
	public static function helpfulcrowd_minimize($string)
	{
		return base64_encode($string);
	}

	/**
	 * UnMinimize
	 *
	 * @param $string
	 * @return string
	 */
	public static function helpfulcrowd_unminimize($string)
	{
		return base64_decode($string);
	}

}
