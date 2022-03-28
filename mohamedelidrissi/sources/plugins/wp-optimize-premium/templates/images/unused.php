<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<div id="wpo-unused-images-section">

	<div id="wpo_unused_images_shade" class="wpo_shade">
		<div class="wpo_shade_inner">
			<span class="dashicons dashicons-update-alt wpo-rotate"></span>
			<h4><?php _e('Loading data...', 'wp-optimize'); ?></h4>
		</div>
	</div>

	<h3 class="wpo-first-child"><?php _e('Unused images', 'wp-optimize');?></h3>

	<div class="wpo-unused-images-section-unloaded wpo-fieldgroup">
		<div class="wpo_shade hidden">
			<div class="wpo_shade_inner">
				<span class="dashicons dashicons-update-alt wpo-rotate"></span>
				<h4><?php _e('Loading data...', 'wp-optimize'); ?></h4>
				<p class="wpo-shade-progress-message"></p>
				<a class="wpo-unused-images-cancel-scan" data-mode="detect_unused_images"><?php _e('Abort scan', 'wp-optimize'); ?></a>
			</div>
		</div>

		<p>
			<button id="wpo_scan_for_unused_images" class="button button-primary"><?php _e('Scan website for unused images', 'wp-optimize'); ?></button>
		</p>
	</div>

	<div class="wpo-unused-images-section-loaded wpo_hidden">

		<div class="wpo-last-scan-info">
			<?php _e('Last scan:', 'wp-optimize'); ?> <span class="wpo-last-scan-text">2020/05/20 @ 12:00</span>
			<a href="javascript:;" id="wpo_unused_images_refresh" class="wpo-refresh-button"><span class="dashicons dashicons-image-rotate"></span><?php _e('Scan again', 'wp-optimize'); ?></a>
		</div>

		<div class="wp-optimize-images-download-csv wpo-unused-images-el">
			<a href="<?php echo add_query_arg(array('wpo_unused_images_csv' => '1', '_nonce' => wp_create_nonce('wpo_unused_images_csv'))); ?>"><?php _e('Download as CSV', 'wp-optimize'); ?></a>
		</div>
		<div class="wpo_unused_images_switch_view">
			<a href="javascript:;" data-mode="grid"><span class="dashicons dashicons-grid-view"></span></a>
			<a href="javascript:;" data-mode="list"><span class="dashicons dashicons-list-view"></span></a>
		</div>

		<div class="notice notice-info wpo-notice-bordered wpo-unused-images-trash-el">
			<p>
				<span class="dashicons dashicons-trash"></span>
				<span><?php _e('Trash', 'wp-optimize'); ?></span>
				<a href="javascript:;" id="wpo_unused_images_view_images_btn"><?php _e('Back to unused images list', 'wp-optimize'); ?></a>
			</p>
		</div>

		<div class="wp-optimize-images-refresh-icon" style="float:right">
			<a href="javascript:;" id="wpo_unused_images_view_trash_btn" class="wpo-refresh-button"><span class=""></span><?php _e('View trash', 'wp-optimize'); ?></a>
		</div>

		<div class="wpo_unused_images_buttons_wrap">
			<a href="javascript:;" id="wpo_unused_images_select_all"><?php _e('Select all', 'wp-optimize'); ?></a> /
			<a href="javascript:;" id="wpo_unused_images_select_none"><?php _e('Select none', 'wp-optimize'); ?></a>
		</div>

		<div id="wpo_unused_images"></div>
		<div id="wpo_unused_images_trash"></div>

		<p id="wpo_unused_images_loaded_count"></p>
		<p id="wpo_unused_images_trash_loaded_count"></p>

		<div id="wpo_unused_images_loader_bottom">
			<img width="16" height="16" src="<?php echo admin_url(); ?>/images/spinner-2x.gif" />
		</div>

		<div id="wpo_unused_images_control_panel" class="wpo-fieldgroup">
			<div id="wpo_unused_images_sites_select_container">
				<label for="wpo_unused_images_sites_select"><?php _e('Select site', 'wp-optimize');?> </label>
				<select id="wpo_unused_images_sites_select"></select>
			</div>
			<div class="notice notice-warning wpo-warning">
				<p>
					<span class="dashicons dashicons-shield"></span>
					<?php _e('This action is irreversible if you do not have a backup.', 'wp-optimize'); ?><br>
					<?php _e('You are recommended to review all images and take a backup before running this action.', 'wp-optimize'); ?><br>
					<strong><?php _e('You may have plugins which do not correctly register their images as in-use.', 'wp-optimize'); ?></strong>
				</p>
			</div>
			<input type="button" id="wpo_move_unused_images_to_trash_btn" class="button button-primary button-large wpo-unused-images-el" value="<?php esc_attr_e('Move selected images to trash', 'wp-optimize'); ?>" />
			<input type="button" id="wpo_remove_unused_images_btn" class="button button-large wpo-unused-images-el" value="<?php esc_attr_e('Delete the selection permanently', 'wp-optimize'); ?>" />

			<input type="button" id="wpo_restore_unused_images_from_trash_btn" class="button button-primary button-large wpo-unused-images-trash-el" value="<?php esc_attr_e('Restore the selection', 'wp-optimize'); ?>" />
			<input type="button" id="wpo_remove_unused_images_from_trash_btn" class="button button-large wpo-unused-images-trash-el" value="<?php esc_attr_e('Delete the selection permanently', 'wp-optimize'); ?>" />

			<?php $wp_optimize->include_template('take-a-backup.php', false, array('checkbox_name' => 'enable-auto-backup-2')); ?>
		</div>

	</div>

