<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div class="wpo-power-tweaks-section">
	<h3 class="wpo-first-child"><?php _e('Power tweaks', 'wp-optimize'); ?></h3>

	<div class="power-tweaks--list">
		<?php
			/**
			 * Display the power tweaks
			 */
			do_action('wpo_power_tweaks_output');
		?>
	</div>

</div>