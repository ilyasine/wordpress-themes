<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<h3 class="wpo-cloudflare-cache-options purge-cache" <?php echo $display ? "style='display:block'" : "style='display:none'"; ?>> <?php _e('Cloudflare settings', 'wp-optimize'); ?></h3>
<div class="wpo-fieldgroup cache-options wpo-cloudflare-cache-options purge-cache" <?php echo $display ? "style='display:block'" : "style='display:none'"; ?> >

	<?php if ($show_cloudflare_settings) : ?>

	<div class="notice error below-h2 wpo-error wpo-error__cloudflare-cache wpo_hidden"><p></p></div>

	<p>
		<input id="purge_cloudflare_cache" type="checkbox" name="purge_cloudflare_cache" class="cache-settings" <?php checked($purge_cloudflare_cache); ?>>
		<label for="purge_cloudflare_cache"><?php _e('Purge Cloudflare cached pages when WP-Optimize cache is purging', 'wp-optimize'); ?></label>
	</p>

	<div id="wpo_cloudflare_credentials" <?php echo $purge_cloudflare_cache ? 'style="display: block;"' : 'style="display: none;"'; ?>>
		<div class="wpo-cloudflare-options-form-wrap">
			<div class="wpo-cloudflare-options-form">
				<label for="cloudflare_api_email"><?php _e('Cloudflare API Email', 'wp-optimize'); ?></label>
				<p>
					<input type="text" name="cloudflare_api_email" id="cloudflare_api_email" class="cache-settings" value="<?php echo esc_attr($cloudflare_api_email); ?>">
				</p>

				<label for="cloudflare_api_key"><?php _e('Cloudflare API key', 'wp-optimize'); ?></label>
				<p>
					<input type="text" name="cloudflare_api_key" id="cloudflare_api_key" class="cache-settings" value="<?php echo esc_attr($cloudflare_api_key); ?>">
				</p>
			</div>

			<div class="wpo-cloudflare-options-form-separator">
				<div class="wpo-cloudflare-options-form-separator-text">
					<span>OR</span>
				</div>
			</div>

			<div class="wpo-cloudflare-options-form">
				<label for="cloudflare_api_token"><?php _e('Cloudflare API Token', 'wp-optimize'); ?></label>
				<p>
					<input type="text" name="cloudflare_api_token" id="cloudflare_api_token" class="cache-settings" value="<?php echo esc_attr($cloudflare_api_token); ?>">
				</p>
			</div>

		</div>

	</div>

	<?php else: ?>
	<p>
		<?php _e('This site is not using Cloudflare; no Cloudflare integration options are available.', 'wp-optimize'); ?>
	</p>
	<?php endif; ?>

</div>
