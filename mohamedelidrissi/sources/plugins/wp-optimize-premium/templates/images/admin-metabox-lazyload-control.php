<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<div>
	<label for="wpo_disable_single_post_lazyload">
		<input id="wpo_disable_single_post_lazyload" type="checkbox" data-id="<?php echo $post_id; ?>" <?php checked($disable_lazyload); ?> >
		<?php printf(__('Disable lazy-load on this %s', 'wp-optimize'), $post_type); ?>
	</label>
</div>
