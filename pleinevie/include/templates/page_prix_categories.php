<div class="rubrique_info rubrique_info-childs category-<?php echo $category->term_id?>">
    <h2 class="cat_name"><?php echo $category->name; ?></h2>
    <a href="<?php echo get_category_link($category) ?>" class="btn btn-primary pull-right">Tout voir</a>
</div>

<div class="row items-posts" id="<?php echo $_target ?>">
    <?php include(locate_template('include/templates/items-posts.php')); ?>
</div>