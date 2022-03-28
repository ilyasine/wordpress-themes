<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<div class="wpo-fieldgroup__subgroup">
	<label for="enable_user_specific_cache">
		<input name="enable_user_specific_cache" id="enable_user_specific_cache" class="cache-settings" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_user_specific_cache']); ?>>
		<?php _e('Enable user specific cache', 'wp-optimize'); ?>
	</label>
	<div class="notice notice-warning">
		<p>
			<strong><?php _e('Notice:', 'wp-optimize'); ?></strong>
			<?php _e('This option will create cache files for each user.', 'wp-optimize'); ?>
			<?php _e('As a result, the cache size might become large if there are many users on your website.', 'wp-optimize'); ?></span>
		</p>
		<?php if ($is_nginx) { ?>

			<p>
				<strong><?php _e('Important:', 'wp-optimize'); ?></strong><br>
				<?php echo sprintf(__('As the user specific cache might contain personal information, it is highly advised to configure your server to disallow direct access to %s.', 'wp-optimize'), htmlspecialchars($path_to_cache)); ?>
			</p>
			<p>
				<?php echo sprintf(__('Nginx configuration example:', 'wp-optimize')); ?><br>
<pre class="code">
location <?php echo $path_to_cache; ?> { 
	deny all; 
}
</pre>	
			</p>
		<?php } ?>
	</div>
</div>