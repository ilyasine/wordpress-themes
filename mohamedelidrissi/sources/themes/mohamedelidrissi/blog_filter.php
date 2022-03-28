

<?php function filter_ajax() {

// echo 'filter_ajax is detected <br>';
// echo ' the category is '+$category ;

$category = $_POST['category'];
$pagenb = $_POST['pagenb'];

$args = array (
  'paged' => $paged , 
  'post_type' => 'post',
  //  'posts_per_page' => 2,

);

if( isset($category)) {
  $args['category__in'] = array($category); 
}

// $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// echo $paged ;

$filtered_blogs = new WP_Query($args);

  while ($filtered_blogs->have_posts()) {
      $filtered_blogs->the_post(); ?>
        <div class="grid-post">
                <div class="blog-item"> 
                  <div class="post-thumb"><a href="<?php the_permalink();?>"><i class="fal fa-external-link-alt" id="thumb-link"></i><?php the_post_thumbnail(); ?></a></div>
                  <div class="post-txt">
                    <h2><a href="<?php the_permalink();?>"><?php the_title() ;?></a></h2>
                    <div class="meta-top">                         
                        <div class="meta-tags">
                            <div class="meta-poster"><i class="fad fa-user-music"></i>محمد الإدريسي</div>
                            <div class="meta-category"><i class="fad fa-tags"></i><?php the_category(); ?></div>
                            <div class="meta-date"><i class="fad fa-clock"></i><?php the_date(); ?></div>
                        </div>                          
                    </div>
               
           <?php the_excerpt() ;?>
           
           <div class="meta-bottom"> 
            <div class="learn-more" type="button"><a href="<?php the_permalink();?>" target="blank"><b>قراءة المزيد</b><i class="fal fa-external-link-alt" id="learn-more-link"></i></a></div>
               <div class="share-post">
                <div class="share-btn">
                  <i class="fas fa-share-alt-square"></i>
                  <div class="share-arrow"></div>
               </div>
                <div class="share-list">
                 <a onClick="shareOnpinterest()"><i class="fab fa-pinterest"></i></a>
                 <a onClick="shareOntwitter()" ><i class="fab fa-twitter"></i></a>
                 <a onClick="shareOnFB()"><i class="fab fa-facebook"></i></a>
                </div>
                  <script>

                     function shareOnFB(){
                      window.open("https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink();?> &text=<?php the_title() ;?> | محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                       }
                     function shareOntwitter(){
                      window.open("https://twitter.com/intent/tweet?url=<?php the_permalink();?> &text=  محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                       }
                     function shareOnpinterest(){
                      window.open("https://pinterest.com/pin/create/button/?url=<?php the_permalink();?> &description= <?php the_title() ;?> | محمد الإدريسي", "_blank", "toolbar=yes,scrollbars=yes,resizable=yes,top=100,left=50,width=450,height=500");
                       }
                  </script>
               </div>  
           </div>
           
         </div>
       </div>
  <!-- *** blog 1 **** -->
  <div class="separator">
    <object type="image/svg+xml" data="<?php echo get_theme_file_uri('assets\svgs\separator.svg')?>"></object>
    
   </div>
 </div>
<?php  }

wp_reset_postdata(); ?>

            <div class="pagination">
             <?php 
                echo paginate_links( array(
                 'post_type' => 'post',
                 'current'   => max( 1, $paged ),
                 'total' => $filtered_blogs -> max_num_pages , 
                 'prev_text' => __( '<i class="fad fa-angle-double-right"></i>السابق', 'textdomain' ),
                 'next_text' => __( 'التالي<i class="fad fa-angle-double-left"></i>', 'textdomain' ),
                ) ); ?>
            </div> 
           
  <?php wp_die();
}

