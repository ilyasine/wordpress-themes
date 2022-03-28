<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<div>
	<label for="wpo_disable_single_post_caching">
		<input id="wpo_disable_single_post_caching" type="checkbox" data-id="<?php echo $post_id; ?>" <?php checked($disable_caching); ?> >
		<?php echo sprintf(__('Do not cache this %s', 'wp-optimize'), $post_type); ?>
	</label>
</div>
