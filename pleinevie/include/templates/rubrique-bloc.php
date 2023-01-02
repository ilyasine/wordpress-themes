<?php

if(!empty($parent_category)){
    if($parent_category->slug == 'loisirs' && is_front_page()){
        include (locate_template("/pleinevie/include/templates/bloc-loisirs.php"));
    }else{
        $child_categories = get_categories(
            array( 'parent' => $parent_category->term_id, )
        );
        $args_rubrique = array(
            'numberposts' => 4, 
            'category' => $parent_category->term_id,
            'orderby' => 'post_date',
            'order' => 'DESC',
        );
        if(  $args_lock = get_locking_config('last_post_by_category', 'bloc_rubrique_'. $parent_category->term_id)  ){
            $data_posts=Locking::get_locking_ids($args_lock , $args_rubrique);
        } else {
            $data_posts = get_posts($args_rubrique);
        }
        ?>
        <div class="bloc_rubrique">
            <div class="bloc_rubrique_head">
                <h2 class="default-title pull-left"><?php echo $parent_category->name;?></h2>
                <a class="more_cat pull-right" href="<?php echo get_category_link($parent_category);?>">Tout voir</a>
            </div>
            <nav class="categories-list">
                <ul class="list-inline">
                    <?php
                    if(!empty($child_categories)){
                        foreach($child_categories as $cat){
                            echo '<li><a class="filter-category" href="'. get_category_link($cat) .'">'.$cat->name.'</a></li>';
                        }
                    }
                    ?>
                </ul>
            </nav>
            <?php 
                if( get_param_global('show_cat_description') && !empty($parent_category->description) ){
                    echo '<span class="bloc-description">' . $parent_category->description . '</span>';
                }?>
            <div class="row">
                <?php
                if(!empty($data_posts)){
                    include(locate_template('/include/templates/items-posts.php'));
                }
                ?>
            </div>
        </div>
        <?php
    }
}