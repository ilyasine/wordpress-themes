<?php if (!defined('WPO_VERSION')) die('No direct access allowed');

global $wp_optimize_notices;

?>
<div class="wpo-plugin-family__premium">
	<h2><?php _e("Our Other Plugins", 'wp-optimize');?></h2>
	<div class="wpo-plugin-family__plugins">
		<div class="wpo-plugin-family__plugin">
			<?php
			$wp_optimize->wp_optimize_url('https://updraftplus.com/', null, '<img class="addons" alt="'.__("UpdraftPlus", 'wp-optimize').'" src="'. WPO_PLUGIN_URL.'images/features/updraftplus_logo.png' .'">');
			$wp_optimize->wp_optimize_url('https://updraftplus.com/', null, '<h3>'.__('UpdraftPlus – the ultimate protection for your site, hard work and business', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php _e("If you’ve got a WordPress website, you need a backup.", 'wp-optimize');?>
			</p>
			<p>
				<?php _e("Hacking, server crashes, dodgy updates or simple user error can ruin everything.", 'wp-optimize');?>
			</p>
			<p>
				<?php _e("With UpdraftPlus, you can rest assured that if the worst does happen, it's no big deal. rather than losing everything, you can simply restore the backup and be up and running again in no time at all.", 'wp-optimize');?>
			</p>
			<p>
				<?php _e("You can also migrate your website with few clicks without hassle.", 'wp-optimize');?>
			</p>
			<p>
				<?php _e("With a long-standing reputation for excellence and outstanding reviews, it’s no wonder that UpdraftPlus is the world’s most popular WordPress backup plugin.", 'wp-optimize');?>
			</p>
			<?php
			$ud_is_installed = WP_Optimize()->is_installed('ml-slider');
			if ($ud_is_installed['installed']) {
			?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php _e('Installed', 'wp-optimize'); ?></p>
			<?php
			} else {
				$wp_optimize->wp_optimize_url('https://updraftplus.com/', null, __('Try for free', 'wp-optimize'));
			}
			?>
		</div>
		<div class="wpo-plugin-family__plugin">
		<?php
			$wp_optimize->wp_optimize_url('https://updraftplus.com/updraftcentral/', null, '<img class="addons" alt="'.__("UpdraftCentral Dashboard
	", 'wp-optimize').'" src="'. WPO_PLUGIN_URL.'images/features/updraftcentral_logo.png' .'">');
			$wp_optimize->wp_optimize_url('https://updraftplus.com/', null, '<h3>'.__('UpdraftCentral – save hours managing multiple WP sites from one place', 'wp-optimize').'</h3>', 'class="other-plugin-title"');
			?>
			<p>
				<?php _e("If you manage a few WordPress sites, you need UpdraftCentral.", 'wp-optimize');?>
			</p>
			<p>
				<?php _e("UpdraftCentral is a powerful tool that allows you to efficiently manage, update, backup and even restore multiple websites from just one location. You can also manage users and comments on all the sites at once, and through its central login feature, you can access each WP-dashboard with a single click.", 'wp-optimize');?>
			</p>
			<p>
				<?php _e("With a wide range of useful features, including automated backup schedules and sophisticated one click updates, UpdraftCentral is sure to boost to your productivity and save you time.", 'wp-optimize');?>
			</p>
			<?php
			$udc_is_installed = WP_Optimize()->is_installed('updraftcentral');
			if ($udc_is_installed['installed']) {
			?>
				<p class="wpo-plugin-installed"><span class="dashicons dashicons-yes"></span> <?php _e('Installed', 'wp-optimize'); ?></p>
			<?php
			} else {
				$wp_optimize->wp_optimize_url('https://updraftplus.com/updraftcentral/', null, __('Try for free', 'wp-optimize'));
			}
			?>
		</div>
	</div>
</div>