<?php

defined('ABSPATH') || exit;

require_once 'HelpfulcrowdBase.php';

class HelpfulcrowdWidgets extends HelpfulcrowdBase
{

	private $options;

	public function __construct()
	{
		$this->options = get_option('helpfulcrowd_options');
		$this->store_id = get_option('helpfulcrowd_store_id');

		if($this->options['summary_position'] == 'hc_summary_shortcode') {
			add_shortcode( 'helpfulcrowd_product_summary_widget',
				array($this,'helpfulcrowd_product_summary') );
		} else {
			add_action(
				$this->options['summary_position'],
				array($this, 'helpfulcrowd_product_summary'),
				6
			);
		}
		
		if($this->options['tabs_position'] == 'hc_tabs_shortcode') {
			add_shortcode( 'helpfulcrowd_product_tab_widget',
				array($this,'helpfulcrowd_product_tabs') );
		} else {
			add_action(
					$this->options['tabs_position'],
					array($this, 'helpfulcrowd_product_tabs'),
					20
			);
		}

		add_action(
				$this->options['rating_position'],
				array($this, 'helpfulcrowd_product_rating'),
				20
		);

		
		add_action(
				'wp_footer',
				array($this, 'helpfulcrowd_sidebar'),
				20
		);
		
		add_action(
				'wp_head',
				array($this, 'helpfulcrowd_widgets_embed'),
				30
		);

		add_shortcode(
				'helpfulcrowd_review_journal',
				array($this, 'helpfulcrowd_review_journal')
		);

		add_shortcode(
				'helpfulcrowd_review_slider',
				array($this, 'helpfulcrowd_review_slider')
		);
		
		
	}

	public function helpfulcrowd_product_summary()
	{
		$psummary = '';
		if ($this->options['summary_enabled'] && $this->options['summary_position']) {			
			static $count = 0;
		
			global $product;
			if(!$product->get_id()) {
				return false;
			}
		
			$this->helpfulcrowd_add_storeid_script();
			
			$style = array();
	
			if ($this->options['summary_margin_top']) {
				$style[] = "margin-top:" . $this->options['summary_margin_top'] . 'px';
			}
			if ($this->options['summary_margin_bottom']) {
				$style[] = "margin-bottom:" . $this->options['summary_margin_bottom'] . 'px';
			}
	
			if (!$count) {
				$psummary .= "<script>helpfulcrowdWidgets.push('product_summary')</script>";
			}
			$psummary .= '<div class="hc-widget" ';
			if (!empty($style)) { 
				$psummary .= 'style="'.esc_attr(implode(';', $style)).'"'; 
			}
			$psummary .= '><div data-hc="product-summary" data-hc-id="'.esc_attr($product->get_id()).'"></div></div>';
			$count++;
		}
		if($this->options['summary_position'] == 'hc_summary_shortcode') {
			return $psummary;
		} else {
			echo $psummary;
		}
	}

	public function helpfulcrowd_product_tabs()
	{
		$ptabs = '';
		static $count = 0;		
		
		if ($this->options['tabs_enabled'] && $this->options['tabs_position']) {			
			global $product;		
			if(!$product->get_id()){
				return false;
			}
			
			$this->helpfulcrowd_add_storeid_script();
			
			if (!$count) {
				$ptabs .= "<script>helpfulcrowdWidgets.push('product_tabs')</script>";
			}
			$ptabs .= '<div class="hc-widget"><div data-hc="product-tabs" data-hc-id="'.esc_attr($product->get_id()).'"></div></div>';
			$count++;
		}
		if($this->options['tabs_position'] == 'hc_tabs_shortcode') {
			return $ptabs;
		} else {
			echo $ptabs;
		}
		
		
	}

	public function helpfulcrowd_product_rating()
	{
		static $count = 0;
		
		if ($this->options['rating_enabled'] && $this->options['rating_position']) {
			
			global $product;
			if(!$product->get_id()) {
				return false;
			}
			
			$this->helpfulcrowd_add_storeid_script();
	
			$style = array();
	
			if ($this->options['rating_margin_top']) {
				$style[] = "margin-top:" . $this->options['rating_margin_top'] . 'px';
			}
			if ($this->options['rating_margin_bottom']) {
				$style[] = "margin-bottom:" . $this->options['rating_margin_bottom'] . 'px';
			}
	
			if (!$count) {
				?>
				<script>helpfulcrowdWidgets.push('product_rating')</script>
				<?php
			}
	
			?>
			<div class="hc-widget" <?php if ($style) { ?>style="<?php echo esc_attr(implode(';', $style)); ?>"<?php } ?>>
				<div data-hc="product-rating" data-hc-id="<?php echo esc_attr($product->get_id()); ?>"></div>
			</div>
			<?php
			$count++;
		}
	}

	public function helpfulcrowd_review_journal()
	{
		static $count = 0;
		$this->helpfulcrowd_add_storeid_script();

		$string = '';

		if (!$count) {
			$string .= "<script>helpfulcrowdWidgets.push('review_journal')</script>";
		}

		$string .= '<div class="hc-widget"><div data-hc="review-journal"></div></div>';

		$count++;
		return $string;
	}

	public function helpfulcrowd_review_slider()
	{
		static $count = 0;
		$this->helpfulcrowd_add_storeid_script();

		$string = '';

		if (!$count) {
			$string .= "<script>helpfulcrowdWidgets.push('review_slider')</script>";
		}

		$string .= '<div class="hc-widget"><div data-hc="review-slider"></div></div>';

		$count++;
		return $string;
	}

	public function helpfulcrowd_sidebar()
	{
		static $count = 0;
		
		if ($this->options['sidebar_enabled']) {
			
			$this->helpfulcrowd_add_storeid_script();
			
			if (!$count) {
				?>
				<script>helpfulcrowdWidgets.push('sidebar')</script>
				<?php
			}
			
			$count++;
		}
	}
	
	public function helpfulcrowd_widgets_embed() {
		$string = '<script>var $ = jQuery; </script>';
		echo $string;
	}
	
	protected function helpfulcrowd_add_storeid_script()
	{
		static $count = 0;

		if (!$count) {
			wp_enqueue_script('helpfulcrowd-mail', $this->helpfulcrowd_get_plugin_url('../assets/helpfulcrowd.js'), array('jquery'), '1.4');
			?>
			<script>
				var helpfulcrowdStoreId = '<?php echo $this->store_id;?>';
				var helpfulcrowdWidgets = [];
			</script>
			<?php
		}

		$count++;
	}
	
}