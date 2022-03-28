<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>
<h3><?php _e('Preload key requests / assets', 'wp-optimize'); ?></h3>
<div class="wpo-fieldgroup">
	<p class="wpo_min-bold-green wpo_min-rowintro">
		<?php _e('Preload critical assets to improve loading speed.', 'wp-optimize'); ?>
		<a href="https://getwpo.com/faqs/preload-critical-assets/"><?php _e('Learn more about preloading key requests.', 'wp-optimize'); ?></a>
	</p>
	<fieldset>
		<legend class="screen-reader-text">
		<?php _e('Preload key requests', 'wp-optimize'); ?>
		</legend>
		<input
			name="hpreload"
			id="hpreload"
			type="hidden"
			value="<?php echo esc_attr($wpo_minify_options['hpreload']); ?>"
		>
		<div class="asset-preload-main">
			<table class="asset-preload-list wpo-simple-table">
				<thead>
					<th><?php _e('Asset URL', 'wp-optimize'); ?></th>
					<th><?php _e('Asset type', 'wp-optimize'); ?></th>
					<th><?php _e('Cross origin', 'wp-optimize'); ?></th>
					<th>&nbsp;</th>
				</thead>
				<tbody>
					<tr class="nothing">
						<td colspan="4"><p><?php _e('No asset to preload', 'wp-optimize'); ?></p></td>
					</tr>
				</tbody>
			</table>
			<a href="#" class="add-asset wpo-repeater__add"><span class="dashicons dashicons-plus"></span> <?php _e('Add an asset', 'wp-optimize'); ?></a>
		</div>
	</fieldset>
</div>

<?php
/**
 * Backbone template - single item
 */
?>
<script type="text/html" id="tmpl-wpo-asset-preload--item">
	<td class="asset-href">{{data.href}}</td>
	<td class="asset-type">{{data.type}}</td>
	<td class="asset-crossorigin">
		<# if(data.crossorigin) { #>
			yes
		<# } else { #>
			no
		<# } #>
	</td>
	<td class="asset-edit">
		<button class="wpo-asset--edit button-link" type="button"><?php _e('Edit'); ?></button> <button class="wpo-asset--delete button-link button-link-delete" type="button"><?php _e('Delete'); ?></button>
	</td>
</script>

<?php
/**
 * Backbone template - add form
 */
?>
<script type="text/html" id="tmpl-wpo-asset-preload--form">
	<td data-label="<?php esc_attr_e('New asset', 'wp-optimize'); ?>">
		<input type="text" id="preload_href" placeholder="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/fonts/fontawesome-webfont.woff" <# if (data.href) { #>value="{{data.href}}"<# } #>>
	</td>
	<td>
		<select id="preload_type">
			<optgroup label="<?php esc_attr_e('Common values', 'wp-optimize'); ?>">
				<option value="script"><?php _e('Script', 'wp-optimize'); ?></option>
				<option value="style"><?php _e('Style', 'wp-optimize'); ?></option>
				<option value="font"><?php _e('Font', 'wp-optimize'); ?></option>
				<option value="image"><?php _e('Image', 'wp-optimize'); ?></option>
			</optgroup>
			<optgroup label="<?php esc_attr_e('Other values', 'wp-optimize'); ?>">
				<option value="audio"><?php _e('Audio', 'wp-optimize'); ?></option>
				<option value="document"><?php _e('Document', 'wp-optimize'); ?></option>
				<option value="embed"><?php _e('Embed', 'wp-optimize'); ?></option>
				<option value="object"><?php _e('Object', 'wp-optimize'); ?></option>
				<option value="track"><?php _e('Track', 'wp-optimize'); ?></option>
				<option value="video"><?php _e('Video', 'wp-optimize'); ?></option>
				<option value="worker"><?php _e('Worker', 'wp-optimize'); ?></option>
			</optgroup>
		</select>
	</td>
	<td>
		<input type="checkbox" id="preload_crossorigin" <# if (data.crossorigin) { #>checked="checked"<# } #>>
	</td>
	<td class="asset-preload-form--actions">
		<button type="button" class="button button-primary add-item" data-alt-label="<?php esc_attr_e('Save', 'wp-optimize'); ?>"><?php _e('Add', 'wp-optimize'); ?></button>
		<button type="button" class="button button cancel"><?php _e('Cancel', 'wp-optimize'); ?></button>
	</td>
</script>
