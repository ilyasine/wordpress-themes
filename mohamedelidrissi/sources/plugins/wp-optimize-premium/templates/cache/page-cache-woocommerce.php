<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<hr>
<h4>WooCommerce</h4>
<div class="wpo-fieldgroup__subgroup">
	<label for="enable_cache_per_country">
		<input name="enable_cache_per_country" id="enable_cache_per_country" class="cache-settings" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_cache_per_country'], 1); ?>>
		<?php _e("Generate country-specific files", 'wp-optimize'); ?>
	</label>
	<span tabindex="0" data-tooltip="<?php _e("Enable this if you need to display a different price depending on the visitor's location (e.g. when applying different taxes like VAT depending on the country).", 'wp-optimize'); ?> <?php _e("This feature uses geolocation to determine the user's location, and defaults to the shop's location.", 'wp-optimize'); ?> <?php _e("When enabled, a cookie will be added, allowing WP-Optimize to serve the corresponding cache.", 'wp-optimize'); ?>"><span class="dashicons dashicons-editor-help"></span> </span>
</div>