<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<?php if (!empty($wpo_cache_excluded_posts)) { ?>
<table id="wpo_cache_excluded_posts">
	<thead>
		<tr>
			<th style="width: 80%;"><?php _e('Excluded posts:', 'wp-optimize'); ?></th>
			<th><?php _e('Post type', 'wp-optimize'); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($wpo_cache_excluded_posts as $post): ?>
		<tr>
			<td><a href="<?php echo admin_url('/post.php?action=edit&post='.$post['ID']); ?>" target="_blank"><?php echo $post['post_title']; ?></a></td>
			<?php
			$post_type_obj = get_post_type_object($post['post_type']);
			?>
			<td><?php echo $post_type_obj->labels->singular_name; ?></td>
			<td><a href="javascript:;" class="wpo-exclude-from-cache" data-id="<?php echo $post['ID']; ?>"><?php _e('Delete', 'wp-optimize'); ?></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php }