</div>

<div id="wpo-unused-image-sizes-section">
	<h3><?php _e('Image sizes', 'wp-optimize'); ?></h3>

	<div class="wpo-unused-image-sizes-section-unloaded wpo-fieldgroup">
		<div class="wpo_shade hidden">
			<div class="wpo_shade_inner">
				<span class="dashicons dashicons-update-alt wpo-rotate"></span>
				<h4><?php _e('Loading data...', 'wp-optimize'); ?></h4>
				<p class="wpo-shade-progress-message"></p>
				<a class="wpo-unused-images-cancel-scan" data-mode="detect_images_sizes"><?php _e('Abort scan', 'wp-optimize'); ?></a>
			</div>
		</div>

		<p>
			<button id="wpo_scan_for_unused_image_sizes" class="button button-primary"><?php _e('Scan website for unused image sizes', 'wp-optimize'); ?></button>
		</p>
	</div>

	<div class="wpo-unused-image-sizes-section-loaded wpo-fieldgroup wpo_hidden">
		<div class="wpo-fieldgroup">
			<h3><?php _e('Registered image sizes', 'wp-optimize'); ?></h3><img class="wpo_unused_images_loader" width="20" height="20" src="<?php echo admin_url(); ?>/images/spinner-2x.gif" />
			<div id="registered_image_sizes"></div>
			<h3><?php _e('Unused image sizes', 'wp-optimize');?></h3><img class="wpo_unused_images_loader" width="20" height="20" src="<?php echo admin_url(); ?>/images/spinner-2x.gif" />
			<p class="hide_on_empty">
				<?php _e('These image sizes were used by some of the themes or plugins installed previously and they remain within your database.', 'wp-optimize'); ?>
				<a href="https://codex.wordpress.org/Post_Thumbnails#Add_New_Post_Thumbnail_Sizes" target="_blank"><?php _e('Read more about custom image sizes here.', 'wp-optimize'); ?></a>
			</p>
			<div id="unused_image_sizes"></div>

			<div class="wpo_remove_selected_sizes_btn__container">
				<div class="notice notice-warning wpo-warning">
					<p>
						<span class="dashicons dashicons-shield"></span>
						<?php _e("This feature is for experienced users. Don't remove registered image sizes if you are not sure that images with selected sizes are not used on your site.", 'wp-optimize'); ?>
					</p>
				</div>

				<div class="wpo-last-scan-info">
					<?php _e('Last scan:', 'wp-optimize'); ?> <span class="wpo-last-scan-text">2020/05/20 @ 12:00</span>
					<a href="javascript:;" id="wpo_unused_image_sizes_refresh" class="wpo-refresh-button"><span class="dashicons dashicons-image-rotate"></span><?php _e('Scan again', 'wp-optimize'); ?></a>
				</div>

				<input type="button" id="wpo_remove_selected_sizes_btn" class="button button-primary button-large" value="<?php esc_attr_e('Remove selected sizes', 'wp-optimize'); ?>" disabled />
				<?php $wp_optimize->include_template('take-a-backup.php', false, array('checkbox_name' => 'enable-auto-backup-3')); ?>
			</div>
		</div>
	</div>
</div>
