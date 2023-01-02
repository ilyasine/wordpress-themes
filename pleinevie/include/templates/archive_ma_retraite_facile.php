<?php

global $wp_query, $posts_exclude;
$cat = rw_get_category_by_slug('ma-retraite-facile');
$child_categories = get_categories(['parent' => $cat->term_id]);
$current_cat = $wp_query->queried_object;
$current_cat = get_category($current_cat->term_id);
$desc_cat = category_description($current_cat->term_id);
$image = RW_Category::get_image_category($cat->term_id,'item_search_page','');
get_header();
?>
<div class="row">
	<div class="rubrique-ma-retraite-facile">
		<div class="col-lg-12">
			<?php
				echo "<div>".$image."</div>";
				echo "<div class='desc'>".$desc_cat."</div>"
			;?>
		</div>

		<?php
			if(!empty($child_categories) && $current_cat->category_parent==false){
				foreach($child_categories as $child){
					echo "<div class='col-lg-12'>
					<h2 class='text-uppercase'><a href='".get_category_link($child)."'>{$child->name}</a></h2>
					<div>".RW_Category::get_image_category($child->term_id,'item_search_page','')."</div>
					<div class='desc'>".category_description($child->term_id)."</div></div>";
				}
			}else{
				$data_posts = get_posts([
				    'category' => $current_cat->term_id,
				]);
				?>
				    <div class="col-lg-12">
				    	<h1 class="cat_name"><?php echo $current_cat->name?></h1>
				    	<div class="mt-30">
				    		<div class="row category_posts">
				    		    <?php include(locate_template('/include/templates/items-posts.php')); ?>
				    		</div>
				    	</div>
				    </div>
				<?php
			}
		?>
	</div>
</div>
<?php
get_footer();