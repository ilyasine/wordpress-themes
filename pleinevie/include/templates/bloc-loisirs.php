<?php
$category_loisirs = get_param_global('category_loisirs', 'loisirs');
$posts = get_posts(array(
    'showposts' => 2, 
    'category_name' => $category_loisirs,
    'orderby' => 'post_date', 
    'order' => 'DESC',
));
if( !empty($posts) && count($posts) == 2 ):
?>
<div class="bloc-loisirs">
    <div class="bloc_rubrique_head">
        <h2 class="default-title"><?php _e('LOISIRS'); ?></h2>
        <?php
            
            $category_link = get_term_link($category_loisirs, 'category');
            if($category_link){
                ?>
                <a href="javascript:void(0);" data-href="<?php echo $category_link; ?>" class="more_cat">
                    <?php echo __("Tout voir", REWORLDMEDIA_TERMS);?>
                </a>
                <?php
            }
         ?>
     </div>
    <div class="row">
        <?php 
        foreach ($posts as $post) {
            setup_postdata($post);
            ?>
                <div class="post col-xs-12 col-md-6">
                    <?php
                        $size = "rw_medium";
                        include(locate_template('include/templates/block_post.php'));
                    ?>
                </div>
            <?php 
        }
        wp_reset_postdata();
        ?>
    </div>
</div>
<?php endif; ?>