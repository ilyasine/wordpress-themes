<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<p class="innodb_force_optimize--container hidden">
	<input id="innodb_force_optimize_single" type="checkbox">
	<label for="innodb_force_optimize_single"><?php _e('Optimize InnoDB tables anyway.', 'wp-optimize'); ?></label>
	<a href="https://getwpo.com/faqs/" target="_blank"><?php _e('Warning: you should read the FAQ on the risks of this operation first.', 'wp-optimize'); ?></a>
</p>