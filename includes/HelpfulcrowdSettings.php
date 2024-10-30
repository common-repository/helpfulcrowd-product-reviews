<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdBase.php';
require_once 'HelpfulcrowdMessage.php';

class HelpfulcrowdSettings extends HelpfulcrowdBase
{
	private $options;

	public function __construct()
	{
		add_action(
				'admin_menu',
				array($this, 'helpfulcrowd_init_plugin')
		);
		add_action(
				'admin_init',
				array($this, 'helpfulcrowd_register_settings')
		);
		add_filter(
				'plugin_action_links_helpfulcrowd/helpfulcrowd.php',
				array($this, 'helpfulcrowd_settings_link')
		);		
	}

	public function helpfulcrowd_init_plugin()
	{
		add_menu_page(
				__('HelpfulCrowd', 'helpfulcrowd-product-reviews'),
				__('HelpfulCrowd', 'helpfulcrowd-product-reviews'),
				'manage_options',
				'helpfulcrowd',
				array($this, 'helpfulcrowd_settings_page'),
				$this->helpfulcrowd_get_plugin_url('../assets/img/logo_icon.png')
		);
	}

	public function helpfulcrowd_register_settings()
	{
		register_setting(
				'helpfulcrowd_options_group',
				'helpfulcrowd_options',
				array($this, 'helpfulcrowd_sanitize')
		);

		add_settings_section(
				'helpfulcrowd_summary_section_id',
				__('Product Summary', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_print_section_info'),
				'helpfulcrowd-setting-admin'
		);

		add_settings_field(
				'summary_enabled',
				__('Summary Enabled', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_summary_enabled'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_summary_section_id'
		);

		add_settings_field(
				'summary_position',
				__('Summary Position', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_summary_position'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_summary_section_id'
		);

		add_settings_field(
				'summary_margin_top',
				__('Spacing above widget (in pixels)', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_summary_margin_top'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_summary_section_id'
		);

		add_settings_field(
				'summary_margin_bottom',
				__('Spacing below widget (in pixels)', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_summary_margin_bottom'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_summary_section_id'
		);
		
		
		add_settings_field(
				'summary_shortcode',
				__('Summary Shortcode', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_summary_shortcode'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_summary_section_id'
		);
		

		add_settings_section(
				'helpfulcrowd_tabs_section_id',
				__('Product Tabs', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_print_section_info'),
				'helpfulcrowd-setting-admin'
		);

		add_settings_field(
				'tabs_enabled',
				__('Tabs Enabled', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_tabs_enabled'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_tabs_section_id'
		);

		add_settings_field(
				'tabs_position',
				__('Tabs Position', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_tabs_position'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_tabs_section_id'
		);
		
		add_settings_field(
				'tabs_shortcode',
				__('Tabs Shortcode', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_tabs_shortcode'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_tabs_section_id'
		);

		add_settings_section(
				'helpfulcrowd_rating_section_id',
				__('Product Rating', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_print_section_info'),
				'helpfulcrowd-setting-admin'
		);

		add_settings_field(
				'rating_enabled',
				__('Rating Enabled', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_rating_enabled'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_rating_section_id'
		);

		add_settings_field(
				'rating_position',
				__('Rating Position', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_rating_position'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_rating_section_id'
		);

		add_settings_field(
				'rating_margin_top',
				__('Spacing above widget (in pixels)', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_rating_margin_top'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_rating_section_id'
		);

		add_settings_field(
				'rating_margin_bottom',
				__('Spacing below widget (in pixels)', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_rating_margin_bottom'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_rating_section_id'
		);

		add_settings_section(
				'helpfulcrowd_shotcode_section_id',
				__('Shortcodes', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_print_section_info'),
				'helpfulcrowd-setting-admin'
		);

		add_settings_field(
				'review_journal',
				__('Review Journal', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_review_journal'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_shotcode_section_id'
		);

		add_settings_field(
				'review_slider',
				__('Review Slider', 'helpfulcrowd-product-reviews'),
				array($this, 'helpfulcrowd_review_slider'),
				'helpfulcrowd-setting-admin',
				'helpfulcrowd_shotcode_section_id'
		);
	}

	public function helpfulcrowd_settings_link($links)
	{
		$url = esc_url(add_query_arg(
				'page',
				'helpfulcrowd',
				get_admin_url() . 'admin.php'
		));

		$settings_link = "<a href='$url'>" . __('Settings', 'helpfulcrowd-product-reviews') . '</a>';

		array_push(
				$links,
				$settings_link
		);
		return $links;
	}

	public function helpfulcrowd_settings_page()
	{
		wp_enqueue_style('helpfulcrowd-style', $this->helpfulcrowd_get_plugin_url('../assets/helpfulcrowd.css'), [], '1.0');

		if(!empty(get_option('helpfulcrowd_adminConsent')) && get_option('helpfulcrowd_adminConsent') == 'Yes')
		{
			$register = new HelpfulcrowdRegister();
		}
		$message = new HelpfulcrowdMessage();

		$this->options = get_option('helpfulcrowd_options');

		if (empty($this->options)) {
			$this->options = $this->helpfulcrowd_sanitize([], true);
			add_option('helpfulcrowd_options', $this->options);
		}

		$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
		$img_path = $this->helpfulcrowd_get_plugin_url('../assets/img/');

		$navs = [
				'dashboard' => __('Dashboard', 'helpfulcrowd-product-reviews'),
				'reviews' => __('Reviews', 'helpfulcrowd-product-reviews'),
				'q-a' => __('Q&A', 'helpfulcrowd-product-reviews'),
				'products' => __('Products', 'helpfulcrowd-product-reviews'),
				'settings' => __('Settings', 'helpfulcrowd-product-reviews')
		];

		?>
		<div class="wrap helpfulcrowd-container">
			<h2><?php _e('HelpfulCrowd', 'helpfulcrowd-product-reviews'); ?></h2>

			<?php if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
				<div class="notice notice-warning is-dismissible">
					<p><?php _e('Woocommerce required!', 'helpfulcrowd-product-reviews'); ?></p>
				</div>
			<?php } elseif (empty(get_option('helpfulcrowd_installed'))) { ?>
				<div class="notice notice-warning is-dismissible">
					<p><?php _e('Plugin is not activated!', 'helpfulcrowd-product-reviews'); ?></p>
				</div>
			<?php } else { ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php echo sprintf(__('Activated. Log to <a href="%1$s/" target="_blank">HelpfulCrowd Dashboard</a>, Email: <code>%2$s</code>','helpfulcrowd-product-reviews'), $this->helpfulcrowd_get_host(), get_option('admin_email')); ?>
						
					</p>
				</div>
			<?php } ?>
			<?php if((isset($_GET['hcapis']) && ($_GET['hcapis'] == 'reset'))) {
					$resetAPIs = new HelpfulcrowdResetAPI();	?>
				<div class="notice notice-success is-dismissible">
					<p>
						<?php _e('API\'s re configured for Helpfulcrowd Successfully.', 'helpfulcrowd-product-reviews'); ?>
					</p>
				</div>
			<?php
				} ?>
			<?php settings_errors(); ?>

			<?php if (!empty(get_option('helpfulcrowd_installed'))): ?>
				<div class="notice info-container">
					<!--Welcome Message-->
					<div class="info-welcome content-box">
						<img src="<?php echo esc_url($img_path . 'logo.jpg'); ?>" height="125" width="125" alt="Support">
						<div class="content-box-text">
							<h1 class="content-box-text-header"><?php esc_html_e(HelpfulcrowdMessage::helpfulcrowd_get_message('welcome_heading'), 'helpfulcrowd-product-reviews'); ?></h1>
							<p><?php esc_html_e(HelpfulcrowdMessage::helpfulcrowd_get_message('welcome_text'), 'helpfulcrowd-product-reviews'); ?></p>
						</div>
					</div>

					<div class="info-support-trial">
						<!--Help Message-->
						<div class="info-support content-box">
							<img src="<?php echo esc_url($img_path . 'support.png'); ?>" height="45" width="45" alt="Support">
							<div class="content-box-text">
								<p><?php esc_html_e(HelpfulcrowdMessage::helpfulcrowd_get_message('help_text'), 'helpfulcrowd-product-reviews'); ?></p>
							</div>
						</div>

						<!--Rate Us Message-->
						<div class="info-trial content-box">
							<img src="<?php echo esc_url($img_path . 'rate.png'); ?>" height="45" width="45" alt="Rate">
							<div class="content-box-text">
								<p><?php esc_html_e(HelpfulcrowdMessage::helpfulcrowd_get_message('rate_us_text'), 'helpfulcrowd-product-reviews'); ?></p>
							</div>

							<a href="https://wordpress.org/support/plugin/helpfulcrowd-product-reviews/reviews" target="_blank" class="btn btn-rate">
								<?php _e('Rate us today!', 'helpfulcrowd-product-reviews'); ?>
							</a>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<?php $trialstarted = false; ?>
			<?php if (!empty($msg_g = HelpfulcrowdMessage::helpfulcrowd_get_message('trial_end_notice'))): ?>
				<!--Trial end notice message-->
				<?php $trialstarted = true; ?>
				<div class="notice notice-warning notice-trial notice-warning-fill">
					<div class="content-box-text">
						<p><?php echo wp_kses_data($msg_g); ?></p>
					</div>
				</div>
			<?php elseif (!empty($msg_g = HelpfulcrowdMessage::helpfulcrowd_get_message('plan_info_text'))): ?>
				<!--Plan into text message-->
				<?php $trialstarted = true; ?>
				<div class="notice notice-success notice-trial notice-success-fill">
					<p><?php echo wp_kses_data($msg_g); ?></p>
				</div>
			<?php elseif (!empty($msg_g = HelpfulcrowdMessage::helpfulcrowd_get_message('start_trial_text'))): ?>
				<!--Start trial message-->
				<div class="notice notice-warning notice-trial is-dismissible">
					<img src="<?php echo esc_url($img_path . 'trial.png'); ?>" height="115" width="115" alt="Support">
					<div class="content-box-text">
						<p><?php echo wp_kses_data($msg_g); ?></p>
					</div>

					<a href="?page=helpfulcrowd&state=trial" class="btn btn-rate">
						<?php _e('Start Trial', 'helpfulcrowd-product-reviews'); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if (!empty(get_option('helpfulcrowd_installed'))): ?>
				<h2 class="nav-tab-wrapper">
					<?php foreach ($navs as $alias => $nav): ?>
						<a href="<?php echo esc_url('?page=helpfulcrowd&tab='.$alias); ?>"
						   class="nav-tab <?php echo $active_tab == $alias ? esc_attr('nav-tab-active') : ''; ?>"><?php esc_html_e($nav, 'helpfulcrowd-product-reviews'); ?></a>
					<?php endforeach; ?>
				</h2>

			<?php
			$frame_host = $this->helpfulcrowd_get_host() . '/integrations/woocommerce/';
			$frame_auth = '?store_id=' . get_option('helpfulcrowd_store_id') . '&token=' . $this->helpfulcrowd_get_consumer_convert();

			if (in_array($active_tab, ['dashboard', 'reviews', 'q-a', 'products'])) {
			$header = [
					'dashboard' => __('Helpful Dashboard', 'helpfulcrowd-product-reviews'),
					'reviews' => __('Reviews', 'helpfulcrowd-product-reviews'),
					'products' => __('Products', 'helpfulcrowd-product-reviews'),
					'q-a' => __('Q&A', 'helpfulcrowd-product-reviews')
			];

			switch ($active_tab) {
				case 'reviews':
					$frame_host .= 'reviews/moderation';
					break;

				case 'q-a':
					$frame_host .= 'q-a/moderation';
					break;

				case 'products':
					$frame_host .= 'products';
					break;

				default:
					$frame_host .= 'dashboard';
			}

			$frame_host .= $frame_auth;

			?>
				<script>
					var helpfulcrowdStoreId = '<?php echo get_option('helpfulcrowd_store_id');?>';
					var helpfulcrowdWidgets = ['review_slider'];
				</script>
				<script>
					function loadContentFromHCApp(url)
					{
						jQuery.get(url)
						.done(function(data){
							jQuery('.tabs-main-content').html(data);
							jQuery('.tabs-main-content').find("a").each(function(){
								var ahref = jQuery(this).attr('href');
								if(ahref.indexOf('<?php echo $this->helpfulcrowd_get_host(); ?>') == -1 && ahref.indexOf('mailto') == -1 && ahref.indexOf('<?php echo site_url(); ?>') == -1){
									ahref = '<?php echo $this->helpfulcrowd_get_host(); ?>'+ahref;
									jQuery(this).attr('href', ahref);
								}
							});
							jQuery('.tabs-main-content').find(".woocommerce-link").each(function(){
								jQuery(this).bind('click', function(e) {  
									var ahref = jQuery(this).attr('href');								
									loadContentFromHCApp(ahref);
									e.preventDefault(); // stop the browser from following the link
								});
							});
							jQuery('.tabs-main-content').find("form").each(function(){
									var formaction = jQuery(this).attr("action");
									if(formaction.indexOf('<?php echo $this->helpfulcrowd_get_host(); ?>') == -1){
										formaction = '<?php echo $this->helpfulcrowd_get_host(); ?>'+formaction;
									}
									jQuery(this).attr('action', formaction);
							});
						  })
						  .fail(function() {
							jQuery('.tabs-main-content').html("Error Loading Content...");
						  })
					}
					
				</script>

				<h3><?php echo esc_html($header[$active_tab]) ?? ''; ?></h3>

			<?php if ($active_tab == 'reviews'): ?>
				<div class="hc-widget">
					<div data-hc="review-slider"></div>
				</div>
			<?php endif; ?>
				<div class="tabs-main-content"></div>
				<script>
				loadContentFromHCApp('<?php echo $frame_host; ?>');
				</script>
			<?php

			} elseif ($active_tab == 'settings')  {
			wp_enqueue_script('helpfulcrowd-settings', $this->helpfulcrowd_get_plugin_url('../assets/helpfulcrowd_settings.js'), array('jquery'), '1.0');

			$settings_accordion = [
					$frame_host . 'settings/design' . $frame_auth => __('Design', 'helpfulcrowd-product-reviews'),
					'settings/placement' => __('Widgets', 'helpfulcrowd-product-reviews'),
					//$frame_host . 'settings/widgets' . $frame_auth => __('Widget Options', 'helpfulcrowd-product-reviews'),
					'settings/seed' => __('Data Import', 'helpfulcrowd-product-reviews'),
					//'settings/socials' => 'Connect social accounts'
			];

			foreach ($settings_accordion as $url => $item):
			?>
				<div class="helpfulcrowd-setting-accordion-tab">
					<div class="helpfulcrowd-setting-accordion-tab-header">
						<span><?php echo esc_html($item); ?></span>
						<span class="svg">
                                <svg height="1em" viewBox="0 0 1792 1792" width="1em"
									 xmlns="http://www.w3.org/2000/svg"><g><path
												d="M1427 301l-531 531 531 531q19 19 19 45t-19 45l-166 166q-19 19-45 19t-45-19l-742-742q-19-19-19-45t19-45l742-742q19-19 45-19t45 19l166 166q19 19 19 45t-19 45z"
												fill="currentColor"></path><rect
												class="hc-icon__transparent-background"></rect></g></svg>
                            </span>
					</div>
					<div class="helpfulcrowd-setting-accordion-tab-content">
						<?php if ($url == 'settings/placement'): ?>
							<div class="widget_placement_wrap">
								<form method="post" class="widget_placement_wrap-item" action="options.php"
									  style="padding: 20px">
									<?php
									settings_fields('helpfulcrowd_options_group');
									do_settings_sections('helpfulcrowd-setting-admin');

									submit_button(__('Save', 'helpfulcrowd-product-reviews'));
									?>
								</form>
							</div>
						<?php elseif ($url == 'settings/socials'): ?>
							<?php //HelpfulcrowdSocials::helpfulcrowd_init_socials_form(); ?>
						<?php elseif ($url == 'settings/seed'): ?>
							<div class="widget_placement_wrap">
								<div class="widget_placement_wrap-item" style="text-align: center; padding: 40px;">
									<?php if($trialstarted): ?>
									<a style="padding:10px; background:#f49251; color:#fff;" href="<?php echo esc_url($this->helpfulcrowd_get_host().'/cp/tools/data_import'); ?>" target="_blank"><?php _e('Goto Main App', 'helpfulcrowd-product-reviews'); ?></a>
									<?php else: ?>
									<a style="padding:10px; background:#f49251; color:#fff;" href="?page=helpfulcrowd&state=trial"><?php _e('Start trial and Import in Main App', 'helpfulcrowd-product-reviews'); ?></a>
									<?php endif; ?>
								</div>
							</div>
						<?php else: ?>
							<div class="tabs-main-content"></div>
							<script>
								jQuery('.tabs-main-content').load('<?php echo $url; ?>');
							</script>		
						<?php endif; ?>
					</div>
				</div>

			<?php
			endforeach;
			}
				?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function helpfulcrowd_rating_enabled()
	{
		$this->helpfulcrowd_checkbox('rating_enabled', 'rating.png');
	}

	public function helpfulcrowd_summary_enabled()
	{
		$this->helpfulcrowd_checkbox('summary_enabled', 'summary.png');
	}

	public function helpfulcrowd_tabs_enabled()
	{
		$this->helpfulcrowd_checkbox('tabs_enabled', 'tabs.png');
	}

	public function helpfulcrowd_sidebar_enabled()
	{
		$this->helpfulcrowd_checkbox('sidebar_enabled', '');
	}

	public function helpfulcrowd_rating_position()
	{
		$options = array(
				'woocommerce_before_shop_loop_item' => __('Above Item', 'helpfulcrowd-product-reviews'),
				'woocommerce_before_shop_loop_item_title' => __('Above Title', 'helpfulcrowd-product-reviews'),
				'woocommerce_shop_loop_item_title' => __('Below Title', 'helpfulcrowd-product-reviews'),
				'woocommerce_after_shop_loop_item_title' => __('Below Info', 'helpfulcrowd-product-reviews'),
				'woocommerce_after_shop_loop_item' => __('Below Item', 'helpfulcrowd-product-reviews'),
				'uael_woo_products_title_before' => __('UAEL Above Title', 'helpfulcrowd-product-reviews'),
				'uael_woo_products_title_after' => __('UAEL Below Title', 'helpfulcrowd-product-reviews'),
				'uael_woo_products_price_after' => __('UAEL Below Price', 'helpfulcrowd-product-reviews'),
		);

		$this->helpfulcrowd_select('rating_position', $options);
	}

	public function helpfulcrowd_summary_position()
	{
		$options = array(
				'woocommerce_product_thumbnails' => __('Below Images', 'helpfulcrowd-product-reviews'),
				'woocommerce_single_product_summary' => __('Below Title', 'helpfulcrowd-product-reviews'),
				'woocommerce_before_add_to_cart_form' => __('Above Cart Button', 'helpfulcrowd-product-reviews'),
				'woocommerce_after_add_to_cart_form' => __('Below Cart Button', 'helpfulcrowd-product-reviews'),
				'woocommerce_product_meta_start' => __('Above Meta', 'helpfulcrowd-product-reviews'),
				'woocommerce_product_meta_end' => __('Below Meta', 'helpfulcrowd-product-reviews'),
				'hc_summary_shortcode' => __('Use Shortcode', 'helpfulcrowd-product-reviews'),
		);

		$this->helpfulcrowd_select('summary_position', $options);
	}
	
	/*
	 * Summary Short code
	 */
	public function helpfulcrowd_summary_shortcode()
	{
		?>
		<p><?php _e('If you would like to add the widget manually inside a page-builder or product page content generated by another plugin, please use the shortcode.', 'helpfulcrowd-product-reviews') ?></p>
		<p><code> [helpfulcrowd_product_summary_widget] </code></p>
		<?php
	}
	
	
	public function helpfulcrowd_tabs_position()
	{
		$options = array(
				'woocommerce_product_thumbnails' => __('Below Images', 'helpfulcrowd-product-reviews'),
				'woocommerce_share' => __('Below Info', 'helpfulcrowd-product-reviews'),
				'woocommerce_after_single_product' => __('Page Bottom', 'helpfulcrowd-product-reviews'),
				'hc_tabs_shortcode' => __('Use Shortcode', 'helpfulcrowd-product-reviews'),
		);

		$this->helpfulcrowd_select('tabs_position', $options);
	}
	
	/*
	 * Tab Short code
	 */
	public function helpfulcrowd_tabs_shortcode()
	{
		?>
		<p><?php _e('If you would like to add the widget manually inside a page-builder or product page content generated by another plugin, please use the shortcode.','helpfulcrowd-product-reviews') ?></p>
		<p><code> [helpfulcrowd_product_tab_widget] </code></p>
		<?php
	}
	
	

	public function helpfulcrowd_rating_margin_top()
	{
		$this->helpfulcrowd_number('rating_margin_top');
	}

	public function helpfulcrowd_rating_margin_bottom()
	{
		$this->helpfulcrowd_number('rating_margin_bottom');
	}

	public function helpfulcrowd_summary_margin_top()
	{
		$this->helpfulcrowd_number('summary_margin_top');
	}

	public function helpfulcrowd_summary_margin_bottom()
	{
		$this->helpfulcrowd_number('summary_margin_bottom');
	}

	protected function helpfulcrowd_checkbox($name, $image = false)
	{
		?>
		<label class="checkbox" for="<?php esc_attr_e($name); ?>">
			<input type="checkbox" id="<?php esc_attr_e($name); ?>" name="helpfulcrowd_options[<?php esc_attr_e($name); ?>]" value="1"
				   <?php if ($this->options[$name]){ ?>checked="checked"<?php } ?>/>
			<div class="checkbox__text"></div>
		</label>
	
		<?php if($image) 
		{ ?>
			<p>
				<img src="<?php echo esc_url($this->helpfulcrowd_get_plugin_url('../assets/img/' . $image)); ?>" alt=""/>
			</p>
			<?php 
		}
	}

	protected function helpfulcrowd_select($name, $options, $image = false)
	{
		?>
		<select name="helpfulcrowd_options[<?php esc_attr_e($name); ?>]">
			<?php foreach ($options as $value => $title) { ?>
				<option value="<?php esc_attr_e($value); ?>"
						<?php if ($this->options[$name] == $value) { ?>selected="selected"<?php } ?>><?php esc_html_e($title); ?></option>
			<?php } ?>
		</select>
		<?php if ($image) { ?>
		<p>
			<img src="<?php echo esc_url(plugin_dir_url('') . HELPFULCROWD_PLUGIN_NAME . '/assets/img/' . $image); ?>" alt=""/>
		</p>
	<?php }
	}

	protected function helpfulcrowd_number($name)
	{
		?>
		<input name="helpfulcrowd_options[<?php esc_attr_e($name); ?>]" type="number"
			   value="<?php esc_attr_e( (int)$this->options[$name]); ?>" step="1"/>
		<?php
	}

	public function helpfulcrowd_review_journal()
	{
		?>
		<code>[helpfulcrowd_review_journal]</code>
		<?php
	}

	public function helpfulcrowd_review_slider()
	{
		?>
		<code>[helpfulcrowd_review_slider]</code>
		<?php
	}

	public function helpfulcrowd_print_section_info()
	{
	}

	public function helpfulcrowd_sanitize($input, $default = false)
	{
		$new_input = array();

		$new_input['rating_enabled'] = (int) key_exists('rating_enabled', $input) ? $input['rating_enabled'] : $default;
		$new_input['summary_enabled'] = (int) key_exists('summary_enabled', $input) ? $input['summary_enabled'] : $default;
		$new_input['tabs_enabled'] = (int) key_exists('tabs_enabled', $input) ? $input['tabs_enabled'] : $default;
		$new_input['sidebar_enabled'] = (int) key_exists('sidebar_enabled', $input) ? $input['sidebar_enabled'] : $default;

		$new_input['rating_position'] = isset($input['rating_position']) ? $input['rating_position'] : 'woocommerce_shop_loop_item_title';
		$new_input['summary_position'] = isset($input['summary_position']) ? $input['summary_position'] : 'woocommerce_single_product_summary';
		$new_input['tabs_position'] = isset($input['tabs_position']) ? $input['tabs_position'] : 'woocommerce_after_single_product';

		$new_input['rating_margin_top'] = key_exists('rating_margin_top', $input) ? (int)$input['rating_margin_top'] : 0;
		$new_input['rating_margin_bottom'] = key_exists('rating_margin_bottom', $input) ? (int)$input['rating_margin_bottom'] : 0;
		$new_input['summary_margin_top'] = key_exists('summary_margin_top', $input) ? (int)$input['summary_margin_top'] : 0;
		$new_input['summary_margin_bottom'] = key_exists('summary_margin_bottom', $input) ? (int)$input['summary_margin_bottom'] : 0;
		return $new_input;
	}
	
}